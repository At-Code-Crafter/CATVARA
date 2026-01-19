<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Common\ActivityLog;
use App\Models\Company\Company;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs for the active company.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ActivityLog::query()
                ->where('company_id', active_company_id())
                ->with(['causer']) // We can't easily with subject because morphTo with many possibilities
                ->orderBy('created_at', 'desc');

            if ($request->filled('event')) {
                $query->where('event', $request->event);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('causer', function ($row) {
                    if (!$row->causer) return '<span class="text-slate-400">System</span>';
                    
                    return '
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-7 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500">
                                '.strtoupper(substr($row->causer->name ?? 'U', 0, 1)).'
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700">'.e($row->causer->name).'</span>
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">'.e(str_replace('App\\Models\\', '', $row->causer_type)).'</span>
                            </div>
                        </div>
                    ';
                })
                ->editColumn('event', function ($row) {
                    $bg = 'bg-slate-100 text-slate-600';
                    $event = str_replace(['_', '.'], ' ', $row->event);
                    
                    if (str_contains($row->event, 'created')) $bg = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                    if (str_contains($row->event, 'updated')) $bg = 'bg-blue-50 text-blue-600 border-blue-100';
                    if (str_contains($row->event, 'deleted')) $bg = 'bg-rose-50 text-rose-600 border-rose-100';

                    return '<span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider border '.$bg.'">'.e($event).'</span>';
                })
                ->editColumn('subject', function ($row) {
                    $type = str_replace(['App\\Models\\', 'Company\\', 'Customer\\', 'Pricing\\'], '', $row->subject_type);
                    return '
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-800">'.e($type).'</span>
                            <span class="text-[10px] text-slate-400 font-medium">ID: '.e($row->subject_id).'</span>
                        </div>
                    ';
                })
                ->addColumn('changes_count', function ($row) {
                    $changes = $row->properties['changes'] ?? [];
                    $count = count($changes);
                    if ($count === 0) return '<span class="text-slate-300">—</span>';
                    
                    return '<span class="badge badge-light-blue font-black text-[10px]">'.$count.' Fields</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return '
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-600">'.$row->created_at->format('d M, Y').'</span>
                            <span class="text-[10px] text-slate-400 font-medium">'.$row->created_at->format('h:i A').'</span>
                        </div>
                    ';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-light h-8 w-8 p-0 rounded-lg view-changes" data-id="'.$row->id.'">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                    ';
                })
                ->rawColumns(['causer', 'event', 'subject', 'changes_count', 'created_at', 'action'])
                ->make(true);
        }

        $events = ActivityLog::query()
            ->where('company_id', active_company_id())
            ->select('event')
            ->distinct()
            ->pluck('event');

        return view('catvara.activity-log.index', compact('events'));
    }

    /**
     * Get specific log details (for modal)
     */
    public function show(Company $company, $id)
    {
        $log = ActivityLog::where('company_id', $company->id)
            ->with(['causer'])
            ->findOrFail($id);

        return response()->json([
            'log' => $log,
            'subject_type_simple' => str_replace(['App\\Models\\', 'Company\\', 'Customer\\', 'Pricing\\'], '', $log->subject_type)
        ]);
    }
}
