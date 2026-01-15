@extends('catvara.layouts.app')

@section('title', 'Products')

@section('content')
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6 animate-fade-in">
    <div>
      <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Product Catalog</h1>
      <p class="text-slate-400 mt-1 font-medium">Manage your complete product catalog and variants.</p>
    </div>
    <div class="mt-4 sm:mt-0">
      <a href="{{ company_route('catalog.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle mr-2"></i> Add Product
      </a>
    </div>
  </div>

  {{-- Filters --}}
  <div class="card p-6 border-slate-100 bg-white shadow-soft mb-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="space-y-1.5">
        <label for="filter_category"
          class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Category</label>
        <select id="filter_category" class="select2 w-full">
          <option value="">All Categories</option>
          @foreach ($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="space-y-1.5">
        <label for="filter_status"
          class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status</label>
        <select id="filter_status" class="select2 w-full">
          <option value="">All Status</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
      <div class="space-y-1.5">
        <label for="filter_stock" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Stock
          Level</label>
        <select id="filter_stock" class="select2 w-full">
          <option value="">All Stock</option>
          <option value="in_stock">In Stock</option>
          <option value="low_stock">Low Stock</option>
          <option value="out_of_stock">Out of Stock</option>
        </select>
      </div>
    </div>
    <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-slate-50">
      <button id="btn_reset_filters" class="btn btn-white min-w-[120px]">Reset</button>
      <button id="btn_apply_filters" class="btn btn-primary min-w-[120px]">Filter</button>
    </div>
  </div>

  <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
    <div class="p-0">
      <table class="table-premium w-full text-left" id="productsTable">
        <thead>
          <tr>
            <th class="px-8!">Product</th>
            <th>Category</th>
            <th>Variants</th>
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
      const table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: "{{ company_route('catalog.products.index') }}",
          data: function(d) {
            d.category_id = $('#filter_category').val();
            d.status = $('#filter_status').val();
            d.stock_level = $('#filter_stock').val();
          }
        },
        columns: [{
            data: 'name',
            name: 'name'
          },
          {
            data: 'category_name',
            name: 'category_name'
          },
          {
            data: 'variants_count',
            name: 'variants_count',
            searchable: false
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
          searchPlaceholder: "Search products...",
          processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
        },
        dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
      });

      $('#btn_apply_filters').on('click', function() {
        table.ajax.reload();
      });

      $('#btn_reset_filters').on('click', function() {
        $('#filter_category, #filter_status, #filter_stock').val('').trigger('change');
        table.ajax.reload();
      });
    });
  </script>
@endpush
