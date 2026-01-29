<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\CategoryStoreRequest;
use App\Http\Requests\Catalog\CategoryUpdateRequest;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\Category;
use App\Models\Company\Company;
use App\Models\Tax\TaxGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'categories');

        $company = $request->company;

        if (! $request->ajax()) {
            $parents = Category::query()
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('catvara.catalog.categories.index', compact('parents'));
        }

        $query = $this->baseQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('name_html', function ($row) {
                $depth = 0;
                if (! empty($row->parent_id)) {
                    $depth = 1;
                }
                if (! empty($row->grandparent_id)) {
                    $depth = 2;
                }

                $indent = '';
                for ($i = 0; $i < $depth; $i++) {
                    $indent .= '<span class="text-slate-300 mr-2">—</span>';
                }

                return '
                    <div class="flex items-center">
                      <div class="font-medium text-slate-800">'.$indent.e($row->name).'</div>
                    </div>
                ';
            })

            ->addColumn('slug_html', fn ($row) => '<span class="text-xs font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded">'.e($row->slug).'</span>'
            )

            ->addColumn('products_count_html', function ($row) {
                $count = (int) ($row->products_count ?? 0);

                return $count > 0
                    ? '<span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full ring-1 ring-inset ring-emerald-500/10">'.$count.' products</span>'
                    : '<span class="text-slate-300 text-xs">0 products</span>';
            })

            ->addColumn('parent_html', function ($row) {
                return ! empty($row->parent_name)
                    ? '<span class="text-xs font-medium text-slate-600 bg-slate-100 px-2 py-1 rounded-md border border-slate-200">'.e($row->parent_name).'</span>'
                    : '<span class="text-slate-300 text-xs">—</span>';
            })

            ->addColumn('children_html', function ($row) {
                $count = (int) ($row->children_count ?? 0);

                return $count > 0
                    ? '<span class="text-xs font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-full ring-1 ring-inset ring-brand-500/10">'.$count.'</span>'
                    : '<span class="text-slate-300 text-xs">—</span>';
            })

            ->addColumn('tax_group_html', function ($row) {
                return ! empty($row->tax_group_name)
                    ? '<span class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full ring-1 ring-inset ring-indigo-600/20">'.e($row->tax_group_name).'</span>'
                    : '<span class="text-slate-300 text-xs">—</span>';
            })

            ->addColumn('status_badge', function ($row) {
                return ((int) $row->is_active === 1)
                    ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Active</span>'
                    : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 ring-1 ring-inset ring-slate-500/20">Inactive</span>';
            })

            ->editColumn('created_at', fn ($row) => $row->created_at ? date('Y-m-d', strtotime($row->created_at)) : '-')

            ->addColumn('action', function ($row) {
                $editUrl = company_route('catalog.categories.edit', ['category' => $row->id]);
                $deleteUrl = company_route('catalog.categories.destroy', ['category' => $row->id]);

                return '
                   <div class="flex items-center justify-end gap-2">
                        <a href="'.$editUrl.'" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                         <button type="button" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Delete" onclick="confirmDelete(\''.$deleteUrl.'\')">
                            <i class="fas fa-trash"></i>
                        </button>
                   </div>
                ';
            })

            ->rawColumns([
                'name_html',
                'slug_html',
                'parent_html',
                'children_html',
                'products_count_html',
                'tax_group_html',
                'status_badge',
                'action',
            ])
            ->make(true);
    }

    public function stats(Request $request)
    {
        $base = $this->baseQuery($request);
        $sub = DB::query()->fromSub($base, 'x');

        $all = (clone $sub)->count();
        $active = (clone $sub)->where('is_active', 1)->count();
        $parents = (clone $sub)->whereNull('parent_id')->count();
        $children = (clone $sub)->whereNotNull('parent_id')->count();

        return response()->json([
            'all_categories' => $all,
            'active_categories' => $active,
            'parent_categories' => $parents,
            'child_categories' => $children,
        ]);
    }

    private function baseQuery(Request $request)
    {
        $company = $request->company;

        $prefix = DB::getTablePrefix();
        $table = (new Category)->getTable(); // categories

        $c = $prefix.'c';
        $p = $prefix.'p';
        $gp = $prefix.'gp';
        $cc = $prefix.'cc';
        $pc = $prefix.'pc';
        $tg = $prefix.'tg';

        $childrenCount = DB::table($table.' as c2')
            ->select('c2.parent_id', DB::raw('COUNT(*) as children_count'))
            ->where('c2.company_id', $company->id)
            ->groupBy('c2.parent_id');

        $productsCount = DB::table('products as pr')
            ->select('pr.category_id', DB::raw('COUNT(*) as products_count'))
            ->where('pr.company_id', $company->id)
            ->groupBy('pr.category_id');

        $q = DB::table($table.' as c')
            ->leftJoin($table.' as p', 'p.id', '=', 'c.parent_id')
            ->leftJoin($table.' as gp', 'gp.id', '=', 'p.parent_id')
            ->leftJoinSub($childrenCount, 'cc', fn ($join) => $join->on('cc.parent_id', '=', 'c.id'))
            ->leftJoinSub($productsCount, 'pc', fn ($join) => $join->on('pc.category_id', '=', 'c.id'))
            ->leftJoin('tax_groups as tg', 'tg.id', '=', 'c.tax_group_id')
            ->where('c.company_id', $company->id)
            ->select([
                'c.id as id',
                'c.company_id as company_id',
                'c.parent_id as parent_id',
                'c.tax_group_id as tax_group_id',
                'c.name as name',
                'c.slug as slug',
                'c.is_active as is_active',
                'c.created_at as created_at',

                DB::raw("{$p}.name as parent_name"),
                DB::raw("{$gp}.id as grandparent_id"),
                DB::raw("{$gp}.name as grandparent_name"),
                DB::raw("COALESCE({$cc}.children_count, 0) as children_count"),
                DB::raw("COALESCE({$pc}.products_count, 0) as products_count"),

                DB::raw("{$tg}.name as tax_group_name"),
            ]);

        if ($request->filled('is_active')) {
            $q->where('c.is_active', (int) $request->is_active);
        }

        if ($request->filled('parent_id')) {
            if ($request->parent_id === '__ROOT__') {
                $q->whereNull('c.parent_id');
            } else {
                $q->where('c.parent_id', (int) $request->parent_id);
            }
        }

        if ($request->filled('has_children')) {
            if ($request->has_children === '1') {
                $q->whereRaw("COALESCE({$cc}.children_count, 0) > 0");
            } elseif ($request->has_children === '0') {
                $q->whereRaw("COALESCE({$cc}.children_count, 0) = 0");
            }
        }

        if ($request->filled('date_from')) {
            $q->whereDate('c.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $q->whereDate('c.created_at', '<=', $request->date_to);
        }

        return $q;
    }

    public function create()
    {
        $this->authorize('create', 'categories');

        $company = request()->company;

        $categories = Category::where('company_id', $company->id)->with('children')->get();
        $attributes = Attribute::where('company_id', $company->id)->get();

        $taxGroups = TaxGroup::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('catvara.catalog.categories.form', compact('categories', 'attributes', 'taxGroups'));
    }

    public function store(CategoryStoreRequest $request)
    {
        $this->authorize('create', 'categories');

        $company = $request->company;

        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $category = new Category;
            $category->company_id = $company->id;
            $category->parent_id = $validated['parent_id'] ?? null;
            $category->tax_group_id = $validated['tax_group_id'] ?? null;
            $category->name = $validated['name'];
            $category->slug = Str::slug($validated['name']);
            $category->is_active = (bool) ($validated['is_active'] ?? 0);
            $category->save();

            $category->attributes()->sync($validated['attributes']);

            DB::commit();

            return redirect(company_route('catalog.categories.index'))
                ->with('success', 'Category created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Company $company, Category $category)
    {
        $this->authorize('edit', 'categories');

        if ($category->company_id !== $company->id) {
            abort(403);
        }

        $categories = Category::where('company_id', $company->id)
            ->where('id', '!=', $category->id)
            ->with('children')
            ->get();

        $attributes = Attribute::where('company_id', $company->id)->get();

        $taxGroups = TaxGroup::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('catvara.catalog.categories.form', compact('category', 'categories', 'attributes', 'taxGroups'));
    }

    public function update(CategoryUpdateRequest $request, Company $company, Category $category)
    {
        $this->authorize('edit', 'categories');

        if ($category->company_id !== $company->id) {
            abort(403);
        }

        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $category->parent_id = $validated['parent_id'] ?? null;
            $category->tax_group_id = $validated['tax_group_id'] ?? null;
            $category->name = $validated['name'];
            $category->slug = Str::slug($validated['name']);
            $category->is_active = (bool) ($validated['is_active'] ?? 0);
            $category->save();

            $category->attributes()->sync($validated['attributes']);

            DB::commit();

            return redirect(company_route('catalog.categories.index'))
                ->with('success', 'Category updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Company $company, Category $category)
    {
        $this->authorize('delete', 'categories');

        if ($category->company_id !== $company->id) {
            abort(403);
        }

        try {
            $category->delete();

            return redirect()->back()->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cannot delete category because it is in use.');
        }
    }
}
