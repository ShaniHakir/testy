<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user1_id', 'user2_id', 'last_message_at'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function participants()
    {
        return User::whereIn('id', [$this->user1_id, $this->user2_id]);
    }

    public function getOtherUser($userId)
    {
        return $this->user1_id == $userId ? $this->user2 : $this->user1;
    }

    public function hasParticipant($userId)
    {
        return $this->user1_id == $userId || $this->user2_id == $userId;
    }
}