@extends('catvara.layouts.app')

@section('title', 'Products')

@section('content')
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6 animate-fade-in">
    <div>
      <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Product Catalog</h1>
      <p class="text-slate-400 mt-1 font-medium">Manage your complete product catalog and variants.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3">
      <a href="{{ company_route('catalog.products.export') }}" class="btn btn-white text-slate-600">
        <i class="fas fa-file-export mr-2 text-slate-400"></i> Export
      </a>
      <a href="{{ company_route('catalog.products.import') }}" class="btn btn-white text-slate-600">
        <i class="fas fa-file-import mr-2 text-slate-400"></i> Import
      </a>
      <a href="{{ company_route('catalog.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle mr-2"></i> Add Product
      </a>
    </div>
  </div>

  {{-- Filters --}}
  <div class="card border-slate-100 bg-white shadow-soft mb-8">
    <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/20">
      <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
        <i class="fas fa-filter text-brand-400"></i> Filters
      </h3>
      <button
        class="filter-toggle-btn h-8 w-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-all">
        <i class="fas fa-chevron-up filter-toggle-icon"></i>
      </button>
    </div>
    <div class="p-6 filter-card-content">
      <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <div class="space-y-1.5">
          <label for="filter_category"
            class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Category</label>
          <select id="filter_category" class="w-full">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-1.5">
          <label for="filter_brand"
            class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Brand</label>
          <select id="filter_brand" class="w-full">
            <option value="">All Brands</option>
            @foreach ($brands as $brand)
              <option value="{{ $brand->id }}">{{ $brand->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-1.5">
          <label for="filter_status"
            class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status</label>
          <select id="filter_status" class="w-full">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label for="filter_stock" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Stock
            Level</label>
          <select id="filter_stock" class="w-full">
            <option value="">All Stock</option>
            <option value="in_stock">In Stock</option>
            <option value="low_stock">Low Stock (≤ 5)</option>
            <option value="out_of_stock">Out of Stock</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label for="filter_date_range"
            class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Created Date</label>
          <div class="input-icon-group group date-range-fix">
            <i class="far fa-calendar-alt text-slate-400 group-focus-within:text-brand-400 transition-colors"></i>
            <input type="text" id="filter_date_range" class="w-full pl-10" placeholder="Select date range...">
          </div>
        </div>
      </div>
      <div class="filter-actions">
        <button id="btn_reset_filters" class="btn btn-white min-w-[120px]">Clear Filter</button>
        <button id="btn_apply_filters" class="btn btn-primary min-w-[123px]">Apply Filter</button>
      </div>
    </div>
  </div>

  <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
    <div class="p-6">
      <table class="table-premium w-full text-left" id="productsTable">
        <thead>
          <tr>
            <th class="px-8!">Product</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Status</th>
            <th class="text-right px-8!">Actions</th>
          </tr>
        </thead>
        <tbody>
          {{-- DataTables handled --}}
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {
      // Date Range
      const dateRangePicker = flatpickr("#filter_date_range", {
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "M j, Y",
        onReady: (selectedDates, dateStr, instance) => {
          $(instance.altInput).addClass(
            'w-full pl-10 border-slate-200 rounded-xl text-sm h-[44px] font-semibold transition-all focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10'
          );
        }
      });

      const table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: "{{ company_route('catalog.products.index') }}",
          data: function(d) {
            d.category_id = $('#filter_category').val();
            d.brand_id = $('#filter_brand').val();
            d.status = $('#filter_status').val();
            d.stock_level = $('#filter_stock').val();

            const dateRange = $('#filter_date_range').val();
            if (dateRange && dateRange.includes(' to ')) {
              const dates = dateRange.split(' to ');
              d.date_from = dates[0];
              d.date_to = dates[1];
            }
          }
        },
        columns: [{
            data: 'name',
            name: 'name',
            className: 'px-8 py-4',
            render: (data, type, row) => {
              const img = row.image_url || '{{ asset('theme/adminlte/dist/img/default-150x150.png') }}';
              return `
                 <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 border border-slate-100 shadow-sm">
                        <img src="${img}" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-bold text-slate-800 truncate">${data}</div>
                        <div class="text-[10px] font-medium text-slate-400 truncate uppercase tracking-tight">${row.slug}</div>
                    </div>
                </div>`;
            }
          },
          {
            data: 'category_name',
            name: 'category_name',
            className: 'py-4',
            render: (data) => data ?
              `<span class="text-sm font-medium text-slate-600">${data}</span>` :
              `<span class="text-xs text-slate-300 italic">Uncategorized</span>`
          },
          {
            data: 'brand_name',
            name: 'brand_name',
            className: 'py-4',
            render: (data) => data ?
              `<span class="text-sm font-medium text-slate-600">${data}</span>` :
              `<span class="text-xs text-slate-300 italic">—</span>`
          },
          {
            data: 'total_stock',
            name: 'total_stock',
            className: 'py-4',
            render: (data, type, row) => {
              const color = data > 0 ? (data <= 5 ? 'text-amber-600 bg-amber-50 border-amber-100' :
                  'text-emerald-600 bg-emerald-50 border-emerald-100') :
                'text-slate-400 bg-slate-50 border-slate-200';
              return `
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ${color} border">${data} Units</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter ml-1">${row.variants_count} Variants</span>
                    </div>
                `;
            }
          },
          {
            data: 'price_range',
            name: 'price_range',
            className: 'py-4 font-bold text-slate-700 text-sm',
            render: (data) => data !== '—' ? `<span class="text-brand-600">{{ request()->company->baseCurrency->symbol ?? '$' }}</span>${data}` : data
          },
          {
            data: 'status',
            name: 'status',
            className: 'py-4',
            render: (data) => {
              return data ?
                `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase border border-emerald-100"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active</span>` :
                `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-bold uppercase border border-slate-200"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive</span>`;
            }
          },
          {
            data: 'action',
            // This can stay server-side or move to client-side. Let's keep server-side for now as it uses route helpers, or we can pass ID and build it here.
            // Better to standardise: I'll use the ID from row to build it client side to avoid HTML in controller.
            name: 'action',
            orderable: false,
            searchable: false,
            className: 'text-right px-8',
            render: function(data, type, row) {
              // We need the edit URL. Since we can't easily generate Laravel routes in JS string,
              // we'll rely on the server validation for the URL or just assume a pattern if safe.
              // Safest: The controller sends the `edit_url` in the row data.
              return `
                  <div class="flex items-center justify-end gap-2">
                       <a href="${row.edit_url}" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit Product">
                          <i class="fas fa-edit"></i>
                      </a>
                  </div>
                `;
            }
          }
        ],
        language: {
          search: "",
          searchPlaceholder: "Search products...",
          processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
        },
        dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
      });

      $('#btn_apply_filters').on('click', function() {
        table.ajax.reload();
      });

      $('#btn_reset_filters').on('click', function() {
        $('#filter_category, #filter_brand, #filter_status, #filter_stock').val('');
        dateRangePicker.clear();
        table.ajax.reload();
      });
    });
  </script>
@endpush
