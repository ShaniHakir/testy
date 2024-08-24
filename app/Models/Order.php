<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'Pending';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_REJECTED = 'Rejected';

    protected $fillable = [
        'user_id',
        'total_amount_usd',
        'total_amount_btc',
        'status',
    ];

    protected $casts = [
        'total_amount_usd' => 'decimal:2',
        'total_amount_btc' => 'decimal:8',
    ];
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function allItemsHaveStatus($status)
    {
        return $this->orderItems()->where('status', '!=', $status)->doesntExist();
    }
}