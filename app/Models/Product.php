<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price', 
        'discount_quantity',
        'discount_price',
        'category_id',
        'user_id',
        'stock',
    ];

    protected $casts = [
        'discount_quantity' => 'integer',
        'price_usd' => 'float',
        'price_btc' => 'float',
        'discount_price_usd' => 'float',
        'discount_price_btc' => 'float',
        'stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getDefaultImage(): ?ProductImage
    {
        return $this->images()->where('is_default', true)->first();
    }

    public function getPriceInUsd(): float
    {
        return $this->price ?? 0.0;
    }


    public function getPriceInBtc(): float
    {
        return $this->price_btc;
    }

    public function getDiscountPriceInUsd(): ?float
    {
        return $this->discount_price_usd;
    }

    public function getDiscountPriceInBtc(): ?float
    {
        return $this->discount_price_btc;
    }

    public function getCurrentPriceInUsd(?int $quantity = null): float
    {
        $price = $this->shouldApplyDiscount($quantity) 
            ? ($this->discount_price ?? $this->price) 
            : $this->price;
        
        Log::info("Product ID: {$this->id}, Price applied: " . ($price ?: 'N/A'));
        return (float)$price ?: 0.0;
    }

    public function getCurrentPriceInBtc(?int $quantity = null): float
    {
        if ($this->shouldApplyDiscount($quantity)) {
            $price = $this->getDiscountPriceInBtc() ?? $this->getPriceInBtc();
            Log::info("Product ID: {$this->id}, Discounted BTC price applied: {$price}");
        } else {
            $price = $this->getPriceInBtc();
            Log::info("Product ID: {$this->id}, Regular BTC price applied: {$price}");
        }
        return $price;
    }

    public function shouldApplyDiscount(?int $quantity = null): bool
    {
        if ((!$this->discount_price_usd && !$this->discount_price_btc) || !$this->discount_quantity) {
            return false;
        }

        if ($quantity === null) {
            $quantity = $this->getCartQuantity();
        }

        return $quantity >= $this->discount_quantity;
    }

    protected function getCartQuantity(): int
    {
        $user = auth()->user();
        if (!$user || !$user->cart) {
            return 0;
        }

        return $user->cart->items()
            ->where('product_id', $this->id)
            ->sum('quantity');
    }
}