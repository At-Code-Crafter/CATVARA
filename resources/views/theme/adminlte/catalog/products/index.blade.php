@extends('theme.adminlte.layouts.app')

@section('title', 'Products')

@section('content-header')

<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-7">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-box-open"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">Products</h1>
          <div class="text-muted small">
            Manage product catalog for <span class="font-weight-bold">{{ active_company()?->name ?? 'Company' }}</span>.
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-5 d-flex justify-content-sm-end mt-3 mt-sm-0">
      <div class="btn-group">
        <a href="{{ company_route('catalog.products.create') }}" class="btn btn-primary btn-ent">
          <i class="fas fa-plus mr-1"></i> Add Product
        </a>
        <button type="button" class="btn btn-primary btn-ent dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
          aria-haspopup="true" aria-expanded="false">
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu dropdown-menu-right">
          <a class="dropdown-item" href="javascript:void(0)" id="btn_refresh">
            <i class="fas fa-sync-alt mr-2"></i> Refresh
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('content')
  {{-- FILTERS --}}
  <div class="card ent-card mb-3">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between">
        <h3 class="card-title">
          <i class="fas fa-sliders-h"></i> Filters
        </h3>

        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="row align-items-end">
        <div class="col-md-4 mb-3">
          <label class="filter-label">Category</label>
          <select id="filter_category" class="form-control ent-control select2">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12">
          <div class="ent-actions">
            <button id="btn_reset_filters" class="btn btn-link btn-ent">
              <i class="fas fa-undo mr-1"></i> Clear
            </button>
            <button id="btn_apply_filters" class="btn btn-secondary btn-ent">
              <i class="fas fa-filter mr-1"></i> Apply Filters
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TABLE --}}
  <div class="card ent-card">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between">
        <h3 class="card-title">
          <i class="fas fa-table"></i> Product Directory
        </h3>
        <div class="text-muted small">
          Server-side results with category filter
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-enterprise mb-0" id="products-table" style="width:100%">
          <thead>
            <tr>
              <th style="width:70px;">#</th>
              <th style="min-width: 320px;">Product</th>
              <th style="min-width: 200px;">Category</th>
              <th style="min-width: 140px;">Variants</th>
              <th style="min-width: 120px;">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {

      // Select2 (bootstrap4)
      if ($.fn.select2) {
        $('#filter_category').select2({
          theme: 'bootstrap4',
          width: '100%',
          placeholder: 'All Categories'
        });
      }

      function getFilters() {
        return {
          category_id: $('#filter_category').val() || ''
        };
      }

      const table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
          [1, 'asc']
        ],

        ajax: {
          url: "{{ company_route('catalog.products.index') }}",
          data: function(d) {
            const f = getFilters();
            d.category_id = f.category_id;
          }
        },

        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'name',
            name: 'name'
          },
          {
            data: 'category_name',
            name: 'category.name'
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
            searchable: false
          },
        ],

        dom: '<"row p-3 align-items-center"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-md-end"f>>rt<"row p-3 align-items-center"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-md-end"p>>',
        language: {
          searchPlaceholder: "Quick search products...",
          search: ""
        }
      });

      function applyFilters() {
        table.ajax.reload(null, true);
      }

      $('#btn_apply_filters').on('click', function(e) {
        e.preventDefault();
        applyFilters();
      });

      $('#btn_reset_filters').on('click', function(e) {
        e.preventDefault();

        if ($.fn.select2) {
          $('#filter_category').val('').trigger('change');
        } else {
          $('#filter_category').val('');
        }

        applyFilters();
      });

      $('#btn_refresh').on('click', function(e) {
        e.preventDefault();
        applyFilters();
      });

      // Optional: apply filters on Enter (avoid datatable search field)
      $(document).on('keydown', function(e) {
        const isTypingInSearch = $(e.target).closest('.dataTables_filter').length > 0;
        if (isTypingInSearch) return;

        if (e.key === 'Enter') {
          applyFilters();
        }
      });
    });
  </script>
@endpush
