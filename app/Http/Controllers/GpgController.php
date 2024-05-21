<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Crypt_GPG;

class GpgController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'gpg_key' => 'required|string',
        ]);
    
        $user = Auth::user();
        $user->gpg_key = $request->gpg_key;
        $user->gpg_key_verified = false;
        $user->save();
    
        return redirect()->route('gpg.verify');
    }
    

    public function verify()
    {
        $user = Auth::user();
        if (empty($user->gpg_key)) {
            return back()->withErrors(['error' => 'No GPG key provided. Please upload a key.']);
        }
    
        $gpg = new Crypt_GPG(['homedir' => storage_path('app/.gnupg')]);
        try {
            $importResult = $gpg->importKey($user->gpg_key);
            if (empty($importResult['fingerprint'])) {
                return back()->withErrors(['error' => 'Failed to import GPG key.']);
            }
            $user->gpg_key_fingerprint = $importResult['fingerprint'];
            $user->save();
    
            $gpg->addEncryptKey($importResult['fingerprint']);
            $verificationCode = bin2hex(random_bytes(10));
            $encryptedData = $gpg->encrypt($verificationCode, true);
            session(['verification_code' => $verificationCode]);
    
            return view('settings.gpg_verify', compact('encryptedData'));
        } catch (\Exception $e) {
            \Log::error('GPG Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error processing GPG key: ' . $e->getMessage()]);
        }
    }
    

    public function checkVerification(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string',
        ]);

        $expectedCode = session('verification_code');
        if ($request->verification_code === $expectedCode) {
            $user = Auth::user();
            $user->gpg_key_verified = true;
            $user->save();
            return redirect()->route('settings.gpg')->with('success', 'GPG Key verified successfully.');
        } else {
            return back()->withErrors(['verification_code' => 'Invalid verification code.']);
        }
    }

    public function showGpgSettings()
    {
        $user = Auth::user();
        return view('settings.gpg', compact('user'));
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $user->gpg_key = null;
        $user->gpg_key_verified = false;
        $user->save();

        return redirect()->route('settings.gpg')->with('success', 'GPG Key deleted successfully.');
    }

    public function toggle2fa(Request $request)
    {
        $user = Auth::user();
        $user->two_factor_auth = $request->has('two_factor_auth');
        $user->save();

        return redirect()->route('settings.gpg')->with('success', '2FA settings updated successfully.');
    }
}
