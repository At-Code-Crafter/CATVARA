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
        $this->authorize('view', 'currencies');

        if ($request->ajax()) {
            $data = Currency::query();

            return \DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name_html', function ($currency) {
                    return '
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                <span class="text-lg font-bold text-amber-600">' . e($currency->symbol ?: '$') . '</span>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">' . e($currency->name) . '</p>
                            </div>
                        </div>';
                })
                ->addColumn('code_html', function ($currency) {
                    return '<span class="px-2.5 py-1 text-xs font-bold rounded bg-slate-100 text-slate-700">' . e($currency->code) . '</span>';
                })
                ->addColumn('symbol_html', function ($currency) {
                    if (!$currency->symbol) {
                        return '<span class="text-slate-400">—</span>';
                    }
                    return '<span class="text-lg font-semibold text-slate-700">' . e($currency->symbol) . '</span>';
                })
                ->addColumn('decimals_html', function ($currency) {
                    return '<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold bg-blue-50 text-blue-600">' . $currency->decimal_places . '</span>';
                })
                ->addColumn('status_html', function ($currency) {
                    return $currency->is_active
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active</span>'
                        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactive</span>';
                })
                ->addColumn('actions', function ($currency) {
                    $editUrl = route('currencies.edit', $currency->id);
                    $deleteUrl = route('currencies.destroy', $currency->id);
                    return '
                        <div class="flex items-center justify-end gap-1">
                            <a href="' . $editUrl . '" class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <button type="button" class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all btn-delete" data-url="' . $deleteUrl . '" title="Delete">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['name_html', 'code_html', 'symbol_html', 'decimals_html', 'status_html', 'actions'])
                ->make(true);
        }

        return view('catvara.admin.currencies.index');
    }

    public function create()
    {
        $this->authorize('create', 'currencies');

        return view('theme.adminlte.settings.currencies.create');
    }

    public function store(StoreCurrencyRequest $request)
    {
        $this->authorize('create', 'currencies');

        Currency::create($request->validated() + ['is_active' => $request->has('is_active')]);
        return redirect()->route('currencies.index')->with('success', 'Currency created successfully.');
    }

    public function edit(Currency $currency)
    {
        $this->authorize('edit', 'currencies');

        return view('theme.adminlte.settings.currencies.edit', compact('currency'));
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        $this->authorize('edit', 'currencies');

        $currency->update($request->validated() + ['is_active' => $request->has('is_active')]);
        return redirect()->route('currencies.index')->with('success', 'Currency updated successfully.');
    }

    public function destroy(Currency $currency)
    {
        $this->authorize('delete', 'currencies');

        $currency->delete();
        return redirect()->route('currencies.index')->with('success', 'Currency deleted successfully.');
    }
}
