@extends('theme.adminlte.layouts.app')

@section('title', 'Attributes')

@section('content-header')

  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-7">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-tags"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">Attributes</h1>
          <div class="text-muted small">Manage attributes and their values.</div>
        </div>
      </div>
    </div>

    <div class="col-sm-5 d-flex justify-content-sm-end mt-3 mt-sm-0">
      <div class="btn-group">
        <a href="{{ company_route('catalog.attributes.create') }}" class="btn btn-primary btn-ent">
          <i class="fas fa-plus mr-1"></i> Add Attribute
        </a>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
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
  {{-- No inline CSS: your enterprise SCSS/CSS already covers ent-card, ent-control, table-enterprise, select2 --}}
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
        <div class="col-md-3 mb-3">
          <label class="filter-label">Status</label>
          <select id="filterStatus" class="form-control ent-control">
            <option value="">All</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
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

  <div class="card ent-card">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between">
        <h3 class="card-title">
          <i class="fas fa-table"></i> Attribute List
        </h3>
        <div class="text-muted small">Server-side results</div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-enterprise mb-0" id="attributes-table" style="width:100%">
          <thead>
            <tr>
              <th style="min-width: 200px;">Name</th>
              <th style="min-width: 140px;">Code</th>
              <th style="min-width: 320px;">Values</th>
              <th style="min-width: 120px;">Status</th>
              <th style="min-width: 140px;">Created</th>
              <th style="min-width: 90px;">Action</th>
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

      function getFilters() {
        return {
          is_active: $('#filterStatus').val() || '',
          has_values: $('#filterHasValues').val() || ''
        };
      }

      function initTooltips() {
        if ($.fn.tooltip) {
          $('[data-toggle="tooltip"]').tooltip({
            container: 'body'
          });
        }
      }

      const table = $('#attributes-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
          [4, 'desc']
        ], // created_at

        ajax: {
          url: '{{ company_route('catalog.attributes.index') }}',
          data: function(d) {
            const f = getFilters();
            d.is_active = f.is_active;
            d.has_values = f.has_values;
          }
        },

        columns: [{
            data: 'name',
            name: 'attributes.name'
          },
          {
            data: 'code',
            name: 'attributes.code',
            orderable: false,
            searchable: true
          },
          {
            data: 'values_badges',
            name: 'values_badges',
            orderable: false,
            searchable: false
          },
          {
            data: 'status_badge',
            name: 'attributes.is_active',
            orderable: true,
            searchable: false
          },
          {
            data: 'created_at',
            name: 'attributes.created_at'
          },
          {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
          }
        ],

        dom: '<"row p-3 align-items-center"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-md-end"f>>rt<"row p-3 align-items-center"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-md-end"p>>',
        language: {
          searchPlaceholder: "Quick search attributes...",
          search: ""
        },

        drawCallback: function() {
          initTooltips();
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
        $('#filterStatus').val('');
        $('#filterHasValues').val('');
        applyFilters();
      });

      $('#btn_refresh').on('click', function(e) {
        e.preventDefault();
        applyFilters();
      });

      initTooltips();
    });
  </script>
@endpush
