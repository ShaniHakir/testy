<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyConversionService
{
    protected $apiUrl = 'https://min-api.cryptocompare.com/data/price';

    public function convertXmrToUsd($amount)
    {
        $rate = $this->getXmrUsdRate();
        return $amount * $rate;
    }

    public function convertBtcToUsd($amount)
    {
        $rate = $this->getBtcUsdRate();
        return $amount * $rate;
    }

    protected function getXmrUsdRate()
    {
        return Cache::remember('xmr_usd_rate', 300, function () {
            $response = Http::get($this->apiUrl, [
                'fsym' => 'XMR',
                'tsyms' => 'USD'
            ]);

            if ($response->successful()) {
                return $response->json()['USD'];
            }

            // Return a fallback rate if API call fails
            return 0.00;
        });
    }

    protected function getBtcUsdRate()
    {
        return Cache::remember('btc_usd_rate', 300, function () {
            $response = Http::get($this->apiUrl, [
                'fsym' => 'BTC',
                'tsyms' => 'USD'
            ]);

            if ($response->successful()) {
                return $response->json()['USD'];
            }

            // Return a fallback rate if API call fails
            return 0.00;
        });
    }
}
