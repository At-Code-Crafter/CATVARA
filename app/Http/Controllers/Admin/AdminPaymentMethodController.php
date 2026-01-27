<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PaymentMethod;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminPaymentMethodController extends Controller
{
    /**
     * Display a listing of all payment methods across all companies.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PaymentMethod::query()
                ->select([
                    'payment_methods.id',
                    'payment_methods.code',
                    'payment_methods.name',
                    'payment_methods.type',
                    'payment_methods.is_active',
                    'payment_methods.allow_refund',
                    'payment_methods.requires_reference',
                    'payment_methods.company_id',
                    'companies.name as company_name',
                ])
                ->leftJoin('companies', 'companies.id', '=', 'payment_methods.company_id');

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('name_html', function ($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center">
                            <i class="fas fa-credit-card text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">' . e($row->name) . '</p>
                            <p class="text-xs text-slate-400 font-mono">' . e($row->code) . '</p>
                        </div>
                    </div>';
                })

                ->addColumn('company_html', function ($row) {
                    if ($row->company_name) {
                        return '
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-600 text-xs font-bold">
                            <i class="fas fa-building text-[10px]"></i> ' . e($row->company_name) . '
                        </span>';
                    }
                    return '<span class="text-slate-400 text-xs">—</span>';
                })

                ->addColumn('type_html', function ($row) {
                    $colors = [
                        'CASH' => 'bg-emerald-50 text-emerald-600',
                        'CARD' => 'bg-blue-50 text-blue-600',
                        'GATEWAY' => 'bg-purple-50 text-purple-600',
                        'BANK' => 'bg-indigo-50 text-indigo-600',
                        'WALLET' => 'bg-amber-50 text-amber-600',
                        'CREDIT' => 'bg-rose-50 text-rose-600',
                    ];
                    $labels = [
                        'CASH' => 'Cash',
                        'CARD' => 'Card',
                        'GATEWAY' => 'Gateway',
                        'BANK' => 'Bank',
                        'WALLET' => 'Wallet',
                        'CREDIT' => 'Credit',
                    ];
                    $color = $colors[$row->type] ?? 'bg-slate-50 text-slate-600';
                    $label = $labels[$row->type] ?? $row->type;

                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-lg ' . $color . ' text-xs font-bold">' . $label . '</span>';
                })

                ->addColumn('features_html', function ($row) {
                    $badges = [];
                    if ($row->allow_refund) {
                        $badges[] = '<span class="inline-flex items-center px-2 py-0.5 rounded bg-green-50 text-green-600 text-[10px] font-bold">Refund</span>';
                    }
                    if ($row->requires_reference) {
                        $badges[] = '<span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-bold">Ref</span>';
                    }
                    return implode(' ', $badges) ?: '<span class="text-slate-300">—</span>';
                })

                ->addColumn('status_html', function ($row) {
                    return $row->is_active
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                          </span>'
                        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                          </span>';
                })

                ->rawColumns(['name_html', 'company_html', 'type_html', 'features_html', 'status_html'])
                ->make(true);
        }

        return view('catvara.admin.payment-methods.index');
    }
}
