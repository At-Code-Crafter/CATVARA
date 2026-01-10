<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Added for type hinting in getAttributes and edit/destroy methods
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'categories');

        $company = $request->company;

        // Non-ajax page load: provide parent list for filter dropdown
        if (! $request->ajax()) {
            $parents = Category::query()
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('theme.adminlte.catalog.categories.index', compact('parents'));
        }

        // Ajax: DataTables
        $query = $this->baseQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('name_html', function ($row) {
                // WordPress-like indentation (supports up to 2 levels via parent + grandparent joins)
                $depth = 0;
                if (! empty($row->parent_id)) {
                    $depth = 1;
                }
                if (! empty($row->grandparent_id)) {
                    $depth = 2;
                }

                $indent = '';
                for ($i = 0; $i < $depth; $i++) {
                    $indent .= '<span class="text-muted mr-1">—</span>';
                }

                $sub = '';
                if (! empty($row->grandparent_name) && ! empty($row->parent_name)) {
                    $sub = '<div class="text-muted small">In: '.e($row->grandparent_name).' → '.e($row->parent_name).'</div>';
                } elseif (! empty($row->parent_name)) {
                    $sub = '<div class="text-muted small">In: '.e($row->parent_name).'</div>';
                } else {
                    $sub = '<div class="text-muted small">Parent category</div>';
                }

                return '
                    <div>
                      <div class="font-weight-bold text-dark">'.$indent.e($row->name).'</div>
                      '.$sub.'
                    </div>
                ';
            })

            ->addColumn('slug_html', fn ($row) => '<span class="ent-chip"><i class="fas fa-link"></i> '.e($row->slug).'</span>'
            )

            ->addColumn('parent_html', function ($row) {
                if (! empty($row->parent_name)) {
                    return '<span class="badge badge-info px-2 py-1">'.e($row->parent_name).'</span>';
                }

                return '<span class="text-muted">—</span>';
            })

            ->addColumn('children_html', function ($row) {
                $count = (int) ($row->children_count ?? 0);
                if ($count > 0) {
                    $label = $count === 1 ? 'child' : 'children';

                    return '<span class="badge badge-light px-2 py-1"><i class="fas fa-level-down-alt mr-1"></i>'.$count.' '.$label.'</span>';
                }

                return '<span class="text-muted">—</span>';
            })

            ->addColumn('status_badge', function ($row) {
                return ((int) $row->is_active === 1)
                    ? '<span class="badge badge-success px-2 py-1">Active</span>'
                    : '<span class="badge badge-danger px-2 py-1">Inactive</span>';
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at ? date('Y-m-d', strtotime($row->created_at)) : '-';
            })

            ->addColumn('action', function ($row) {
                $editUrl = company_route('catalog.categories.edit', ['category' => $row->id]);
                $deleteUrl = company_route('catalog.categories.destroy', ['category' => $row->id]);

                return view('theme.adminlte.components._table-actions', [
                    'row' => $row,
                    'showUrl' => null,
                    'editUrl' => $editUrl,
                    'deleteUrl' => $deleteUrl,
                    'restoreUrl' => null,
                    'editSidebar' => false,
                ])->render();
            })

            ->rawColumns([
                'name_html',
                'slug_html',
                'parent_html',
                'children_html',
                'status_badge',
                'action',
            ])
            ->make(true);
    }

    public function stats(Request $request)
    {
        $base = $this->baseQuery($request);

        // Wrap the same base query to count without breaking joins/prefix aliases
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

    /**
     * Prefix-safe base query with joins + filters
     * IMPORTANT: selects are plain aliases (name, slug, parent_name...) so DataTables ordering works reliably.
     */
    private function baseQuery(Request $request)
    {
        $company = $request->company;

        $prefix = DB::getTablePrefix();     // e.g. "ec_"
        $table = (new Category)->getTable(); // "categories"

        // Because prefix also affects aliases on your connection, reference prefixed aliases in raw expressions
        $c = $prefix.'c';
        $p = $prefix.'p';
        $gp = $prefix.'gp';
        $cc = $prefix.'cc';

        // children count (direct children per category)
        $childrenCount = DB::table($table.' as c2')
            ->select('c2.parent_id', DB::raw('COUNT(*) as children_count'))
            ->where('c2.company_id', $company->id)
            ->groupBy('c2.parent_id');

        $q = DB::table($table.' as c')
            ->leftJoin($table.' as p', 'p.id', '=', 'c.parent_id')
            ->leftJoin($table.' as gp', 'gp.id', '=', 'p.parent_id')
            ->leftJoinSub($childrenCount, 'cc', function ($join) {
                $join->on('cc.parent_id', '=', 'c.id');
            })
            ->where('c.company_id', $company->id)
            ->select([
                // Select as plain names (NO table alias in DataTables column names)
                'c.id as id',
                'c.company_id as company_id',
                'c.parent_id as parent_id',
                'c.name as name',
                'c.slug as slug',
                'c.is_active as is_active',
                'c.created_at as created_at',

                // Prefix-safe alias references:
                DB::raw("{$p}.name as parent_name"),
                DB::raw("{$gp}.id as grandparent_id"),
                DB::raw("{$gp}.name as grandparent_name"),
                DB::raw("COALESCE({$cc}.children_count, 0) as children_count"),
            ]);

        // Filters
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

        $categories = Category::where('company_id', request()->company->id)->with('children')->get();
        // Load all attributes for selection
        $attributes = \App\Models\Catalog\Attribute::where('company_id', request()->company->id)->get();

        return view('theme.adminlte.catalog.categories.form', compact('categories', 'attributes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', 'categories');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'attributes' => 'array',
            'attributes.*' => 'exists:attributes,id',
        ]);

        $category = new Category;
        // $category->uuid = Str::uuid();
        $category->company_id = $request->company->id;
        $category->parent_id = $request->parent_id;
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->is_active = $request->has('is_active');
        $category->save();

        // Sync Attributes
        if ($validated['attributes']) {
            $category->attributes()->sync($validated['attributes']);
        }

        return redirect(company_route('catalog.categories.index'))
            ->with('success', 'Category created successfully.');
    }

    public function edit(Company $company, Category $category)
    {
        $this->authorize('edit', 'categories');

        if ($category->company_id !== $company->id) {
            abort(403);
        }
        $categories = Category::where('company_id', $company->id)
            ->where('id', '!=', $category->id) // Prevent self-parenting loop
            ->get();

        // Load all attributes for selection
        $attributes = \App\Models\Catalog\Attribute::where('company_id', $company->id)->get();

        return view('theme.adminlte.catalog.categories.form', compact('category', 'categories', 'attributes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company, Category $category)
    {
        $this->authorize('edit', 'categories');

        if ($category->company_id !== $company->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'attributes' => 'array',
            'attributes.*' => 'exists:attributes,id',
        ]);

        $category->parent_id = $request->parent_id;
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->is_active = $request->has('is_active');
        $category->save();

        // Sync Attributes
        if ($validated['attributes']) {
            $category->attributes()->sync($validated['attributes']);
        } else {
            $category->attributes()->detach(); // If no attributes are selected, remove all
        }

        return redirect(company_route('catalog.categories.index'))
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Company $company, Category $category)
    {
        $this->authorize('delete', 'categories');

        if ($category->company_id !== $company->id) {
            abort(403);
        }

        // Check if has children or products... for now simple delete logic or catch exception
        try {
            $category->delete();

            return redirect()->back()->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cannot delete category because it is in use.');
        }
    }
}
