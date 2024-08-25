<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_usd',
        'price_btc',
        'vendor_id',
        'status',
    ];

    protected $casts = [
        'price_usd' => 'decimal:2',
        'price_btc' => 'decimal:8',
        'quantity' => 'integer',
        'status' => 'string'
    ];

    public function setPriceUsdAttribute($value)
    {
        $this->attributes['price_usd'] = $value ?? 0;
    }

    public function setPriceBtcAttribute($value)
    {
        $this->attributes['price_btc'] = $value ?? 0;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}