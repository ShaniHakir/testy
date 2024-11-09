<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MoneroWallet extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_address',
        'address_index',
        'last_block_height',
        'balance'
    ];

    protected $casts = [
        'balance' => 'decimal:12',
        'last_block_height' => 'integer',
        'address_index' => 'integer',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(MoneroTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Get confirmed incoming transactions total
     */
    public function getConfirmedIncomingTotal(): string
    {
        return $this->transactions()
            ->where('type', 'incoming')
            ->where('is_confirmed', true)
            ->where('address', $this->wallet_address) // Only count transactions to this subaddress
            ->sum('amount');
    }

    /**
     * Get confirmed outgoing transactions total
     */
    public function getConfirmedOutgoingTotal(): string
    {
        return $this->transactions()
            ->where('type', 'outgoing')
            ->where('is_confirmed', true)
            ->where(function($query) {
                $query->whereNull('address') // For older transactions that might not have address
                    ->orWhere('address', $this->wallet_address); // Only count transactions from this subaddress
            })
            ->sum('amount');
    }

    /**
     * Get available balance (confirmed incoming - confirmed outgoing)
     */
    public function getAvailableBalance(): string
    {
        $incoming = $this->getConfirmedIncomingTotal();
        $outgoing = $this->getConfirmedOutgoingTotal();
        return bcsub($incoming, $outgoing, 12);
    }

    /**
     * Get pending incoming transactions
     */
    public function getPendingIncoming()
    {
        return $this->transactions()
            ->where('type', 'incoming')
            ->where('is_confirmed', false)
            ->where('address', $this->wallet_address)
            ->get();
    }

    /**
     * Get pending outgoing transactions
     */
    public function getPendingOutgoing()
    {
        return $this->transactions()
            ->where('type', 'outgoing')
            ->where('is_confirmed', false)
            ->where(function($query) {
                $query->whereNull('address')
                    ->orWhere('address', $this->wallet_address);
            })
            ->get();
    }

    /**
     * Get confirmed transactions
     */
    public function getConfirmedTransactions()
    {
        return $this->transactions()
            ->where('is_confirmed', true)
            ->where(function($query) {
                $query->where('address', $this->wallet_address) // Incoming transactions to this address
                    ->orWhere(function($q) { // Outgoing transactions from this address
                        $q->where('type', 'outgoing')
                          ->where(function($q2) {
                              $q2->whereNull('address')
                                 ->orWhere('address', $this->wallet_address);
                          });
                    });
            })
            ->orderBy('confirmed_height', 'desc')
            ->get();
    }

    /**
     * Check if wallet has sufficient funds for a transaction
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return bccomp($this->getAvailableBalance(), $amount, 12) >= 0;
    }

    /**
     * Update last scanned block height
     */
    public function updateLastBlockHeight(int $height): void
    {
        if ($height > $this->last_block_height) {
            $this->last_block_height = $height;
            $this->save();
        }
    }
}
