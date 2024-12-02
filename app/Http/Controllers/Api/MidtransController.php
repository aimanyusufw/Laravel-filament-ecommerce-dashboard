<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
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

        $order = Order::where('order_code', $orderId)->first();
        $invoice = Invoice::where('order_id', $order->id)->first();

        if (!$order || !$invoice || $order->status != 1) {
            throw new HttpResponseException(response([
                "message" => 'Invalid payment',
            ], 400));
        }

        $invoiceStatus = $invoice->status;
        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $invoiceStatus = '1';
                } else {
                    $invoiceStatus = '2';
                }
            }
        } else if ($transaction == 'settlement') {
            $invoiceStatus = '2';
        } else if ($transaction == 'pending') {
            $invoiceStatus = '1';
        } else if ($transaction == 'deny') {
            $invoiceStatus = '4';
        } else if ($transaction == 'expire') {
            $invoiceStatus = '3';
        } else if ($transaction == 'cancel') {
            $invoiceStatus = '4';
        }

        if ($invoiceStatus == '2') {
            $order->status = '2';
            $order->save();
        }
        $invoice->status = $invoiceStatus;
        $invoice->raw_response = json_encode($request->all());
        $invoice->save();

        return response()->json(["message" => "Your payment is successfully"]);
    }
}
