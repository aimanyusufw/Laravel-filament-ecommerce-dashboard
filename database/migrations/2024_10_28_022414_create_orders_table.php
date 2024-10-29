<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->nullable()->default(1); // 1. Pending payment 2. Processing 3. Ready to ship 4. Shipped 5. Completed 6. Cancelled
            $table->json('shipping_detail')->nullable();
            $table->json('shipping_address_detail')->nullable();
            $table->double('subtotal')->nullable()->default(0);
            $table->double('shipping_cost')->nullable()->default(0);
            $table->double('tax')->nullable()->default(0);
            $table->double('grand_total')->nullable()->default(0);
            $table->date('order_date')->nullable();
            $table->string('order_code')->nullable()->unique();
            $table->string('resi_code')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
