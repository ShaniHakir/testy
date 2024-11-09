<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MoneroRPCService;
use App\Models\MoneroTransaction;
use App\Models\User;

class CheckMoneroPool extends Command
{
    protected $signature = 'monero:check-pool';
    protected $description = 'Check for transactions in the Monero pool';

    protected $moneroService;

    public function __construct(MoneroRPCService $moneroService)
    {
        parent::__construct();
        $this->moneroService = $moneroService;
    }

    public function handle()
    {
        try {
            $walletRPC = $this->moneroService->wallet();
            $result = $walletRPC->getTransfers([
                'pool' => true,
                'in' => true
            ]);

            if (isset($result['pool'])) {
                foreach ($result['pool'] as $transfer) {
                    // Try to find user by address label
                    $label = $transfer['label'] ?? '';
                    if (preg_match('/^user_(\d+)$/', $label, $matches)) {
                        $userId = $matches[1];
                        $amount = $transfer['amount'] / 1e12;

                        // Create transaction record if it doesn't exist
                        MoneroTransaction::firstOrCreate(
                            ['tx_hash' => $transfer['txid']],
                            [
                                'user_id' => $userId,
                                'amount' => $amount,
                                'type' => 'deposit',
                                'is_confirmed' => false
                            ]
                        );

                        $this->info("Found pool transaction: {$transfer['txid']} for user $userId");
                    }
                }
            }

            $this->info('Pool check completed');
        } catch (\Exception $e) {
            $this->error('Error checking pool: ' . $e->getMessage());
        }
    }
}
