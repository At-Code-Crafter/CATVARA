<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StorePaymentTermRequest;
use App\Http\Requests\Settings\UpdatePaymentTermRequest;
use App\Models\Accounting\PaymentTerm;
use App\Models\Company\Company;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentTermController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'payment-terms');

        if ($request->ajax()) {
            $data = PaymentTerm::forCompany();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="'.company_route('settings.payment-terms.edit', ['payment_term' => $row->id]).'" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit"><i class="fas fa-edit"></i></a>';

                    return $btn;
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-wider border border-emerald-100">Active</span>'
                        : '<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border border-slate-100">Inactive</span>';
                })
                ->rawColumns(['action', 'is_active'])
                ->make(true);
        }

        return view('catvara.settings.payment-terms.index');
    }

    public function create()
    {
        $this->authorize('create', 'payment-terms');

        return view('catvara.settings.payment-terms.form');
    }

    public function store(StorePaymentTermRequest $request)
    {
        $this->authorize('create', 'payment-terms');

        PaymentTerm::create($request->validated() + [
            'company_id' => active_company_id(),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('settings.payment-terms.index', ['company' => active_company()->uuid])->with('success', 'Payment Term created successfully.');
    }

    public function edit(Company $company, $id)
    {
        $this->authorize('edit', 'payment-terms');

        $payment_term = PaymentTerm::where('company_id', $company->id)->findOrFail($id);

        return view('catvara.settings.payment-terms.form', compact('payment_term'));
    }

    public function update(UpdatePaymentTermRequest $request, Company $company, $id)
    {
        $this->authorize('edit', 'payment-terms');

        $payment_term = PaymentTerm::where('company_id', $company->id)->findOrFail($id);
        $payment_term->update($request->validated() + [
            'company_id' => $company->id,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('settings.payment-terms.index', ['company' => active_company()->uuid])->with('success', 'Payment Term updated successfully.');
    }

    public function destroy(Company $company, $id)
    {
        $this->authorize('delete', 'payment-terms');

        $payment_term = PaymentTerm::where('company_id', $company->id)->findOrFail($id);
        $payment_term->delete();

        return redirect()->route('settings.payment-terms.index', ['company' => active_company()->uuid])->with('success', 'Payment Term deleted successfully.');
    }
}
