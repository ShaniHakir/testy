<?php

namespace App\Console;

use App\Services\MoneroRPCService;
use Illuminate\Console\Command;

class MoneroDepositChecker extends Command
{
    protected $signature = 'monero:check-deposits';
    protected $description = 'Check for new Monero deposits';

    public function __construct(
        protected MoneroRPCService $moneroService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Checking for Monero deposits...');
        
        try {
            $this->moneroService->checkDeposits();
            $this->info('Deposit check completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error checking deposits: ' . $e->getMessage());
            \Log::error('Deposit check failed: ' . $e->getMessage());
        }
    }
}