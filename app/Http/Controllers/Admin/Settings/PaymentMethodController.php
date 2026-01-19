<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PaymentMethod;
use App\Models\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $companyId = active_company_id();
            $query = PaymentMethod::where('company_id', $companyId);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('type_badge', function ($row) {
                    $colors = [
                        'CASH' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                        'CARD' => 'bg-blue-50 text-blue-600 border-blue-100',
                        'GATEWAY' => 'bg-purple-50 text-purple-600 border-purple-100',
                        'BANK' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                        'WALLET' => 'bg-amber-50 text-amber-600 border-amber-100',
                        'CREDIT' => 'bg-rose-50 text-rose-600 border-rose-100',
                    ];
                    $color = $colors[$row->type] ?? 'bg-slate-50 text-slate-600 border-slate-100';

                    return '<span class="px-3 py-1.5 rounded-lg '.$color.' text-xs font-black uppercase tracking-wider border">'.$row->type_label.'</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-wider border border-emerald-100">Active</span>'
                        : '<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border border-slate-100">Inactive</span>';
                })
                ->addColumn('features', function ($row) {
                    $badges = [];
                    if ($row->allow_refund) {
                        $badges[] = '<span class="px-2 py-0.5 rounded bg-green-50 text-green-600 text-[10px] font-bold border border-green-100">Refund</span>';
                    }
                    if ($row->requires_reference) {
                        $badges[] = '<span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100">Ref Required</span>';
                    }

                    return implode(' ', $badges) ?: '<span class="text-slate-300">—</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = company_route('settings.payment-methods.edit', ['payment_method' => $row->id]);

                    return '
                        <a href="'.$editUrl.'" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    ';
                })
                ->rawColumns(['type_badge', 'status_badge', 'features', 'actions'])
                ->make(true);
        }

        return view('catvara.settings.payment-methods.index');
    }

    /**
     * Show the form for creating a new payment method.
     */
    public function create()
    {
        return view('catvara.settings.payment-methods.form');
    }

    /**
     * Store a newly created payment method.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:payment_methods,code|alpha_dash:ascii',
            'name' => 'required|string|max:255',
            'type' => 'required|in:CASH,CARD,GATEWAY,BANK,WALLET,CREDIT',
            'is_active' => 'boolean',
            'allow_refund' => 'boolean',
            'requires_reference' => 'boolean',
        ]);

        PaymentMethod::create([
            'company_id' => active_company_id(),
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => $request->has('is_active'),
            'allow_refund' => $request->has('allow_refund'),
            'requires_reference' => $request->has('requires_reference'),
        ]);

        return redirect()->route('settings.payment-methods.index', ['company' => active_company()->uuid])
            ->with('success', 'Payment method created successfully.');
    }

    /**
     * Show the form for editing the specified payment method.
     */
    public function edit(Company $company, $id)
    {
        $paymentMethod = PaymentMethod::where('company_id', $company->id)->findOrFail($id);

        return view('catvara.settings.payment-methods.form', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, Company $company, $id)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash:ascii', Rule::unique('payment_methods')->ignore($id)],
            'name' => 'required|string|max:255',
            'type' => 'required|in:CASH,CARD,GATEWAY,BANK,WALLET,CREDIT',
            'is_active' => 'boolean',
            'allow_refund' => 'boolean',
            'requires_reference' => 'boolean',
        ]);
        $paymentMethod = PaymentMethod::where('company_id', $company->id)->findOrFail($id);

        $paymentMethod->update([
            'company_id' => $company->id,
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => $request->has('is_active'),
            'allow_refund' => $request->has('allow_refund'),
            'requires_reference' => $request->has('requires_reference'),
        ]);

        return redirect()->route('settings.payment-methods.index', ['company' => active_company()->uuid])
            ->with('success', 'Payment method updated successfully.');
    }

    /**
     * Remove the specified payment method.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Check if payment method is in use
        if ($paymentMethod->payments()->exists()) {
            return back()->with('error', 'Cannot delete payment method that has associated payments.');
        }

        $paymentMethod->delete();

        return redirect()->route('settings.payment-methods.index', ['company' => active_company()->uuid])
            ->with('success', 'Payment method deleted successfully.');
    }
}
