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
        Schema::table('monero_wallets', function (Blueprint $table) {
            $table->dropColumn('view_key_encrypted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monero_wallets', function (Blueprint $table) {
            $table->string('view_key_encrypted')->after('address_index');
        });
    }
};
