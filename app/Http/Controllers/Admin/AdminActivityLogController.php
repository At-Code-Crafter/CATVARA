<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Common\ActivityLog;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminActivityLogController extends Controller
{
    /**
     * Display a listing of all activity logs across all companies.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ActivityLog::query()
                ->with(['causer', 'company'])
                ->select('activity_logs.*')
                ->leftJoin('companies', 'activity_logs.company_id', '=', 'companies.id')
                ->orderBy('activity_logs.created_at', 'desc');

            if ($request->filled('event')) {
                $query->where('activity_logs.event', $request->event);
            }

            if ($request->filled('company_id')) {
                $query->where('activity_logs.company_id', $request->company_id);
            }

            return DataTables::eloquent($query)
                ->addColumn('causer_html', function ($log) {
                    if (!$log->causer) {
                        return '<span class="text-slate-400 text-xs">System</span>';
                    }
                    return '
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-black text-slate-500">
                                ' . strtoupper(substr($log->causer->name ?? 'U', 0, 1)) . '
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-slate-700">' . e($log->causer->name) . '</span>
                            </div>
                        </div>';
                })
                ->addColumn('event_html', function ($log) {
                    $bg = 'bg-slate-100 text-slate-600';
                    $event = str_replace(['_', '.'], ' ', $log->event);

                    if (str_contains($log->event, 'created')) $bg = 'bg-emerald-50 text-emerald-600';
                    if (str_contains($log->event, 'updated')) $bg = 'bg-blue-50 text-blue-600';
                    if (str_contains($log->event, 'deleted')) $bg = 'bg-rose-50 text-rose-600';

                    return '<span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider ' . $bg . '">' . e($event) . '</span>';
                })
                ->addColumn('subject_html', function ($log) {
                    $type = str_replace(['App\\Models\\', 'Company\\', 'Customer\\', 'Pricing\\', 'Catalog\\', 'Sales\\', 'Inventory\\'], '', $log->subject_type);
                    return '
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-slate-800">' . e($type) . '</span>
                            <span class="text-[10px] text-slate-400">ID: ' . e($log->subject_id) . '</span>
                        </div>';
                })
                ->addColumn('company_html', function ($log) {
                    if (!$log->company) {
                        return '<span class="text-slate-400">—</span>';
                    }
                    return '
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                                <span class="text-[10px] font-bold text-purple-600">' . substr($log->company->name, 0, 2) . '</span>
                            </div>
                            <span class="text-xs font-medium text-slate-700">' . e($log->company->name) . '</span>
                        </div>';
                })
                ->addColumn('description_html', function ($log) {
                    return '<span class="text-xs text-slate-600">' . e(\Illuminate\Support\Str::limit($log->description, 50)) . '</span>';
                })
                ->addColumn('created_at_html', function ($log) {
                    return '
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-slate-600">' . $log->created_at->format('M d, Y') . '</span>
                            <span class="text-[10px] text-slate-400">' . $log->created_at->format('h:i A') . '</span>
                        </div>';
                })
                ->rawColumns(['causer_html', 'event_html', 'subject_html', 'company_html', 'description_html', 'created_at_html'])
                ->toJson();
        }

        $events = ActivityLog::select('event')->distinct()->orderBy('event')->pluck('event');
        $companies = \App\Models\Company\Company::orderBy('name')->get(['id', 'name']);

        return view('catvara.admin.activity-logs.index', compact('events', 'companies'));
    }
}
