<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function password()
    {
        return view('settings.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
    
        $user = Auth::user();
    
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect']);
        }
    
        $user->password = $request->new_password;
        $user->save();
    
        return back()->with('status', 'Password updated successfully');
    }
    
    public function pin()
    {
        return view('settings.pin');
    }

    public function updatePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|integer',
            'new_pin' => 'required|integer|digits:6|confirmed',
        ]);

        $user = Auth::user();

        if ($request->current_pin != $user->pin) {
            return back()->withErrors(['current_pin' => 'The current PIN is incorrect']);
        }

        $user->pin = $request->new_pin;
        $user->save();

        return back()->with('status', 'PIN updated successfully');
    }

    public function jabber()
    {
        return view('settings.jabber');
    }

    public function updateJabber(Request $request)
    {
        $request->validate([
            'jabber_xmpp' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $user->jabber_xmpp = $request->jabber_xmpp;
        $user->save();

        return back()->with('status', 'Jabber/XMPP address updated successfully');
    }

    public function about()
    {
        return view('settings.about');
    }

    public function updateAbout(Request $request)
    {
        $request->validate([
            'about' => 'nullable|string',
        ]);

        $user = Auth::user();
        $user->about = $request->about;
        $user->save();

        return back()->with('status', 'About information updated successfully');
    }
}
