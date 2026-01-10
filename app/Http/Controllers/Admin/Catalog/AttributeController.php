<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\AttributeValue;
use Illuminate\Http\Request;
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
        $attrTable = $prefix.'attributes';
        $avTable = $prefix.'attribute_values';

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
              FROM '.$avTable.' av
              WHERE av.attribute_id = '.$attrTable.'.id
            ) as values_list'
            );

        if ($request->filled('is_active')) {
            $query->where('attributes.is_active', (int) $request->is_active);
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()

                ->editColumn('code', fn ($row) => '<code>'.e($row->code).'</code>')

                ->addColumn('values_badges', function ($row) {
                    $list = trim((string) ($row->values_list ?? ''));

                    if ($list === '') {
                        return '<span class="text-muted">—</span>';
                    }

                    $items = array_values(array_filter(array_map('trim', explode(',', $list))));
                    $max = 6;

                    $html = '<div class="d-flex flex-wrap" style="gap:6px;">';
                    foreach (array_slice($items, 0, $max) as $val) {
                        $html .= '<span class="badge badge-secondary">'.e($val).'</span>';
                    }

                    if (count($items) > $max) {
                        $more = count($items) - $max;
                        $full = e(implode(', ', $items));
                        $html .= '<span class="badge badge-light border" data-toggle="tooltip" title="'.$full.'">+'.$more.' more</span>';
                    }

                    $html .= '</div>';

                    return $html;
                })

                ->addColumn('status_badge', fn ($row) => ((int) $row->is_active === 1)
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>')

                ->addColumn('action', function ($row) {
                    $editUrl = company_route('catalog.attributes.edit', ['attribute' => $row->id]);

                    return '
                  <div class="d-inline-flex align-items-center" style="gap:8px;">
                    <a href="'.e($editUrl).'" class="btn btn-sm btn-outline-primary"
                       data-toggle="tooltip" title="Edit Attribute">
                       <i class="fas fa-pen"></i>
                    </a>
                  </div>
                ';
                })
                ->addColumn('action', function ($row) {
                    $compact['showUrl'] = null;
                    $compact['editUrl'] = company_route('catalog.attributes.edit', ['attribute' => $row->id]);
                    $compact['deleteUrl'] = null;
                    $compact['editSidebar'] = false;

                    return view('theme.adminlte.components._table-actions', $compact)->render();
                })

                ->rawColumns(['code', 'values_badges', 'status_badge', 'action'])
                ->make(true);
        }

        return view('theme.adminlte.catalog.attributes.index');
    }

    public function create()
    {
        $this->authorize('create', 'attributes');

        return view('theme.adminlte.catalog.attributes.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create', 'attributes');

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'values' => 'required|string', // Comma separated for MVP convenience
        ]);

        $attribute = new Attribute;
        $attribute->company_id = $request->company->id;
        $attribute->name = $request->name;
        $attribute->code = Str::slug($request->code);

        if (Attribute::where('company_id', $request->company->id)->where('code', $attribute->code)->exists()) {
            return back()->withErrors(['code' => 'Code already exists for this company.']);
        }

        $attribute->save();

        // Process values
        $values = array_map('trim', explode(',', $request->values));
        foreach ($values as $val) {
            if (! empty($val)) {
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

        return view('theme.adminlte.catalog.attributes.form', compact('attribute'));
    }

    public function update(Request $request, \App\Models\Company\Company $company, Attribute $attribute)
    {
        $this->authorize('edit', 'attributes');

        if ($attribute->company_id !== $company->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'new_values' => 'nullable|string',
            'existing_values' => 'nullable|array',
        ]);

        $attribute->name = $request->name;
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
                if (! empty($val) && ! in_array($val, $existing)) {
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
