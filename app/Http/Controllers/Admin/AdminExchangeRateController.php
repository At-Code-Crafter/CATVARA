<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pricing\ExchangeRate;
use App\Models\Pricing\Currency;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminExchangeRateController extends Controller
{
    /**
     * Display a listing of all exchange rates across all companies.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ExchangeRate::with(['company', 'baseCurrency', 'targetCurrency'])
                ->select('exchange_rates.*')
                ->leftJoin('companies', 'exchange_rates.company_id', '=', 'companies.id')
                ->leftJoin('currencies as base_curr', 'exchange_rates.base_currency_id', '=', 'base_curr.id')
                ->leftJoin('currencies as target_curr', 'exchange_rates.target_currency_id', '=', 'target_curr.id');

            return DataTables::eloquent($query)
                ->addColumn('pair_html', function ($rate) {
                    return '
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-600">' . e($rate->baseCurrency->code ?? 'N/A') . '</span>
                            <i class="fas fa-arrow-right text-slate-300 text-xs"></i>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-600">' . e($rate->targetCurrency->code ?? 'N/A') . '</span>
                        </div>';
                })
                ->addColumn('rate_html', function ($rate) {
                    return '<span class="text-lg font-bold text-slate-800">' . number_format($rate->rate, 6) . '</span>';
                })
                ->addColumn('company_html', function ($rate) {
                    if (!$rate->company) {
                        return '<span class="text-slate-400">—</span>';
                    }
                    return '
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-purple-600">' . substr($rate->company->name, 0, 2) . '</span>
                            </div>
                            <span class="text-sm font-medium text-slate-700">' . e($rate->company->name) . '</span>
                        </div>';
                })
                ->addColumn('effective_date_html', function ($rate) {
                    if (!$rate->effective_date) {
                        return '<span class="text-slate-400">—</span>';
                    }
                    return '<span class="text-sm text-slate-600">' . \Carbon\Carbon::parse($rate->effective_date)->format('M d, Y') . '</span>';
                })
                ->addColumn('source_html', function ($rate) {
                    if (!$rate->source) {
                        return '<span class="text-slate-400">—</span>';
                    }
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wider">' . e($rate->source) . '</span>';
                })
                ->rawColumns(['pair_html', 'rate_html', 'company_html', 'effective_date_html', 'source_html'])
                ->toJson();
        }

        return view('catvara.admin.exchange-rates.index');
    }
}
