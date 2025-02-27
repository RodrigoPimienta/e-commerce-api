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
        Schema::create('products_carts', function (Blueprint $table) {
            $table->id('id_product_cart');
            $table->foreignId('id_cart')->constrained('carts', 'id_cart');
            $table->foreignId('id_product')->constrained('products_stores', 'id_product');
            $table->integer('quantity');
            $table->decimal('final_price', 10, 2)->nullable(); // Stores the price at the time of purchase
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_carts');
    }
};
