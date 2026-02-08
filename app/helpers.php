<?php

use App\Models\Company\Company;
use Illuminate\Support\Facades\Route;

/**
 * NOTE:
 * - Keep this file in your autoload "files" (composer.json) and run: composer dump-autoload
 * - This helper set is intentionally simple and safe (no extra engineering).
 */
if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        // Keep your current placeholder logic as-is.
        // If you enable settings later, you can restore the cached implementation.
        return $default;
    }
}

/**
 * Current selected company id (from session)
 */
if (!function_exists('active_company_id')) {
    function active_company_id(): ?int
    {
        $id = session('current_company_id');

        return $id ? (int) $id : null;
    }
}

/**
 * Whether a company is selected
 */
if (!function_exists('company_selected')) {
    function company_selected(): bool
    {
        return (bool) active_company_id();
    }
}

/**
 * Selected company model (minimal columns; avoids heavy loads)
 */
if (!function_exists('active_company')) {
    function active_company(): ?Company
    {
        static $memo = null;
        static $loaded = false;

        if ($loaded) {
            return $memo;
        }

        $loaded = true;

        $id = active_company_id();
        if (!$id) {
            return $memo = null;
        }

        return $memo = Company::query()
            ->select('id', 'uuid', 'name', 'code', 'company_status_id', 'logo')
            ->find($id);
    }
}
/**
 * Whether current user can switch company (has > 1 company)
 */
if (!function_exists('can_switch_company')) {
    function can_switch_company(): bool
    {
        static $memo = null;
        if ($memo !== null) {
            return $memo;
        }

        $user = auth()->user();
        if (!$user) {
            return $memo = false;
        }

        return $memo = ($user->companies()->count() > 1);
    }
}

/**
 * Get all companies for current user (for dropdowns in footer/header if needed)
 */
if (!function_exists('my_companies')) {
    function my_companies()
    {
        static $memo = null;
        static $loaded = false;

        if ($loaded) {
            return $memo;
        }

        $loaded = true;

        $user = auth()->user();
        if (!$user) {
            return $memo = collect();
        }

        return $memo = $user->companies()
            ->select('companies.id', 'companies.uuid', 'companies.name', 'companies.code')
            ->orderBy('companies.name')
            ->get();
    }
}

/**
 * Safe route generator for company-scoped routes.
 * - returns fallback if route missing OR company missing
 * - never throws RouteNotFoundException
 *
 * Usage:
 *  company_route('company.dashboard')  // auto injects company uuid
 *  company_route('company.settings.roles.index')
 */
if (!function_exists('company_route')) {
    function company_route(string $name, array $params = [], string $fallback = '#'): string
    {
        if (!Route::has($name)) {
            return $fallback;
        }

        $company = active_company();
        if (!$company) {
            return $fallback;
        }

        return route($name, array_merge(['company' => $company->uuid], $params));
    }
}

/**
 * Safe route generator for non-company routes (settings/auth/etc)
 */
if (!function_exists('safe_route')) {
    function safe_route(string $name, array $params = [], string $fallback = '#'): string
    {
        return Route::has($name) ? route($name, $params) : $fallback;
    }
}

/**
 * Convenience: active company name/code for UI
 */
if (!function_exists('active_company_label')) {
    function active_company_label(string $fallback = 'No company selected'): string
    {
        $c = active_company();
        if (!$c) {
            return $fallback;
        }

        $code = $c->code ? " ({$c->code})" : '';

        return $c->name . $code;
    }
}

/**
 * Automatically prefixes table names in a raw SQL string.
 * It looks for "table.column" patterns and prepends the DB prefix if needed.
 *
 * Example: db_raw("SUM(order_items.quantity)") -> DB::raw("SUM(prefix_order_items.quantity)")
 * Note: This uses a dot heuristic. It may prefix aliases if you use them with dots.
 */
if (!function_exists('db_raw')) {
    function db_raw(string $expression)
    {
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        if (empty($prefix)) {
            return \Illuminate\Support\Facades\DB::raw($expression);
        }

        // Regex strategy:
        // 1. Skip strings in single quotes ('...') using (*SKIP)(*F)
        // 2. Match table-like identifiers followed by a dot and another identifier (column, *, or backticked name)
        // 3. Prefix the table name if it's not already prefixed
        $pattern = '/\'[^\']*\'(*SKIP)(*F)|(?<![a-zA-Z0-9_])([a-zA-Z_][a-zA-Z0-9_]*)(\s*\.\s*)(?=[a-zA-Z0-9_`"\'*]+)/';

        $prefixed = preg_replace_callback($pattern, function ($matches) use ($prefix) {
            $table = $matches[1];
            $separator = $matches[2];

            // If the table is already prefixed, don't prefix again.
            if (str_starts_with($table, $prefix)) {
                return $table . $separator;
            }

            return $prefix . $table . $separator;
        }, $expression);

        return \Illuminate\Support\Facades\DB::raw($prefixed);
    }
}

/**
 * Robust money formatter
 */
if (!function_exists('money')) {
    function money($amount, $currency = 'USD')
    {
        $amount = (float) $amount;

        // Simple but effective formatting
        // In a real app, this could use NumberFormatter for locale-specific display
        return ($currency ?: 'USD') . ' ' . number_format($amount, 2);
    }
}
/**
 * Get public URL for a storage path
 */
if (!function_exists('storage_url')) {
    function storage_url($path)
    {
        if (!$path)
            return null;
        return asset('storage/' . $path);
    }
}
