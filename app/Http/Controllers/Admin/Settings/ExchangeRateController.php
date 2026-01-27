<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Pricing\Currency;
use App\Models\Pricing\ExchangeRate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ExchangeRateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'exchange-rates');

        if ($request->ajax()) {
            $rates = ExchangeRate::where('company_id', $request->company->id)
                ->with(['baseCurrency', 'targetCurrency'])
                ->latest();

            return DataTables::eloquent($rates)
                ->addColumn('pair_html', function ($rate) {
                    return '<div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-brand-50 text-brand-600 border border-brand-100">
                            ' . e($rate->baseCurrency->code ?? 'N/A') . '
                        </span>
                        <i class="fas fa-arrow-right text-slate-300"></i>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                            ' . e($rate->targetCurrency->code ?? 'N/A') . '
                        </span>
                    </div>';
                })
                ->addColumn('rate_html', function ($rate) {
                    return '<span class="text-lg font-bold text-slate-800">' . number_format($rate->rate, 6) . '</span>';
                })
                ->addColumn('effective_date_html', function ($rate) {
                    return '<span class="text-sm text-slate-600">' . ($rate->effective_date ? \Carbon\Carbon::parse($rate->effective_date)->format('M d, Y') : '—') . '</span>';
                })
                ->addColumn('source_html', function ($rate) {
                    if (!$rate->source) {
                        return '<span class="text-slate-300">—</span>';
                    }
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wider">' . e($rate->source) . '</span>';
                })
                ->addColumn('actions', function ($rate) {
                    $editUrl = company_route('settings.exchange-rates.edit', ['exchange_rate' => $rate->id]);
                    $deleteUrl = company_route('settings.exchange-rates.destroy', ['exchange_rate' => $rate->id]);
                    return '<div class="flex items-center justify-end gap-1">
                        <a href="' . $editUrl . '" class="p-2 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this rate?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>';
                })
                ->rawColumns(['pair_html', 'rate_html', 'effective_date_html', 'source_html', 'actions'])
                ->make(true);
        }

        return view('catvara.settings.exchange-rates.index');
    }

    public function create(Request $request)
    {
        $this->authorize('create', 'exchange-rates');

        $currencies = Currency::orderBy('code')->get();
        $companyCurrency = $request->company->currency;

        return view('catvara.settings.exchange-rates.form', compact('currencies', 'companyCurrency'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', 'exchange-rates');

        $validated = $request->validate([
            'base_currency_id' => 'required|exists:currencies,id',
            'target_currency_id' => 'required|exists:currencies,id|different:base_currency_id',
            'rate' => 'required|numeric|min:0.00000001',
            'effective_date' => 'nullable|date',
            'source' => 'nullable|string|max:100',
        ]);

        $rate = new ExchangeRate();
        $rate->company_id = $request->company->id;
        $rate->base_currency_id = $validated['base_currency_id'];
        $rate->target_currency_id = $validated['target_currency_id'];
        $rate->rate = $validated['rate'];
        $rate->effective_date = $validated['effective_date'] ?? now();
        $rate->source = $validated['source'] ?? 'manual';
        $rate->save();

        return redirect(company_route('settings.exchange-rates.index'))
            ->with('success', 'Exchange rate created successfully.');
    }

    public function edit(Request $request, Company $company, ExchangeRate $exchangeRate)
    {
        $this->authorize('edit', 'exchange-rates');

        if ($exchangeRate->company_id !== $company->id) {
            abort(403);
        }

        $currencies = Currency::orderBy('code')->get();
        $companyCurrency = $company->currency;

        return view('catvara.settings.exchange-rates.form', [
            'exchangeRate' => $exchangeRate,
            'currencies' => $currencies,
            'companyCurrency' => $companyCurrency,
        ]);
    }

    public function update(Request $request, Company $company, ExchangeRate $exchangeRate)
    {
        $this->authorize('edit', 'exchange-rates');

        if ($exchangeRate->company_id !== $company->id) {
            abort(403);
        }

        $validated = $request->validate([
            'base_currency_id' => 'required|exists:currencies,id',
            'target_currency_id' => 'required|exists:currencies,id|different:base_currency_id',
            'rate' => 'required|numeric|min:0.00000001',
            'effective_date' => 'nullable|date',
            'source' => 'nullable|string|max:100',
        ]);

        $exchangeRate->base_currency_id = $validated['base_currency_id'];
        $exchangeRate->target_currency_id = $validated['target_currency_id'];
        $exchangeRate->rate = $validated['rate'];
        $exchangeRate->effective_date = $validated['effective_date'] ?? $exchangeRate->effective_date;
        $exchangeRate->source = $validated['source'] ?? $exchangeRate->source;
        $exchangeRate->save();

        return redirect(company_route('settings.exchange-rates.index'))
            ->with('success', 'Exchange rate updated successfully.');
    }

    public function destroy(Company $company, ExchangeRate $exchangeRate)
    {
        $this->authorize('delete', 'exchange-rates');

        if ($exchangeRate->company_id !== $company->id) {
            abort(403);
        }

        $exchangeRate->delete();

        return redirect(company_route('settings.exchange-rates.index'))
            ->with('success', 'Exchange rate deleted successfully.');
    }
}
