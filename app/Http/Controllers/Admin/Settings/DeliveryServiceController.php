<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Sales\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DeliveryServiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'orders');

        if ($request->ajax()) {
            $companyId = active_company_id();
            $query = DeliveryService::where('company_id', $companyId)->ordered();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-wider border border-emerald-100">Active</span>'
                        : '<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border border-slate-100">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = company_route('settings.delivery-services.edit', ['delivery_service' => $row->id]);
                    $deleteUrl = company_route('settings.delivery-services.destroy', ['delivery_service' => $row->id]);

                    return '
                        <a href="'.$editUrl.'" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" class="inline" onsubmit="return confirm(\'Delete this delivery service?\');">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('catvara.settings.delivery-services.index');
    }

    public function create()
    {
        $this->authorize('create', 'orders');

        return view('catvara.settings.delivery-services.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create', 'orders');

        $companyId = active_company_id();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', "unique:delivery_services,name,NULL,id,company_id,{$companyId},deleted_at,NULL"],
            'code' => ['nullable', 'string', 'max:50'],
            'tracking_url_template' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            DeliveryService::create([
                'company_id' => $companyId,
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'tracking_url_template' => $validated['tracking_url_template'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
        } catch (\Throwable $e) {
            Log::error('DeliveryService store failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to create delivery service.');
        }

        return redirect()->route('settings.delivery-services.index', ['company' => active_company()->uuid])
            ->with('success', 'Delivery service created successfully.');
    }

    public function edit(Company $company, $id)
    {
        $this->authorize('edit', 'orders');

        $deliveryService = DeliveryService::where('company_id', $company->id)->findOrFail($id);

        return view('catvara.settings.delivery-services.form', compact('deliveryService'));
    }

    public function update(Request $request, Company $company, $id)
    {
        $this->authorize('edit', 'orders');

        $deliveryService = DeliveryService::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', "unique:delivery_services,name,{$deliveryService->id},id,company_id,{$company->id},deleted_at,NULL"],
            'code' => ['nullable', 'string', 'max:50'],
            'tracking_url_template' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $deliveryService->update([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'tracking_url_template' => $validated['tracking_url_template'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $request->boolean('is_active', false),
            ]);
        } catch (\Throwable $e) {
            Log::error('DeliveryService update failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to update delivery service.');
        }

        return redirect()->route('settings.delivery-services.index', ['company' => active_company()->uuid])
            ->with('success', 'Delivery service updated successfully.');
    }

    public function destroy(Company $company, $id)
    {
        $this->authorize('delete', 'orders');

        $deliveryService = DeliveryService::where('company_id', $company->id)->findOrFail($id);

        if ($deliveryService->deliveryNotes()->exists()) {
            return back()->with('error', 'Cannot delete a delivery service used by existing delivery notes.');
        }

        $deliveryService->delete();

        return redirect()->route('settings.delivery-services.index', ['company' => active_company()->uuid])
            ->with('success', 'Delivery service deleted successfully.');
    }
}
