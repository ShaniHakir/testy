<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\CurrencyConversionService;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $wallet = $user->moneroWallet;
                $unreadMessagesCount = $user->unreadMessages()->count();

                if ($wallet) {
                    // Calculate balance from confirmed transactions
                    $incoming = $wallet->transactions()
                        ->where('type', 'incoming')
                        ->where('is_confirmed', true)
                        ->sum('amount');
                    
                    $outgoing = $wallet->transactions()
                        ->where('type', 'outgoing')
                        ->where('is_confirmed', true)
                        ->sum('amount');
                    
                    $xmrBalance = bcsub($incoming, $outgoing, 12);
                    
                    // Get USD equivalent
                    $currencyService = app(CurrencyConversionService::class);
                    $usdBalance = $currencyService->convertXmrToUsd($xmrBalance);
                } else {
                    $xmrBalance = '0.000000000000';
                    $usdBalance = 0;
                }

                $view->with([
                    'xmrBalance' => $xmrBalance,
                    'usdBalance' => $usdBalance,
                    'unreadMessagesCount' => $unreadMessagesCount
                ]);
            }
        });
    }
}
