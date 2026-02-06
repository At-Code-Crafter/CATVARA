<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\Store;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'stores');

        if ($request->ajax()) {
            $stores = Store::where('company_id', $request->company->id)
                ->with(['inventoryLocation.balances'])
                ->latest();

            return DataTables::eloquent($stores)
                ->addColumn('name_html', function ($store) {
                    $statusBadge = $store->is_active
                        ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wider ml-2"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1"></span>Active</span>'
                        : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-wider ml-2">Inactive</span>';
                    return '<div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-500 flex-shrink-0">
                            <i class="fas fa-store"></i>
                        </div>
                        <div>
                            <div class="font-bold text-slate-800 flex items-center">' . e($store->name) . $statusBadge . '</div>
                            <span class="text-xs font-mono font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">' . e($store->code) . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('address_html', function ($store) {
                    if (!$store->address) {
                        return '<span class="text-slate-300 text-sm">—</span>';
                    }
                    $truncated = strlen($store->address) > 50 ? substr($store->address, 0, 50) . '...' : $store->address;
                    return '<span class="text-sm text-slate-600" title="' . e($store->address) . '">' . e($truncated) . '</span>';
                })
                ->addColumn('phone_html', function ($store) {
                    if (!$store->phone) {
                        return '<span class="text-slate-300 text-sm">—</span>';
                    }
                    return '<span class="text-sm text-slate-600"><i class="fas fa-phone text-xs text-slate-400 mr-1.5"></i>' . e($store->phone) . '</span>';
                })
                ->addColumn('stock_html', function ($store) {
                    $stockCount = $store->inventoryLocation?->balances?->sum('quantity') ?? 0;
                    $skuCount = $store->inventoryLocation?->balances?->count() ?? 0;

                    if ($skuCount == 0) {
                        return '<span class="text-slate-300 text-sm">No stock</span>';
                    }

                    return '<div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                            <i class="fas fa-cubes mr-1.5 opacity-60"></i>' . number_format($stockCount) . ' units
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-brand-50 text-brand-600 border border-brand-100">
                            ' . $skuCount . ' SKUs
                        </span>
                    </div>';
                })
                ->addColumn('actions', function ($store) {
                    $editUrl = company_route('inventory.stores.edit', ['store' => $store->id]);
                    $deleteUrl = company_route('inventory.stores.destroy', ['store' => $store->id]);
                    return '<div class="flex items-center justify-end gap-1">
                        <a href="' . $editUrl . '" class="p-2 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this store?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>';
                })
                ->rawColumns(['name_html', 'address_html', 'phone_html', 'stock_html', 'actions'])
                ->make(true);
        }

        return view('catvara.inventory.stores.index');
    }

    public function create()
    {
        $this->authorize('create', 'stores');

        return view('catvara.inventory.stores.form');
    }

    public function store(Requests\Inventory\StoreStoreRequest $request)
    {
        $this->authorize('create', 'stores');

        $store = new Store();
        $store->uuid = Str::uuid();
        $store->company_id = $request->company->id;
        $store->name = $request->name;
        $store->code = $request->code;
        $store->address = $request->address;
        $store->phone = $request->phone;
        $store->is_active = $request->has('is_active');
        $store->save();

        // Create associated Inventory Location
        $location = new InventoryLocation();
        $location->uuid = Str::uuid();
        $location->company_id = $request->company->id;
        // Correct Polymorphic relation
        $location->locatable_type = Store::class;
        $location->locatable_id = $store->id;
        $location->type = 'store';
        $location->is_active = $store->is_active;
        $location->save();

        return redirect(company_route('inventory.stores.index'))
            ->with('success', 'Store created successfully.');
    }

    public function edit(Company $company, Store $store)
    {
        $this->authorize('edit', 'stores');

        if ($store->company_id !== $company->id) {
            abort(403);
        }
        return view('catvara.inventory.stores.form', compact('store'));
    }

    public function update(Requests\Inventory\UpdateStoreRequest $request, Company $company, Store $store)
    {
        $this->authorize('edit', 'stores');

        if ($store->company_id !== $company->id) {
            abort(403);
        }

        $store->name = $request->name;
        $store->code = $request->code;
        $store->address = $request->address;
        $store->phone = $request->phone;
        $store->is_active = $request->has('is_active');
        $store->save();

        // Update Location is_active status
        if ($store->inventoryLocation) {
            $store->inventoryLocation->is_active = $store->is_active;
            $store->inventoryLocation->save();
        }

        return redirect(company_route('inventory.stores.index'))
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Company $company, Store $store)
    {
        $this->authorize('delete', 'stores');

        if ($store->company_id !== $company->id) {
            abort(403);
        }

        try {
            // Check balances logic would go here
            if ($store->inventoryLocation && $store->inventoryLocation->balances()->sum('quantity') > 0) {
                return back()->with('error', 'Cannot delete store with active stock.');
            }

            if ($store->inventoryLocation) {
                $store->inventoryLocation->delete();
            }
            $store->delete();

            return redirect()->back()->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting store.');
        }
    }
}
