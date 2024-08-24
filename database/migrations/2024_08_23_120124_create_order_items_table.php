<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('price', 16, 8);
                $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};