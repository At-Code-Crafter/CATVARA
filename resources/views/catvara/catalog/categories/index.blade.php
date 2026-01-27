@extends('catvara.layouts.app')

@section('title', 'Categories')

@section('content')
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6 animate-fade-in">
    <div>
      <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Categories</h1>
      <p class="text-slate-400 mt-1 font-medium">Manage your product categories and hierarchy.</p>
    </div>
    <div class="mt-4 sm:mt-0">
      <a href="{{ company_route('catalog.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle mr-2"></i> Add Category
      </a>
    </div>
  </div>

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
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
          <select id="filter_status" class="select2 w-full" data-placeholder="Filter by Status">
            <option value="">All Statuses</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Level</label>
          <select id="filter_parent" class="select2 w-full" data-placeholder="Filter by Level">
            <option value="">All Levels</option>
            <option value="__ROOT__">Root Categories Only</option>
            @if (isset($parents))
              <optgroup label="Specific Parent">
                @foreach ($parents as $p)
                  <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
              </optgroup>
            @endif
          </select>
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
      <table class="table-premium w-full text-left" id="categoriesTable">
        <thead>
          <tr>
            <th class="px-8!">Name</th>
            {{-- <th>Slug</th> --}}
            <th>Products</th>
            <th>Parent</th>
            <th>Children</th>
            <th>Status</th>
            <th class="text-right px-8!">Actions</th>
          </tr>
        </thead>
        <tbody>
          {{-- DataTables will populate this --}}
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {
      // Auto-init handled globally for Select2/Filter Card Toggle

      var table = $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: "{{ company_route('catalog.categories.index') }}",
          data: function(d) {
            d.is_active = $('#filter_status').val();
            d.parent_id = $('#filter_parent').val();
          }
        },
        columns: [{
            data: 'name_html',
            name: 'name',
            orderable: false
          },
          // Slug column commented out
          // {
          //   data: 'slug_html',
          //   name: 'slug'
          // },
          {
            data: 'products_count_html',
            name: 'products_count',
            searchable: false
          },
          {
            data: 'parent_html',
            name: 'parent_name'
          },
          {
            data: 'children_html',
            name: 'children_count',
            searchable: false
          },
          {
            data: 'status_badge',
            name: 'is_active'
          },
          {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            className: 'text-right'
          }
        ],
        language: {
          search: "",
          searchPlaceholder: "Search categories...",
          processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
        },
        dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
        drawCallback: function() {
          // Re-init any plugins if needed (tooltips)
        }
      });

      // Filter Handlers
      $('#btn_apply_filters').on('click', function() {
        table.draw();
      });

      $('#btn_reset_filters').on('click', function() {
        $('#filter_status').val('').trigger('change');
        $('#filter_parent').val('').trigger('change');
        table.draw();
      });
    });
  </script>
@endpush
