<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Wallet;  // Ensure you import the Wallet model

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'pin',
        'jabber_xmpp',
        'role',
        'about',
        'is_banned',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Automatically hash the password when it is set.
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

    /**
     * Get the wallet associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Check if the user is a vendor.
     *
     * @return bool
     */
    public function isVendor()
    {
        return $this->role === 'vendor';
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'user1_id')
            ->orWhere('user2_id', $this->id);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return Message::whereHas('conversation', function ($query) {
            $query->where('user1_id', $this->id)
                  ->orWhere('user2_id', $this->id);
        })->where('sender_id', '!=', $this->id);
    }

    public function unreadMessages()
    {
        return $this->receivedMessages()->where('read', false);
    }

    public function cart()
    {
    return $this->hasOne(Cart::class);
    }
    public function orders()
    {
    return $this->hasMany(Order::class);
    }
}
