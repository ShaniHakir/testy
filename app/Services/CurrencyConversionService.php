<?php

namespace App\Services;

use GuzzleHttp\Client;

class CurrencyConversionService
{
    public function convertBtcToUsd($btcAmount)
    {
        $rate = $this->fetchConversionRate('BTC', 'USD', $btcAmount);
        return $btcAmount * $rate;
    }

    public function convertUsdToBtc($usdAmount)
    {
        $rate = $this->fetchConversionRate('BTC', 'USD', 1); // Fetch rate for 1 BTC to USD
        $btcAmount = $usdAmount / $rate; // Inverse rate for USD to BTC
        return $this->formatBtc($btcAmount);
    }

    private function fetchConversionRate($from, $to, $amount)
    {
        $client = new Client();
        $response = $client->request('POST', "https://cex.io/api/convert/{$from}/{$to}", [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'amnt' => $amount,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['amnt'])) {
            return $data['amnt'];
        }

        \Log::error('Failed to fetch conversion rate: ' . json_encode($data));
        throw new \Exception("Failed to fetch conversion rate");
    }

    private function formatBtc($btcAmount)
    {
        return number_format($btcAmount, 8, '.', '');
    }
}
