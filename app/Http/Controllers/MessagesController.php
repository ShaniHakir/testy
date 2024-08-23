<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    public function index()
    {
        $conversations = Conversation::where('user1_id', Auth::id())
            ->orWhere('user2_id', Auth::id())
            ->orderBy('last_message_at', 'desc')
            ->get();

        $unreadCount = Message::unreadCountForUser(Auth::id());

        return view('messages.index', compact('conversations', 'unreadCount'));
    }

    public function show(Conversation $conversation)
    {
        if (!$conversation->hasParticipant(Auth::id())) {
            abort(403, 'You do not have permission to view this conversation.');
        }

        $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();
        $otherUser = $conversation->getOtherUser(Auth::id());

        // Mark all unread messages in this conversation as read
        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);

        return view('messages.show', compact('conversation', 'messages', 'otherUser'));
    }

    public function create()
    {
        return view('messages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'recipient' => 'required_without:conversation_id|exists:users,username',
            'conversation_id' => 'required_without:recipient|exists:conversations,id',
        ]);

        if ($request->has('conversation_id')) {
            // Replying to an existing conversation
            $conversation = Conversation::findOrFail($request->conversation_id);
            
            if (!$conversation->hasParticipant(Auth::id())) {
                abort(403, 'You do not have permission to send a message in this conversation.');
            }
        } else {
            // Starting a new conversation
            $recipient = User::where('username', $request->recipient)->firstOrFail();
            
            if ($recipient->id === Auth::id()) {
                return redirect()->back()->with('error', 'You cannot start a conversation with yourself.');
            }

            $conversation = Conversation::firstOrCreate(
                [
                    'user1_id' => min(Auth::id(), $recipient->id),
                    'user2_id' => max(Auth::id(), $recipient->id),
                ],
                ['last_message_at' => now()]
            );
        }

        $message = new Message([
            'sender_id' => Auth::id(),
            'content' => $request->content,
            'read' => false,
        ]);

        $conversation->messages()->save($message);
        $conversation->update(['last_message_at' => now()]);

        return redirect()->route('messages.show', $conversation)
            ->with('success', 'Message sent successfully.');
    }

    public function destroy(Message $message)
    {
        if ($message->sender_id !== Auth::id()) {
            abort(403, 'You do not have permission to delete this message.');
        }

        $message->delete();

        return redirect()->back()->with('success', 'Message deleted successfully.');
    }

    public function deleteAll()
    {
        $conversationIds = Conversation::where('user1_id', Auth::id())
            ->orWhere('user2_id', Auth::id())
            ->pluck('id');

        Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', Auth::id())
            ->delete();

        return redirect()->route('messages.index')->with('success', 'All your messages deleted successfully.');
    }

    public function destroyConversation(Conversation $conversation)
    {
        if (!$conversation->hasParticipant(Auth::id())) {
            abort(403, 'You do not have permission to delete this conversation.');
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return redirect()->route('messages.index')->with('success', 'Conversation deleted successfully.');
    }

    public function deleteAllConversations()
    {
        $conversationIds = Conversation::where('user1_id', Auth::id())
            ->orWhere('user2_id', Auth::id())
            ->pluck('id');

        Message::whereIn('conversation_id', $conversationIds)->delete();
        Conversation::whereIn('id', $conversationIds)->delete();

        return redirect()->route('messages.index')->with('success', 'All conversations deleted successfully.');
    }

    public function markAllAsRead()
    {
        $conversationIds = Conversation::where('user1_id', Auth::id())
            ->orWhere('user2_id', Auth::id())
            ->pluck('id');

        Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);

        return redirect()->route('messages.index')->with('success', 'All messages marked as read.');
    }
}