<?php

namespace App\Services;

use App\Models\MoneroTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\Monero\WalletRPC;
use App\Services\Monero\DaemonRPC;

class MoneroRPCService
{


    private $walletHost;
    private $walletPort;
    private $daemonHost;
    private $daemonPort;
    private $mainWalletName;

    public function __construct()
    {
        $this->walletHost = config('monero.rpc.host', '127.0.0.1');
        $this->walletPort = config('monero.rpc.port', '38083');
        $this->daemonHost = config('monero.rpc.daemon_host', '127.0.0.1');
        $this->daemonPort = config('monero.rpc.daemon_port', '28081');
    }

    public function makeRpcCall($method, $params = [])
    {
        $url = "http://{$this->walletHost}:{$this->walletPort}/json_rpc";
        
        $curl = curl_init();
        
        $body = json_encode([
            'jsonrpc' => '2.0',
            'id' => '0',
            'method' => $method,
            'params' => $params
        ]);

        Log::info("Making RPC call", [
            'method' => $method,
            'url' => $url,
            'params' => $params
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($curl);
        
        if ($error = curl_error($curl)) {
            Log::error("Curl error: " . $error);
            throw new Exception("RPC call failed: " . $error);
        }

        curl_close($curl);
        
        $decoded = json_decode($response, true);
        
        Log::info("RPC response received", [
            'response' => $decoded
        ]);

        if (isset($decoded['error'])) {
            Log::error("RPC error", ['error' => $decoded['error']]);
            throw new Exception("RPC error: " . ($decoded['error']['message'] ?? 'Unknown error'));
        }

        return $decoded['result'] ?? [];
    }

    /**
     * Get daemon RPC instance
     */
    private function getDaemonRPC()
    {
        return new DaemonRPC([
            'host' => config('monero.rpc.daemon_host'),
            'port' => config('monero.rpc.daemon_port')
        ]);
    }

    /**
     * Get wallet RPC instance
     */
    private function getWalletRPC()
    {
        return new WalletRPC([
            'host' => config('monero.rpc.host'),
            'port' => config('monero.rpc.port')
        ]);
    }

    /**
     * Generate a deposit address for a user
     */
    public function generateDepositAddress(User $user): string
    {
        try {
            $walletRPC = $this->getWalletRPC();
            $result = $walletRPC->getAddress([
                'account_index' => 0,
                'label' => 'user_' . $user->id
            ]);

            // Check for any pending transactions to this address
            $transfers = $walletRPC->getTransfers([
                'pool' => true,
                'in' => true
            ]);

            // Process pool transactions
            if (isset($transfers['pool'])) {
                foreach ($transfers['pool'] as $transfer) {
                    if (isset($transfer['address']) && $transfer['address'] === $result['address']) {
                        // Create transaction record for pending transaction
                        MoneroTransaction::firstOrCreate(
                            ['tx_hash' => $transfer['txid']],
                            [
                                'user_id' => $user->id,
                                'amount' => $transfer['amount'] / 1e12,
                                'type' => 'deposit',
                                'is_confirmed' => false
                            ]
                        );
                    }
                }
            }

            Log::info('Generated deposit address', [
                'user_id' => $user->id,
                'address' => $result['address']
            ]);

            return $result['address'];
        } catch (Exception $e) {
            Log::error('Failed to generate deposit address: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    /**
     * Send withdrawal to an address
     */
    public function sendWithdrawal(User $user, string $address, float $amount): string
    {
        try {
            // Convert amount to atomic units (1 XMR = 1e12 atomic units)
            $atomicAmount = bcmul($amount, '1000000000000', 0);

            // Check if user has sufficient balance
            if ($user->balance < $amount) {
                throw new Exception('Insufficient balance');
            }

            $walletRPC = $this->getWalletRPC();
            $result = $walletRPC->transfer([
                'destinations' => [
                    ['address' => $address, 'amount' => (int)$atomicAmount]
                ],
                'priority' => 1,
                'ring_size' => 11
            ]);

            // Create transaction record
            MoneroTransaction::create([
                'user_id' => $user->id,
                'tx_hash' => $result['tx_hash'],
                'amount' => $amount,
                'type' => 'withdrawal',
                'is_confirmed' => false
            ]);

            // Deduct from user's balance
            $user->decrement('balance', $amount);

            Log::info('Sent withdrawal', [
                'user_id' => $user->id,
                'amount' => $amount,
                'tx_hash' => $result['tx_hash']
            ]);

            return $result['tx_hash'];
        } catch (Exception $e) {
            Log::error('Failed to send withdrawal: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $amount
            ]);
            throw $e;
        }
    }

    /**
     * Check for new deposits
     */
    public function checkDeposits(): void
    {
        try {
            $result = $this->makeRpcCall('get_transfers', [
                'in' => true,
                'pool' => true,
                'pending' => true
            ]);

            if (isset($result['pool'])) {
                foreach ($result['pool'] as $transfer) {
                    $this->processTransfer($transfer, false);
                }
            }

            if (isset($result['in'])) {
                foreach ($result['in'] as $transfer) {
                    $this->processTransfer($transfer, true);
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to check deposits: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process a transfer and update the database
     */
    private function processTransfer(array $transfer, bool $isConfirmed): void
    {
        try {
            $amount = $transfer['amount'] / 1e12;
            
            Log::info('Processing transfer:', [
                'tx_hash' => $transfer['txid'] ?? 'unknown',
                'amount' => $amount,
                'isConfirmed' => $isConfirmed,
                'label' => $transfer['label'] ?? 'no_label'
            ]);

            $label = $transfer['label'] ?? '';
            if (preg_match('/^user_(\d+)$/', $label, $matches)) {
                $userId = $matches[1];

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
                        Log::info("Updated balance for user $userId: +$amount XMR");
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Error processing transfer: ' . $e->getMessage(), [
                'transfer' => $transfer,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }


    /**
     * Get current blockchain height
     */
    public function getCurrentHeight(): int
    {
        try {
            $daemonRPC = $this->getDaemonRPC();
            $info = $daemonRPC->getInfo();
            return $info['height'];
        } catch (Exception $e) {
            Log::error('Failed to get current height: ' . $e->getMessage());
            throw $e;
        }
    }
}
