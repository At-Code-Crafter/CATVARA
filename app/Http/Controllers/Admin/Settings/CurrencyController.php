<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Pricing\Currency;
use App\Http\Requests\Settings\StoreCurrencyRequest;
use App\Http\Requests\Settings\UpdateCurrencyRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Currency::query();

            return \DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('currencies.edit', $row->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>';
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

        return view('theme.adminlte.settings.currencies.index');
    }

    public function create()
    {
        return view('theme.adminlte.settings.currencies.create');
    }

    public function store(StoreCurrencyRequest $request)
    {
        Currency::create($request->validated() + ['is_active' => $request->has('is_active')]);
        return redirect()->route('currencies.index')->with('success', 'Currency created successfully.');
    }

    public function edit(Currency $currency)
    {
        return view('theme.adminlte.settings.currencies.edit', compact('currency'));
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        $currency->update($request->validated() + ['is_active' => $request->has('is_active')]);
        return redirect()->route('currencies.index')->with('success', 'Currency updated successfully.');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        return redirect()->route('currencies.index')->with('success', 'Currency deleted successfully.');
    }
}
