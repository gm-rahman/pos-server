<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->required();
            $table->decimal('price', 10, 2)->required();
            $table->integer('stock')->required();
            $table->string('trade_offer_min_qty')->nullable();
            $table->string('trade_offer_get_qty')->nullable();
            $table->decimal('discount', 5, 2)->nullable();
            $table->dateTime('discount_or_trade_offer_start_date')->nullable();
            $table->dateTime('discount_or_trade_offer_end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}