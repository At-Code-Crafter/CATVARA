<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentCompanyFromRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->route('company');

        if ($company && $company instanceof \App\Models\Company\Company) {
            session(['current_company_id' => $company->id]);
            // Also share with views immediately for this request lifecyle
            // View::share('currentCompany', $company); // Optional but good practice
        }

        return $next($request);
    }
}
