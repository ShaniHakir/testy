<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'pin' => ['required', 'digits:6'],
            'jabber_xmpp' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'password' => $data['password'], // Model will hash this automatically
            'pin' => $data['pin'],
            'role' => 'user',
            'jabber_xmpp' => $data['jabber_xmpp'] ?? null,
            'about' => $data['about'] ?? null,
            'balance' => 0, // Start with 0 balance
        ]);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        Auth::login($user);

        return redirect($this->redirectTo);
    }
}
