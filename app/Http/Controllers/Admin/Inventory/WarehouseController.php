<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'warehouses');

        if ($request->ajax()) {
            $warehouses = Warehouse::where('company_id', $request->company->id)
                ->with(['inventoryLocation.balances'])
                ->latest();

            return DataTables::eloquent($warehouses)
                ->addColumn('name_html', function ($warehouse) {
                    return '<div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 flex-shrink-0">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div class="font-bold text-slate-800">' . e($warehouse->name) . '</div>
                    </div>';
                })
                ->addColumn('code_html', function ($warehouse) {
                    return '<span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded border border-slate-200">' . e($warehouse->code) . '</span>';
                })
                ->addColumn('address_html', function ($warehouse) {
                    if (!$warehouse->address) {
                        return '<span class="text-slate-300 text-sm">—</span>';
                    }
                    $truncated = strlen($warehouse->address) > 40 ? substr($warehouse->address, 0, 40) . '...' : $warehouse->address;
                    return '<span class="text-sm text-slate-600" title="' . e($warehouse->address) . '">' . e($truncated) . '</span>';
                })
                ->addColumn('phone_html', function ($warehouse) {
                    if (!$warehouse->phone) {
                        return '<span class="text-slate-300 text-sm">—</span>';
                    }
                    return '<span class="text-sm text-slate-600">' . e($warehouse->phone) . '</span>';
                })
                ->addColumn('stock_html', function ($warehouse) {
                    $stockCount = $warehouse->inventoryLocation?->balances?->sum('quantity') ?? 0;
                    $skuCount = $warehouse->inventoryLocation?->balances?->count() ?? 0;

                    if ($skuCount == 0) {
                        return '<span class="text-slate-300 text-sm">No stock</span>';
                    }

                    return '<div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                            <i class="fas fa-cubes mr-1 opacity-60"></i>' . number_format($stockCount) . '
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-600 border border-amber-100">
                            ' . $skuCount . ' SKUs
                        </span>
                    </div>';
                })
                ->addColumn('status_html', function ($warehouse) {
                    if ($warehouse->is_active) {
                        return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>Active
                        </span>';
                    }
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-wider">Inactive</span>';
                })
                ->addColumn('actions', function ($warehouse) {
                    $editUrl = company_route('inventory.warehouses.edit', ['warehouse' => $warehouse->id]);
                    $deleteUrl = company_route('inventory.warehouses.destroy', ['warehouse' => $warehouse->id]);
                    return '<div class="flex items-center justify-end gap-1">
                        <a href="' . $editUrl . '" class="p-2 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this warehouse?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>';
                })
                ->rawColumns(['name_html', 'code_html', 'address_html', 'phone_html', 'stock_html', 'status_html', 'actions'])
                ->make(true);
        }

        return view('catvara.inventory.warehouses.index');
    }

    public function create()
    {
        $this->authorize('create', 'warehouses');

        return view('catvara.inventory.warehouses.form');
    }

    public function store(Requests\Inventory\StoreWarehouseRequest $request)
    {
        $this->authorize('create', 'warehouses');

        $warehouse = new Warehouse();
        $warehouse->uuid = Str::uuid();
        $warehouse->company_id = $request->company->id;
        $warehouse->name = $request->name;
        $warehouse->code = $request->code;
        $warehouse->address = $request->address;
        $warehouse->phone = $request->phone;
        $warehouse->is_active = $request->has('is_active');
        $warehouse->save();

        // Create associated Inventory Location
        $location = new InventoryLocation();
        $location->uuid = Str::uuid();
        $location->company_id = $request->company->id;
        $location->locatable_type = Warehouse::class;
        $location->locatable_id = $warehouse->id;
        $location->type = 'warehouse';
        $location->is_active = $warehouse->is_active;
        $location->save();

        return redirect(company_route('inventory.warehouses.index'))
            ->with('success', 'Warehouse created successfully.');
    }

    public function edit(Company $company, Warehouse $warehouse)
    {
        $this->authorize('edit', 'warehouses');

        if ($warehouse->company_id !== $company->id) {
            abort(403);
        }
        return view('catvara.inventory.warehouses.form', compact('warehouse'));
    }

    public function update(Requests\Inventory\UpdateWarehouseRequest $request, Company $company, Warehouse $warehouse)
    {
        $this->authorize('edit', 'warehouses');

        if ($warehouse->company_id !== $company->id) {
            abort(403);
        }

        $warehouse->name = $request->name;
        $warehouse->code = $request->code;
        $warehouse->address = $request->address;
        $warehouse->phone = $request->phone;
        $warehouse->is_active = $request->has('is_active');
        $warehouse->save();

        // Update Location is_active status
        if ($warehouse->inventoryLocation) {
            $warehouse->inventoryLocation->is_active = $warehouse->is_active;
            $warehouse->inventoryLocation->save();
        }

        return redirect(company_route('inventory.warehouses.index'))
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Company $company, Warehouse $warehouse)
    {
        $this->authorize('delete', 'warehouses');

        if ($warehouse->company_id !== $company->id) {
            abort(403);
        }

        try {
            // Check if stock exists? For now assume soft delete or force delete logic
            // Ideally we check balances first.
            if ($warehouse->inventoryLocation && $warehouse->inventoryLocation->balances()->sum('quantity') > 0) {
                return back()->with('error', 'Cannot delete warehouse with active stock.');
            }

            if ($warehouse->inventoryLocation) {
                $warehouse->inventoryLocation->delete();
            }
            $warehouse->delete();

            return redirect()->back()->with('success', 'Warehouse deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting warehouse.');
        }
    }
}
