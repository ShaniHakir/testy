<?php

namespace App\Http\Controllers;

use App\Services\CurrencyConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $currencyService;

    // Dependency injection of the CurrencyConversionService
    public function __construct(CurrencyConversionService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    // Display the wallet page with BTC and USD balances
    public function index()
    {

        $user = Auth::user();
        $wallet = $user->wallet;
    
        if (!$wallet) {
            return back()->with('error', 'No wallet found.');
        }
    
        $usdAmount = $this->currencyService->convertBtcToUsd($wallet->balance_btc);
        return view('wallet.index', [
            'wallet' => $wallet,
            'usdAmount' => $usdAmount
        ]);

    }
    
}
