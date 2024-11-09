<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MoneroRPCService;
use App\Models\MoneroTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MoneroDepositCheck extends Command
{
    protected $signature = 'monero:check-deposits';
    protected $description = 'Check for new Monero deposits including pool transactions';

    public function __construct(
        protected MoneroRPCService $moneroService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            $this->info('Starting deposit check...');
            
            // Make RPC call with more options to ensure we get all transactions
            $result = $this->moneroService->makeRpcCall('get_transfers', [
                'in' => true,
                'pool' => true,
                'pending' => true,
                'filter_by_height' => false,
                'min_height' => 0
            ]);

            // Debug output
            $this->info('RPC Response:');
            if (isset($result['in'])) {
                $this->info('Confirmed transactions: ' . count($result['in']));
                foreach ($result['in'] as $tx) {
                    $this->info("Found confirmed tx: {$tx['txid']} - Amount: " . ($tx['amount'] / 1e12) . " XMR");
                }
            }
            
            if (isset($result['pool'])) {
                $this->info('Pool transactions: ' . count($result['pool']));
                foreach ($result['pool'] as $tx) {
                    $this->info("Found pool tx: {$tx['txid']} - Amount: " . ($tx['amount'] / 1e12) . " XMR");
                }
            }

            if (isset($result['pending'])) {
                $this->info('Pending transactions: ' . count($result['pending']));
                foreach ($result['pending'] as $tx) {
                    $this->info("Found pending tx: {$tx['txid']} - Amount: " . ($tx['amount'] / 1e12) . " XMR");
                }
            }

            $this->processTransfers($result);

            // Show database state
            $this->info("\nDatabase Transactions:");
            $transactions = MoneroTransaction::orderBy('created_at', 'desc')->take(5)->get();
            foreach ($transactions as $tx) {
                $this->info("DB tx: {$tx->tx_hash} - Amount: {$tx->amount} XMR - " . 
                    ($tx->is_confirmed ? 'Confirmed' : 'Pending'));
            }

            $this->info('Deposit check completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error checking deposits: ' . $e->getMessage());
            Log::error('Deposit check failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function processTransfers(array $result): void
    {
        // Process pool transactions (unconfirmed)
        if (isset($result['pool'])) {
            foreach ($result['pool'] as $transfer) {
                $this->processTransfer($transfer, false);
            }
        }

        // Process confirmed transactions
        if (isset($result['in'])) {
            foreach ($result['in'] as $transfer) {
                $this->processTransfer($transfer, true);
            }
        }

        // Process pending transactions if available
        if (isset($result['pending'])) {
            foreach ($result['pending'] as $transfer) {
                $this->processTransfer($transfer, false);
            }
        }
    }

    protected function processTransfer(array $transfer, bool $isConfirmed): void
    {
        try {
            $amount = $transfer['amount'] / 1e12;
            $address = $transfer['address'] ?? '';
    
            $this->info("\nProcessing transfer:");
            $this->info("TX Hash: " . ($transfer['txid'] ?? 'unknown'));
            $this->info("Amount: $amount XMR");
            $this->info("Status: " . ($isConfirmed ? 'Confirmed' : 'Pending'));
            $this->info("Address: $address");
    
            // First try to find user by label
            $userId = null;
            $label = $transfer['label'] ?? '';
            if (preg_match('/^user_(\d+)$/', $label, $matches)) {
                $userId = $matches[1];
            }
    
            // If no label, try to find user by address
            if (!$userId) {
                // Add address lookup here - you'll need to implement this based on
                // how you store user deposit addresses
                $wallet = \App\Models\MoneroWallet::where('wallet_address', $address)->first();
                if ($wallet) {
                    $userId = $wallet->user_id;
                    $this->info("Found user by address: $userId");
                }
            }
    
            if ($userId) {
                // Create transaction record if it doesn't exist
                $transaction = MoneroTransaction::firstOrCreate(
                    ['tx_hash' => $transfer['txid']],
                    [
                        'user_id' => $userId,
                        'amount' => $amount,
                        'type' => 'deposit',
                        'is_confirmed' => $isConfirmed
                    ]
                );
    
                if ($isConfirmed && !$transaction->is_confirmed) {
                    $transaction->is_confirmed = true;
                    $transaction->save();
    
                    $user = User::find($userId);
                    if ($user) {
                        $user->increment('balance', $amount);
                        $this->info("Updated balance for user $userId: +$amount XMR");
                    }
                }
    
                $this->info("Processed transaction: {$transfer['txid']} for user $userId");
            } else {
                $this->warn("Could not find user for address: $address");
            }
        } catch (\Exception $e) {
            $this->error('Error processing transfer: ' . $e->getMessage());
            Log::error('Error processing transfer:', [
                'error' => $e->getMessage(),
                'transfer' => $transfer,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}