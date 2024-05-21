<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    protected function create(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'password' => $data['password'], // No need to hash here, the model will handle it
            'pin' => $data['pin'],
            'jabber_xmpp' => $data['jabber_xmpp'],
            'about' => $data['about'],
        ]);
    }
    

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        auth()->login($user);

        return redirect()->route('home');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'pin' => ['required', 'integer', 'digits:6'],
            'jabber_xmpp' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
        ]);
    }
}
