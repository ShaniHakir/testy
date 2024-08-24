<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 16, 8)->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending')->after('total_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'total_amount', 'status']);
        });
    }
};