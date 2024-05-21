<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    // Mass assignable attributes
    protected $fillable = [
        'user_id',
        'balance_btc',
    ];

    // Wallet belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Method to get the USD equivalent for display purposes
    public function getUsdValue()
    {
        $btcAmount = $this->balance_btc;
        return $btcAmount * $this->getConversionRate();
    }

    private function getConversionRate()
    {
        // Ideally, this method should fetch the latest BTC to USD rate from an API
        // Here's a placeholder for the API call logic
        // Example using a fictional API client
        $conversionRate = $this->fetchConversionRateFromApi();
        return $conversionRate;
    }

    private function fetchConversionRateFromApi()
    {
        // Assuming using GuzzleHttp client or similar to make the API request
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://api.exchangerate-api.com/v4/latest/BTC');
        $data = json_decode($response->getBody()->getContents(), true);

        // Assuming the response gives a direct BTC to USD rate
        return $data['rates']['USD'];
    }
    
}