<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoneroTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'tx_hash',
        'amount',
        'type', // 'deposit' or 'withdrawal'
        'is_confirmed',
    ];

    protected $casts = [
        'amount' => 'decimal:12',
        'is_confirmed' => 'boolean',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if transaction is a deposit
     */
    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    /**
     * Check if transaction is a withdrawal
     */
    public function isWithdrawal(): bool
    {
        return $this->type === 'withdrawal';
    }

    /**
     * Format amount for display
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 12, '.', ',') . ' XMR';
    }

    /**
     * Get status text
     */
    public function getStatusText(): string
    {
        if ($this->is_confirmed) {
            return 'Confirmed';
        }
        
        return $this->isDeposit() ? 'Pending Confirmation' : 'Processing';
    }
}
