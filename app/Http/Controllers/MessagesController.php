<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    public function index()
    {
        $messages = Auth::user()->receivedMessages()->with('sender')->latest()->get();
        $unreadCount = Auth::user()->receivedMessages()->unread()->count();
        return view('messages.index', compact('messages', 'unreadCount'));
    }

    public function create(Request $request)
    {
        $recipient = $request->query('recipient');
        return view('messages.create', compact('recipient'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        if ($request->has('reply_to')) {
            // This is a reply
            $originalMessage = Message::findOrFail($request->reply_to);
            $recipient = $originalMessage->sender;
        } else {
            // This is a new message
            $request->validate([
                'username' => 'required|exists:users,username',
            ]);
            $recipient = User::where('username', $request->username)->firstOrFail();
        }

        // Check if the recipient is the same as the sender
        if ($recipient->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot send a message to yourself.')->withInput();
        }

        Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $recipient->id,
            'content' => $request->content,
        ]);

        return redirect()->route('messages.index')->with('success', 'Message sent successfully.');
    }

    public function show(Message $message)
    {
        if ($message->recipient_id !== Auth::id()) {
            abort(403);
        }
    
        if (!$message->read) {
            $message->update(['read' => true]);
        }
    
        return view('messages.show', compact('message'));
    }

    public function markAllAsRead()
    {
        Auth::user()->receivedMessages()->unread()->update(['read' => true]);
        return redirect()->route('messages.index')->with('success', 'All messages marked as read.');
    }

    public function destroy(Message $message)
    {
        if ($message->recipient_id !== Auth::id()) {
            abort(403);
        }
        $message->delete();
        return redirect()->route('messages.index')->with('success', 'Message deleted successfully.');
    }
    
    public function deleteAll()
    {
        Auth::user()->receivedMessages()->delete();
        return redirect()->route('messages.index')->with('success', 'All your messages have been deleted successfully.');
    }
}