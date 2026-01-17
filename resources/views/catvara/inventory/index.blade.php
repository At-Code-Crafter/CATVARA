@extends('catvara.layouts.app')

@section('title', 'Inventory Management')

@section('content')
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6 animate-fade-in">
    <div>
      <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Inventory Management</h1>
      <p class="text-slate-400 mt-1 font-medium">Monitor stock levels and manage inventory movements across locations.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3">
        <a href="{{ company_route('inventory.transfers.create') }}" class="btn btn-white">
            <i class="fas fa-exchange-alt mr-2 text-slate-500"></i> New Transfer
        </a>
      <a href="{{ company_route('inventory.inventory.create') }}" class="btn btn-primary">
        <i class="fas fa-sliders-h mr-2"></i> Adjust Stock
      </a>
    </div>
  </div>

  {{-- Stats Cards --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      {{-- Total SKUs --}}
      <div class="card p-6 border-l-4 border-l-sky-500 dashboard-stat-card bg-white text-slate-700 shadow-sm hover:shadow-md transition-all">
          <div class="flex justify-between items-start">
              <div>
                  <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total SKUs</p>
                  <h3 class="text-2xl font-black text-slate-800">{{ $stats['total_skus'] }}</h3>
              </div>
              <div class="p-3 bg-sky-50 rounded-xl text-sky-500">
                  <i class="fas fa-barcode text-xl"></i>
              </div>
          </div>
      </div>

       {{-- Total Units --}}
       <div class="card p-6 border-l-4 border-l-emerald-500 dashboard-stat-card bg-white text-slate-700 shadow-sm hover:shadow-md transition-all">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Units</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $stats['total_units'] }}</h3>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-500">
                <i class="fas fa-boxes text-xl"></i>
            </div>
        </div>
    </div>

     {{-- Low Stock --}}
     <div class="card p-6 border-l-4 border-l-brand-400 dashboard-stat-card bg-white text-slate-700 shadow-sm hover:shadow-md transition-all">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Low Stock Alerts</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $stats['low_stock'] }}</h3>
            </div>
            <div class="p-3 bg-brand-50 rounded-xl text-brand-500">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
        </div>
    </div>

     {{-- Out of Stock --}}
     <div class="card p-6 border-l-4 border-l-red-500 dashboard-stat-card bg-white text-slate-700 shadow-sm hover:shadow-md transition-all">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Out of Stock</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $stats['out_of_stock'] }}</h3>
            </div>
            <div class="p-3 bg-red-50 rounded-xl text-red-500">
                <i class="fas fa-ban text-xl"></i>
            </div>
        </div>
    </div>
  </div>

  {{-- Main Content --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
    {{-- Left: Stock Levels --}}
      <div class="lg:col-span-2 space-y-8">
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-warehouse text-brand-400"></i> Stock Levels
                </h3>
                <div class="w-48">
                    <select id="location-filter" class="w-full text-sm">
                        <option value="">All Locations</option>
                        @foreach ($locations as $loc)
                          <option value="{{ $loc->id }}">{{ $loc->locatable->name ?? $loc->type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="p-0">
                <table id="balances-table" class="table-premium w-full text-left">
                    <thead>
                        <tr>
                            <th>SKU / Product</th>
                            <th>Location</th>
                            <th>On Hand</th>
                            <th>Last Move</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
      </div>

      {{-- Right: Recent Transfers --}}
      <div class="lg:col-span-1">
          <div class="card bg-white border-slate-100 shadow-soft">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">Recent Transfers</h3>
                <a href="{{ company_route('inventory.transfers.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">View All</a>
            </div>
            <div class="p-0">
                @forelse($recentTransfers as $transfer)
                <div class="p-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors group">
                    <div class="flex justify-between items-center mb-2">
                        <a href="{{ company_route('inventory.transfers.show', ['transfer' => $transfer]) }}" class="text-sm font-bold text-slate-800 group-hover:text-brand-600 transition-colors">
                            {{ $transfer->transfer_no }}
                        </a>
                        <span class="badge {{ $transfer->status->code == 'CLOSED' ? 'badge-success' : 'badge-info' }}">
                            {{ $transfer->status->name }}
                        </span>
                    </div>
                    <div class="flex items-center text-xs text-slate-500 gap-2 mb-1">
                        <span>{{ $transfer->fromLocation->locatable->name ?? '-' }}</span>
                        <i class="fas fa-arrow-right text-slate-300"></i>
                        <span>{{ $transfer->toLocation->locatable->name ?? '-' }}</span>
                    </div>
                    <div class="text-[10px] text-slate-400 text-right">
                        {{ $transfer->created_at->format('M d, Y') }}
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-sm italic">
                    No recent transfers found.
                </div>
                @endforelse
            </div>
          </div>
      </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      var table = $('#balances-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
          url: '{{ company_route('inventory.balances.data') }}',
          data: function(d) {
            d.location_id = $('#location-filter').val();
          }
        },
        columns: [{
            data: 'sku',
            name: 'variant.sku',
            render: function(data, type, row) {
                return `
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-700 text-sm">${data}</span>
                        <span class="text-xs text-slate-500 truncate max-w-[200px]" title="${row.product_name}">${row.product_name}</span>
                    </div>
                `;
            }
          },
          {
            data: 'location_name',
            name: 'location.locatable.name',
            render: function(data) {
                return `<span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-1 rounded inline-block">${data}</span>`;
            }
          },
          {
            data: 'quantity',
            name: 'quantity',
            className: 'text-center font-mono font-bold'
          },
          {
            data: 'last_movement',
            name: 'last_movement_at',
            className: 'text-xs text-slate-400'
          },
          {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false,
            className: 'text-right'
          }
        ],
        language: {
          search: "",
          searchPlaceholder: "Search SKU or Product...",
          processing: '<div class="flex items-center justify-center py-4"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
        },
        dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
      });

      $('#location-filter').change(function() {
        table.ajax.reload();
      });
    });
  </script>
@endpush
