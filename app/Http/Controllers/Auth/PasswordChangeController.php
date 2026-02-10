<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordChangeRequest;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.passwords.change');
    }

    public function change(PasswordChangeRequest $request)
    {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->validated()['password']),
            'password_changed_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Your password has been changed successfully.');
    }
}
