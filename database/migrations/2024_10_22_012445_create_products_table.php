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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->default("Unilited product");
            $table->string('slug')->nullable();
            $table->string('excerpt')->nullable();
            $table->text('description')->nullable();
            $table->json('product_images')->nullable();
            $table->unsignedInteger("weight")->nullable()->default(0);
            $table->double('price')->nullable()->default(0);
            $table->double('sale_price')->nullable()->default(0);
            $table->unsignedInteger("stock")->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
