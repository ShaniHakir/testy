<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monero_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('wallet_address', 95)->unique(); // Monero addresses are 95 chars
            $table->unsignedInteger('address_index'); // Index of the subaddress
            $table->bigInteger('last_block_height')->default(0);
            $table->decimal('balance', 24, 12)->default(0); // Monero has 12 decimal places
            $table->timestamps();

            $table->index('wallet_address');
            $table->index('address_index');
        });

        // Table for tracking Monero transactions
        Schema::create('monero_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tx_hash', 64)->unique();
            $table->decimal('amount', 24, 12);
            $table->enum('type', ['incoming', 'outgoing']);
            $table->integer('confirmed_height')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('tx_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monero_transactions');
        Schema::dropIfExists('monero_wallets');
    }
};
