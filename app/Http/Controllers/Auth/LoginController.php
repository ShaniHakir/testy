<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Crypt_GPG;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $credentials = $request->only('username', 'password');
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
    
            if ($user->two_factor_auth && $user->gpg_key_verified && !empty($user->gpg_key_fingerprint)) {
                $gpg = new \Crypt_GPG(['homedir' => storage_path('app/.gnupg')]);
                $gpg->addEncryptKey($user->gpg_key_fingerprint);
    
                $verificationCode = bin2hex(random_bytes(10));
                $encryptedCode = $gpg->encrypt($verificationCode, true);
    
                session(['2fa_code' => $verificationCode, 'auth_user_id' => $user->id]);
    
                Auth::logout();
                return view('auth.two_factor', compact('encryptedCode'));
            } else {
                // Handle the situation where fingerprint is not available
                return back()->withErrors(['error' => 'GPG fingerprint not available.']);
            }
    
            return redirect()->intended('home');
        }
    
        return back()->withErrors(['username' => 'The provided credentials do not match our records.']);
    }
    
    

    public function logout(Request $request)
    {
        Auth::logout();

        return redirect()->route('home');
    }
    public function verifyTwoFactor(Request $request)
{
    $request->validate([
        'verification_code' => 'required|string',
    ]);

    $expectedCode = session('2fa_code');
    $userId = session('auth_user_id');

    if ($request->verification_code === $expectedCode && $userId) {
        Auth::loginUsingId($userId);
        session()->forget(['2fa_code', 'auth_user_id']);

        return redirect()->intended('home');
    }

    return back()->withErrors(['verification_code' => 'Invalid verification code.']);
}

}
