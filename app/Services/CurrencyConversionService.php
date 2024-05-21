<?php

namespace App\Services;

use GuzzleHttp\Client;

class CurrencyConversionService
{
    public function convertBtcToUsd($btcAmount)
    {
        $rate = $this->fetchConversionRate();
        return $btcAmount * $rate;
    }

    private function fetchConversionRate()
    {
        $client = new Client();
        $response = $client->request('POST', 'https://cex.io/api/convert/BTC/USD', [
            'json' => ['amnt' => 1]  // Example amount to fetch the conversion rate
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['amnt'];  // Ensure this key matches the API response for the converted amount
    }
}
