<?php

namespace App\Http\Controllers;

use App\Services\CurrencyConversionService;
use App\Services\MoneroRPCService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MoneroTransaction;

class WalletController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyConversionService $currencyService)
    {
        $this->currencyService = $currencyService;
        $this->middleware(['auth']);
    }

    public function index()
    {
        $user = Auth::user();

        // Get pending deposits for current user only
        $pendingDeposits = MoneroTransaction::where('user_id', $user->id)
            ->where('type', 'deposit')
            ->where('is_confirmed', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pending withdrawals for current user only
        $pendingWithdrawals = MoneroTransaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('is_confirmed', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get confirmed transactions for current user only
        $confirmedTransactions = MoneroTransaction::where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Convert current user's balance to USD
        $usdAmount = $this->currencyService->convertXmrToUsd($user->balance);

        return view('wallet.index', [
            'balance' => $user->balance,
            'usdAmount' => $usdAmount,
            'pendingIncoming' => $pendingDeposits,
            'pendingOutgoing' => $pendingWithdrawals,
            'confirmedTransactions' => $confirmedTransactions
        ]);
    }

    public function getDepositAddress(Request $request)
    {
        try {
            $user = Auth::user();
            $moneroService = app(MoneroRPCService::class);
            $address = $moneroService->generateDepositAddress($user);
            
            return back()->with('deposit_address', $address);
        } catch (\Exception $e) {
            \Log::error('Failed to generate deposit address: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate deposit address');
        }
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'address' => 'required|string|min:95|max:106',
            'amount' => 'required|numeric|min:0.000001'
        ]);

        $user = Auth::user();

        try {
            if (!$user->hasSufficientBalance($request->amount)) {
                return back()->with('error', 'Insufficient funds');
            }

            $moneroService = app(MoneroRPCService::class);
            $txHash = $moneroService->sendWithdrawal($user, $request->address, $request->amount);

            return back()->with('success', 'Withdrawal initiated. Transaction hash: ' . $txHash);
        } catch (\Exception $e) {
            \Log::error('Withdrawal failed: ' . $e->getMessage());
            return back()->with('error', 'Withdrawal failed: ' . $e->getMessage());
        }
    }
}
