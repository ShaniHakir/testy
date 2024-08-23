<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddConversationIdToMessagesTable extends Migration
{
    public function up()
    {
        // 1. Remove the foreign key constraint on recipient_id if it exists
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['recipient_id']);
        });

        // 2. Remove the recipient_id column
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('recipient_id');
        });

        // 3. Ensure all messages have a valid conversation_id
        $messages = DB::table('messages')
            ->leftJoin('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->whereNull('conversations.id')
            ->select('messages.*')
            ->get();

        foreach ($messages as $message) {
            $conversation = DB::table('conversations')
                ->where(function ($query) use ($message) {
                    $query->where('user1_id', $message->sender_id)
                          ->where('user2_id', $message->recipient_id);
                })
                ->orWhere(function ($query) use ($message) {
                    $query->where('user1_id', $message->recipient_id)
                          ->where('user2_id', $message->sender_id);
                })
                ->first();

            if (!$conversation) {
                $conversationId = DB::table('conversations')->insertGetId([
                    'user1_id' => $message->sender_id,
                    'user2_id' => $message->recipient_id,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                    'last_message_at' => $message->created_at,
                ]);
            } else {
                $conversationId = $conversation->id;
            }

            DB::table('messages')
                ->where('id', $message->id)
                ->update(['conversation_id' => $conversationId]);
        }

        // 4. Make conversation_id not nullable (if it's nullable)
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('conversation_id')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('recipient_id')->nullable()->after('sender_id');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
}