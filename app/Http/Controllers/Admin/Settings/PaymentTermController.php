<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StorePaymentTermRequest;
use App\Http\Requests\Settings\UpdatePaymentTermRequest;
use App\Models\Accounting\PaymentTerm;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PaymentTerm::query();

            return \DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="'.route('payment-terms.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>';

                    // Add delete button if needed, often handled via form or JS
                    // $btn .= ' <button ... class="btn btn-danger btn-sm delete-btn" ...><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-danger">Inactive</span>';
                })
                ->rawColumns(['action', 'is_active'])
                ->make(true);
        }

        return view('theme.adminlte.settings.payment_terms.index');
    }

    public function create()
    {
        return view('theme.adminlte.settings.payment_terms.create');
    }

    public function store(StorePaymentTermRequest $request)
    {
        PaymentTerm::create($request->validated() + ['is_active' => $request->has('is_active')]);

        return redirect()->route('payment-terms.index')->with('success', 'Payment Term created successfully.');
    }

    public function edit(PaymentTerm $payment_term)
    {
        return view('theme.adminlte.settings.payment_terms.edit', compact('payment_term'));
    }

    public function update(UpdatePaymentTermRequest $request, PaymentTerm $payment_term)
    {
        $payment_term->update($request->validated() + ['is_active' => $request->has('is_active')]);

        return redirect()->route('payment-terms.index')->with('success', 'Payment Term updated successfully.');
    }

    public function destroy(PaymentTerm $payment_term)
    {
        $payment_term->delete();

        return redirect()->route('payment-terms.index')->with('success', 'Payment Term deleted successfully.');
    }
}
