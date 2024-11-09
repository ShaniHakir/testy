<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add balance to users table
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 24, 12)->default(0)->after('is_banned'); // Monero has 12 decimal places
        });

        // Drop the monero_wallets table as we're not using individual wallets
        Schema::dropIfExists('monero_wallets');

        // Update monero_transactions table
        Schema::table('monero_transactions', function (Blueprint $table) {
            // Drop existing columns we don't need
            $table->dropColumn('confirmed_height');
            
            // Update type enum to use deposit/withdrawal instead of incoming/outgoing
            $table->dropColumn('type');
            $table->enum('type', ['deposit', 'withdrawal'])->after('amount');
        });
    }

    public function down(): void
    {
        // Remove balance from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance');
        });

        // Recreate monero_wallets table
        Schema::create('monero_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('wallet_address', 95)->unique();
            $table->unsignedInteger('address_index');
            $table->bigInteger('last_block_height')->default(0);
            $table->decimal('balance', 24, 12)->default(0);
            $table->timestamps();

            $table->index('wallet_address');
            $table->index('address_index');
        });

        // Restore original monero_transactions columns
        Schema::table('monero_transactions', function (Blueprint $table) {
            $table->integer('confirmed_height')->nullable();
            $table->dropColumn('type');
            $table->enum('type', ['incoming', 'outgoing'])->after('amount');
        });
    }
};
