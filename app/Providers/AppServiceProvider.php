<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Services\CurrencyConversionService; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CurrencyConversionService::class, function ($app) {
            return new CurrencyConversionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $unreadMessagesCount = Auth::user()->receivedMessages()->unread()->count();
                $view->with('unreadMessagesCount', $unreadMessagesCount);
            }
        });
    }
}