<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MoneroTransaction;
use App\Models\User;

class CheckMoneroTransaction extends Command
{
    protected $signature = 'monero:check-tx {txid} {user_id}';
    protected $description = 'Check and record a specific Monero transaction';

    public function handle()
    {
        $txid = $this->argument('txid');
        $userId = $this->argument('user_id');

        // Create transaction record if it doesn't exist
        MoneroTransaction::firstOrCreate(
            ['tx_hash' => $txid],
            [
                'user_id' => $userId,
                'amount' => 0, // Will be updated when confirmed
                'type' => 'deposit',
                'is_confirmed' => false
            ]
        );

        $this->info("Transaction record created for txid: $txid");
    }
}
