<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.passwords.change');
    }

    public function change(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Your password has been changed successfully.');
    }
}
