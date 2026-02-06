<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PaymentTerm;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminPaymentTermController extends Controller
{
    /**
     * Display a listing of all payment terms across all companies.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PaymentTerm::query()
                ->select([
                    'payment_terms.id',
                    'payment_terms.code',
                    'payment_terms.name',
                    'payment_terms.due_days',
                    'payment_terms.is_active',
                    'payment_terms.company_id',
                    'companies.name as company_name',
                ])
                ->leftJoin('companies', 'companies.id', '=', 'payment_terms.company_id');

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('name_html', function ($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-lg bg-teal-50 text-teal-500 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-sm"></i>
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

                ->addColumn('due_days_html', function ($row) {
                    $label = $row->due_days == 0 ? 'Due Immediately' : $row->due_days . ' days';
                    $color = $row->due_days == 0 ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600';
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-lg ' . $color . ' text-xs font-bold">' . $label . '</span>';
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

                ->rawColumns(['name_html', 'company_html', 'due_days_html', 'status_html'])
                ->make(true);
        }

        return view('catvara.admin.payment-terms.index');
    }
}
