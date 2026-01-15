<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Pricing\PriceChannel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PriceChannelController extends Controller
{
    /**
     * Display a listing of price channels.
     */
    public function index(Request $request)
    {
        $this->authorize('view', 'price-channels');

        if ($request->ajax()) {
            $query = PriceChannel::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('price-channels.edit', $row->id);
                    return '
                        <a href="' . $editUrl . '" class="btn btn-xs btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    ';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('theme.adminlte.settings.price-channels.index');
    }

    /**
     * Show the form for creating a new price channel.
     */
    public function create()
    {
        $this->authorize('create', 'price-channels');

        return view('theme.adminlte.settings.price-channels.form');
    }

    /**
     * Store a newly created price channel.
     */
    public function store(Request $request)
    {
        $this->authorize('create', 'price-channels');

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:price_channels,code|alpha_dash:ascii',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        PriceChannel::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('price-channels.index')
            ->with('success', 'Price channel created successfully.');
    }

    /**
     * Show the form for editing the specified price channel.
     */
    public function edit(PriceChannel $priceChannel)
    {
        $this->authorize('edit', 'price-channels');

        return view('theme.adminlte.settings.price-channels.form', [
            'priceChannel' => $priceChannel,
        ]);
    }

    /**
     * Update the specified price channel.
     */
    public function update(Request $request, PriceChannel $priceChannel)
    {
        $this->authorize('edit', 'price-channels');

        $validated = $request->validate([
            'code' => 'required|string|max:50|alpha_dash:ascii|unique:price_channels,code,' . $priceChannel->id,
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $priceChannel->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('price-channels.index')
            ->with('success', 'Price channel updated successfully.');
    }

    /**
     * Remove the specified price channel.
     */
    public function destroy(PriceChannel $priceChannel)
    {
        $this->authorize('delete', 'price-channels');

        // Check if price channel is in use
        if ($priceChannel->variantPrices()->exists()) {
            return back()->with('error', 'Cannot delete price channel that has variant prices assigned.');
        }

        $priceChannel->delete();

        return redirect()->route('price-channels.index')
            ->with('success', 'Price channel deleted successfully.');
    }
}
