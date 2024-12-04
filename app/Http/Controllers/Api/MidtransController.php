<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public function midtrans(Request $request)
    {
        $statusCode = $request->status_code;
        $groosAmount = $request->gross_amount;
        $orderId = $request->order_id;
        $type = $request->payment_type;
        $transaction = $request->transaction_status;
        $signatureKey = $request->signature_key;
        $fraud = $request->fraud_status;
        $serverKey = env('MIDTRANSE_SERVER_KEY');

        $mySignatureKey = hash('sha512', $orderId . $statusCode . $groosAmount . $serverKey);

        if ($signatureKey != $mySignatureKey) {
            throw new HttpResponseException(response([
                "message" => 'Invalid Signature Key'
            ], 400));
        }

        $order = Order::with('orderItems')->where('order_code', $orderId)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $invoice = Invoice::where('order_id', $order->id)->first();
        if (!$invoice || $order->status != 1) {
            return response()->json(['message' => 'Invalid payment'], 400);
        }

        $products = Product::whereIn('id', $order->orderItems->pluck('product_id'))->get();

        $invoiceStatus = $invoice->status;
        switch ($transaction) {
            case 'capture':
                if ($type === 'credit_card') {
                    $invoiceStatus = ($fraud === 'challenge') ? '1' : '2';
                }
                break;
            case 'settlement':
                $invoiceStatus = '2';
                break;
            case 'pending':
                $invoiceStatus = '1';
                break;
            case 'deny':
            case 'cancel':
                $invoiceStatus = '4';
                break;
            case 'expire':
                $invoiceStatus = '3';
                break;
        }

        if ($invoiceStatus === '2') {
            foreach ($products as $product) {
                $orderedQty = $order->orderItems->firstWhere('product_id', $product->id)->qty ?? 0;
                $product->stock -= $orderedQty;
                $product->save();
            }
            $order->status = '2';
            $order->save();
        }

        $invoice->status = $invoiceStatus;
        $invoice->paid_at = time();
        $invoice->raw_response = json_encode($request->all());
        $invoice->save();

        return response()->json(['message' => 'Your payment is successfully']);
    }
}
