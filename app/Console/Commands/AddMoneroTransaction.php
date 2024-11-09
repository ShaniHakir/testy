<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MoneroTransaction;

class AddMoneroTransaction extends Command
{
    protected $signature = 'monero:add-tx';
    protected $description = 'Add specific Monero transaction';

    public function handle()
    {
        MoneroTransaction::firstOrCreate(
            [
                'tx_hash' => '195d43eb59eaa8d298c69cfd16fd07af09f21449468486c3fca3bb585b0f73c1'
            ],
            [
                'user_id' => 11,
                'amount' => 0, // Will be updated when confirmed
                'type' => 'deposit',
                'is_confirmed' => false
            ]
        );

        $this->info('Transaction record created');
    }
}
