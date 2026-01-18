<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Brand;
use App\Models\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        // $this->authorize('view', 'brands');

        $company = $request->company;

        if (!$request->ajax()) {
            return view('catvara.catalog.brands.index');
        }

        $query = Brand::where('company_id', $company->id)
            ->with('parent')
            ->select('brands.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('logo_html', function ($row) {
                if ($row->logo) {
                    return '<img src="' . asset('storage/' . $row->logo) . '" class="w-10 h-10 rounded-md object-cover border border-slate-200" />';
                }
                return '<div class="w-10 h-10 rounded-md bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-400"><i class="fas fa-image"></i></div>';
            })
            ->addColumn('parent_name', function ($row) {
                return $row->parent ? $row->parent->name : '<span class="text-slate-300 text-xs">—</span>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->is_active
                    ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Active</span>'
                    : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 ring-1 ring-inset ring-slate-500/20">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = company_route('catalog.brands.edit', ['brand' => $row->id]);
                $deleteUrl = company_route('catalog.brands.destroy', ['brand' => $row->id]);

                return '
                   <div class="flex items-center justify-end gap-2">
                        <a href="' . $editUrl . '" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                         <button type="button" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Delete" onclick="confirmDelete(\'' . $deleteUrl . '\')">
                            <i class="fas fa-trash"></i>
                        </button>
                   </div>
                   ';
            })
            ->rawColumns(['logo_html', 'parent_name', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        // $this->authorize('create', 'brands');
        $company = request()->company;

        $brands = Brand::where('company_id', $company->id)->get();

        return view('catvara.catalog.brands.form', compact('brands'));
    }

    public function store(Request $request)
    {
        // $this->authorize('create', 'brands');

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:brands,id',
            'logo' => 'nullable|image|max:2048',
        ]);

        $brand = new Brand();
        $brand->uuid = (string) Str::uuid();
        $brand->company_id = $request->company->id;
        $brand->parent_id = $request->parent_id;
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $brand->description = $request->description;
        $brand->is_active = $request->has('is_active');

        if ($request->hasFile('logo')) {
            $brand->logo = $request->file('logo')->store('brands', 'public');
        }

        $brand->save();

        return redirect(company_route('catalog.brands.index'))
            ->with('success', 'Brand created successfully.');
    }

    public function edit(Company $company, Brand $brand)
    {
        // $this->authorize('edit', 'brands');

        if ($brand->company_id !== $company->id) {
            abort(403);
        }

        $brands = Brand::where('company_id', $company->id)
            ->where('id', '!=', $brand->id)
            ->get();

        return view('catvara.catalog.brands.form', compact('brand', 'brands'));
    }

    public function update(Request $request, Company $company, Brand $brand)
    {
        // $this->authorize('edit', 'brands');

        if ($brand->company_id !== $company->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:brands,id',
            'logo' => 'nullable|image|max:2048',
        ]);

        $brand->parent_id = $request->parent_id;
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $brand->description = $request->description;
        $brand->is_active = $request->has('is_active');

        if ($request->hasFile('logo')) {
            $brand->logo = $request->file('logo')->store('brands', 'public');
        }

        $brand->save();

        return redirect(company_route('catalog.brands.index'))
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Company $company, Brand $brand)
    {
        // $this->authorize('delete', 'brands');

        if ($brand->company_id !== $company->id) {
            abort(403);
        }

        try {
            $brand->delete();
            return redirect()->back()->with('success', 'Brand deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cannot delete brand because it is in use.');
        }
    }
}
