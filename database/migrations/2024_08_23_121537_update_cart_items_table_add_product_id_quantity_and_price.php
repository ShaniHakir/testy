<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'product_id')) {
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('cart_items', 'quantity')) {
                $table->integer('quantity')->after('product_id');
            }
            if (!Schema::hasColumn('cart_items', 'price')) {
                $table->decimal('price', 16, 8)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'quantity', 'price']);
        });
    }
};