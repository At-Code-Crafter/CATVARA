<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\AttributeStoreRequest;
use App\Http\Requests\Admin\Catalog\AttributeUpdateRequest;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\AttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'attributes');

        $companyId = $request->company->id;

        $prefix = DB::getTablePrefix();
        $attrTable = $prefix . 'attributes';
        $avTable = $prefix . 'attribute_values';
        $pvavTable = $prefix . 'product_variant_attribute_values';
        $pvTable = $prefix . 'product_variants';

        $query = DB::table('attributes')
            ->where('attributes.company_id', $companyId)
            ->select([
                'attributes.id',
                'attributes.name',
                'attributes.code',
                'attributes.is_active',
                'attributes.created_at',
            ])
            ->selectRaw(
                '(SELECT GROUP_CONCAT(av.value ORDER BY av.sort_order SEPARATOR ", ")
              FROM ' . $avTable . ' av
              WHERE av.attribute_id = ' . $attrTable . '.id
            ) as values_list'
            )
            ->selectRaw(
                '(SELECT COUNT(DISTINCT pv.product_id)
              FROM ' . $avTable . ' av2
              INNER JOIN ' . $pvavTable . ' pvav ON pvav.attribute_value_id = av2.id
              INNER JOIN ' . $pvTable . ' pv ON pv.id = pvav.product_variant_id
              WHERE av2.attribute_id = ' . $attrTable . '.id
            ) as products_count'
            );

        if ($request->filled('is_active')) {
            $query->where('attributes.is_active', (int) $request->is_active);
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()

                ->editColumn('code', fn($row) => '<code class="text-xs font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded">' . e($row->code) . '</code>')

                ->addColumn('values_badges', function ($row) {
                    $list = trim((string) ($row->values_list ?? ''));

                    if ($list === '') {
                        return '<span class="text-slate-300 text-xs">—</span>';
                    }

                    $items = array_values(array_filter(array_map('trim', explode(',', $list))));
                    $max = 5;

                    $html = '<div class="flex flex-wrap gap-1.5">';
                    foreach (array_slice($items, 0, $max) as $val) {
                        $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">' . e($val) . '</span>';
                    }

                    if (count($items) > $max) {
                        $more = count($items) - $max;
                        $full = e(implode(', ', $items));
                        $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-brand-50 text-brand-700 border border-brand-100 cursor-help" title="' . $full . '">+' . $more . ' more</span>';
                    }

                    $html .= '</div>';

                    return $html;
                })

                ->addColumn('products_count_html', function ($row) {
                    $count = (int) ($row->products_count ?? 0);
                    if ($count > 0) {
                        return '<span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full ring-1 ring-inset ring-emerald-500/10">' . $count . ' products</span>';
                    }
                    return '<span class="text-slate-300 text-xs">0 products</span>';
                })

                ->addColumn('status_badge', fn($row) => ((int) $row->is_active === 1)
                    ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Active</span>'
                    : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 ring-1 ring-inset ring-slate-500/20">Inactive</span>')

                ->addColumn('action', function ($row) {
                    $editUrl = company_route('catalog.attributes.edit', ['attribute' => $row->id]);

                    return '
                        <div class="flex items-center justify-end gap-2">
                            <a href="' . $editUrl . '" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    ';
                })

                ->rawColumns(['code', 'values_badges', 'products_count_html', 'status_badge', 'action'])
                ->make(true);
        }

        return view('catvara.catalog.attributes.index');
    }

    public function create()
    {
        $this->authorize('create', 'attributes');

        return view('catvara.catalog.attributes.form');
    }

    public function store(AttributeStoreRequest $request)
    {
        $this->authorize('create', 'attributes');

        $attribute = new Attribute;
        $attribute->company_id = $request->company->id;
        $attribute->name = $request->name;
        $attribute->code = Str::slug($request->code);
        $attribute->is_active = $request->has('is_active');

        if (Attribute::where('company_id', $request->company->id)->where('code', $attribute->code)->exists()) {
            return back()->withErrors(['code' => 'Code already exists for this company.']);
        }

        $attribute->save();

        // Process values
        $values = array_map('trim', explode(',', $request->values));
        foreach ($values as $val) {
            if (!empty($val)) {
                $attribute->values()->create(['value' => $val]);
            }
        }

        return redirect(company_route('catalog.attributes.index'))
            ->with('success', 'Attribute saved successfully.');
    }

    public function edit(\App\Models\Company\Company $company, Attribute $attribute)
    {
        $this->authorize('edit', 'attributes');

        if ($attribute->company_id !== $company->id) {
            abort(403);
        }

        return view('catvara.catalog.attributes.form', compact('attribute'));
    }

    public function update(AttributeUpdateRequest $request, \App\Models\Company\Company $company, Attribute $attribute)
    {
        $this->authorize('edit', 'attributes');

        if ($attribute->company_id !== $company->id) {
            abort(403);
        }

        $attribute->name = $request->name;
        $attribute->is_active = $request->has('is_active');
        $attribute->save();

        // 1. Update Existing Values (Status)
        if ($request->has('existing_values')) {
            foreach ($request->existing_values as $id => $data) {
                $val = AttributeValue::where('attribute_id', $attribute->id)->find($id);
                if ($val) {
                    $val->update([
                        'is_active' => isset($data['is_active']),
                    ]);
                }
            }
        }

        // 2. Add New Values
        if ($request->filled('new_values')) {
            $values = array_map('trim', explode(',', $request->new_values));
            $existing = $attribute->values()->pluck('value')->toArray();

            foreach ($values as $val) {
                // Determine case-insensitive duplicate check? Seeder did it, but let's be safe
                // For now, strict check
                if (!empty($val) && !in_array($val, $existing)) {
                    $attribute->values()->create([
                        'value' => $val,
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect(company_route('catalog.attributes.index'))
            ->with('success', 'Attribute updated successfully.');
    }
}
