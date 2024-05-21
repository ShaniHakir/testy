<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CurrencyConversionService;
use App\Models\User;
use App\Models\Wallet;

class VendorController extends Controller
{
    protected $currencyConversionService;

    public function __construct(CurrencyConversionService $currencyConversionService)
    {
        $this->currencyConversionService = $currencyConversionService;
    }

    public function showUpgradeConfirmation()
    {
        $user = Auth::user();

        if (!$user->gpg_key_verified) {
            return redirect()->route('settings.gpg')->withErrors(['error' => 'You must have a validated GPG key to become a vendor.']);
        }

        $bondPriceUsd = env('VENDOR_BOND_PRICE_USD', 50); // Default to $50 if not set
        $bondPriceBtc = $this->currencyConversionService->convertUsdToBtc($bondPriceUsd);

        return view('settings.confirm_vendor_upgrade', compact('bondPriceUsd', 'bondPriceBtc'));
    }

    public function upgradeToVendor(Request $request)
    {
        $user = Auth::user();

        if (!$user->gpg_key_verified) {
            return redirect()->route('vendor.upgrade')->withErrors(['error' => 'You must have a validated GPG key to become a vendor.']);
        }

        $bondPriceUsd = env('VENDOR_BOND_PRICE_USD', 50); // Default to $50 if not set
        $bondPriceBtc = $this->currencyConversionService->convertUsdToBtc($bondPriceUsd);

        $wallet = $user->wallet;
        if ($wallet->balance_btc < $bondPriceBtc) {
            return redirect()->route('vendor.upgrade')->withErrors(['error' => 'Insufficient funds in wallet.']);
        }

        $wallet->balance_btc -= $bondPriceBtc;
        $wallet->save();

        $user->role = 'vendor';
        $user->save();

        return redirect()->route('profile')->with('success', 'You have been successfully upgraded to a vendor.');
    }
}
