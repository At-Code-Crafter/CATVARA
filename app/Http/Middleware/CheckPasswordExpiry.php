<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            
            // Allow access to password change and logout routes
            if (!$request->is('*/auth/password/change*') && !$request->is('logout')) {
                if ($user instanceof \App\Models\User && $user->hasPasswordExpired()) {
                    $company = active_company();
                    return redirect()->route('auth.password.change', ['company' => $company->uuid])
                        ->with('warning', 'Your password has expired. Please change it to continue.');
                }
            }
        }

        return $next($request);
    }
}
