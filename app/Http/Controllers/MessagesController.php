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
        $messages = Auth::user()->receivedMessages()->with('sender')->get();
        return view('messages.index', compact('messages'));
    }

    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('messages.create', compact('users'));
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

        $message->update(['read' => true]);

        return view('messages.show', compact('message'));
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
        return redirect()->route('messages.index')->with('success', 'All messages deleted successfully.');
    }
}