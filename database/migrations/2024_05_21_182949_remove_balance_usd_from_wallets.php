<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveBalanceUsdFromWallets extends Migration
{
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('balance_usd');  // Drop the balance_usd column
        });
    }

    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('balance_usd', 10, 2)->default(0.00);  // Add balance_usd back if needed
        });
    }
}
