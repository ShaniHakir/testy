<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'content', 'read'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->conversation->participants()->where('users.id', '!=', $this->sender_id)->first();
    }

    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public static function unreadCountForUser($userId)
    {
        return static::whereHas('conversation', function ($query) use ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('user1_id', $userId)
                  ->orWhere('user2_id', $userId);
            });
        })
        ->where('sender_id', '!=', $userId)
        ->where('read', false)
        ->count();
    }
}