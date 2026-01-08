@extends('theme.adminlte.layouts.app')

@section('title', 'Categories')

@section('content-header')
<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-7">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-sitemap"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">Categories</h1>
          <div class="text-muted small">
            Manage catalog categories for <span class="font-weight-bold">{{ active_company()?->name ?? 'Company' }}</span>.
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-5 d-flex justify-content-sm-end mt-3 mt-sm-0">
      <div class="btn-group">
        <a href="{{ company_route('catalog.categories.create') }}" class="btn btn-primary btn-ent">
          <i class="fas fa-plus mr-1"></i> Add Category
        </a>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
          aria-haspopup="true" aria-expanded="false">
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu dropdown-menu-right">
          <a class="dropdown-item" href="javascript:void(0)" id="btn_refresh">
            <i class="fas fa-sync-alt mr-2"></i> Refresh
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-muted" href="javascript:void(0)">
            <i class="far fa-file-excel mr-2"></i> Export (coming soon)
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('content')

  {{-- TOP STATS --}}
  <div class="row">
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="ent-stat">
        <div class="ent-stat-body">
          <div>
            <div class="ent-stat-title">All Categories</div>
            <div class="ent-stat-value" id="statAll">0</div>
          </div>
          <div class="ent-stat-icon"><i class="fas fa-layer-group"></i></div>
        </div>
        <div class="ent-stat-foot">
          <span>Company scope</span>
          <span class="ent-stat-badge">Total</span>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="ent-stat">
        <div class="ent-stat-body">
          <div>
            <div class="ent-stat-title">Active</div>
            <div class="ent-stat-value" id="statActive">0</div>
          </div>
          <div class="ent-stat-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="ent-stat-foot">
          <span>Enabled</span>
          <span class="ent-stat-badge">Active</span>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="ent-stat">
        <div class="ent-stat-body">
          <div>
            <div class="ent-stat-title">Parents</div>
            <div class="ent-stat-value" id="statParents">0</div>
          </div>
          <div class="ent-stat-icon"><i class="fas fa-folder"></i></div>
        </div>
        <div class="ent-stat-foot">
          <span>Top level</span>
          <span class="ent-stat-badge">Root</span>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="ent-stat">
        <div class="ent-stat-body">
          <div>
            <div class="ent-stat-title">Children</div>
            <div class="ent-stat-value" id="statChildren">0</div>
          </div>
          <div class="ent-stat-icon"><i class="fas fa-level-down-alt"></i></div>
        </div>
        <div class="ent-stat-foot">
          <span>Nested</span>
          <span class="ent-stat-badge">Child</span>
        </div>
      </div>
    </div>
  </div>

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
        <div class="col-md-2 mb-3">
          <label class="filter-label">Status</label>
          <select id="filterStatus" class="form-control ent-control">
            <option value="">All</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <div class="col-md-2 mb-3">
          <label class="filter-label">Parent Category</label>
          <select id="filterParent" class="form-control ent-control select2">
            <option value="">All</option>
            <option value="__ROOT__">Only Parent (Root)</option>
            @foreach($parents as $p)
              <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2 mb-3">
          <label class="filter-label">Has Children</label>
          <select id="filterHasChildren" class="form-control ent-control">
            <option value="">All</option>
            <option value="1">Yes (Has child)</option>
            <option value="0">No (Leaf)</option>
          </select>
        </div>

        <div class="col-md-2 mb-3">
          <label class="filter-label">Date From</label>
          <input type="date" id="filter_date_from" class="form-control ent-control">
        </div>

        <div class="col-md-2 mb-3">
          <label class="filter-label">Date To</label>
          <input type="date" id="filter_date_to" class="form-control ent-control">
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
          <i class="fas fa-table"></i> Category Directory
        </h3>
        <div class="text-muted small">
          
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-enterprise mb-0" id="categories-table" style="width:100%">
          <thead>
            <tr>
              <th style="min-width: 320px;">Name</th>
              <th style="min-width: 220px;">Slug</th>
              <th style="min-width: 200px;">Parent</th>
              <th style="min-width: 140px;">Children</th>
              <th style="min-width: 120px;">Status</th>
              <th style="min-width: 140px;">Created</th>
              <th style="min-width: 120px;">Action</th>
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

      if ($.fn.select2) {
        $('#filterParent').select2({
          theme: 'bootstrap4',
          width: '100%',
          placeholder: 'All'
        });
      }

      if ($.fn.tooltip) {
        $('body').tooltip({
          selector: '[data-toggle="tooltip"]',
          trigger: 'hover',
          container: 'body',
          boundary: 'window'
        });
      }

      function getFilters() {
        return {
          is_active: $('#filterStatus').val() || '',
          parent_id: $('#filterParent').val() || '',
          has_children: $('#filterHasChildren').val() || '',
          date_from: $('#filter_date_from').val() || '',
          date_to: $('#filter_date_to').val() || ''
        };
      }

      function setStatsLoading(isLoading) {
        const ids = ['#statAll', '#statActive', '#statParents', '#statChildren'];
        ids.forEach(function(sel) {
          const $el = $(sel);
          if (isLoading) {
            $el.data('prev', $el.text());
            $el.text('…');
          }
        });
      }

      function loadStats() {
        setStatsLoading(true);

        $.get('{{ company_route('catalog.categories.stats') }}', getFilters())
          .done(function(res) {
            $('#statAll').text(res.all_categories ?? 0);
            $('#statActive').text(res.active_categories ?? 0);
            $('#statParents').text(res.parent_categories ?? 0);
            $('#statChildren').text(res.child_categories ?? 0);
          })
          .fail(function() {
            $('#statAll').text($('#statAll').data('prev') || 0);
            $('#statActive').text($('#statActive').data('prev') || 0);
            $('#statParents').text($('#statParents').data('prev') || 0);
            $('#statChildren').text($('#statChildren').data('prev') || 0);
          });
      }

      const table = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[5, 'desc']], // created_at

        ajax: {
          url: '{{ company_route('catalog.categories.index') }}',
          data: function(d) {
            const f = getFilters();
            d.is_active = f.is_active;
            d.parent_id = f.parent_id;
            d.has_children = f.has_children;
            d.date_from = f.date_from;
            d.date_to = f.date_to;
          }
        },

        // IMPORTANT: column "name" must match selected aliases from controller query
        columns: [
          { data: 'name_html', name: 'name' },
          { data: 'slug_html', name: 'slug' },
          { data: 'parent_html', name: 'parent_name', orderable: true, searchable: true },
          { data: 'children_html', name: 'children_count', searchable: false },
          { data: 'status_badge', name: 'is_active', searchable: false },
          { data: 'created_at', name: 'created_at', searchable: false },
          { data: 'action', name: 'action', orderable: false, searchable: false }
        ],

        dom: '<"row p-3 align-items-center"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-md-end"f>>rt<"row p-3 align-items-center"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-md-end"p>>',
        language: {
          searchPlaceholder: "Quick search categories...",
          search: ""
        }
      });

      function applyFilters() {
        table.ajax.reload(null, true);
        loadStats();
      }

      $('#btn_apply_filters').on('click', function(e) {
        e.preventDefault();
        applyFilters();
      });

      $('#btn_reset_filters').on('click', function(e) {
        e.preventDefault();

        $('#filterStatus').val('');
        $('#filterHasChildren').val('');
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');

        if ($.fn.select2) {
          $('#filterParent').val('').trigger('change');
        } else {
          $('#filterParent').val('');
        }

        applyFilters();
      });

      $('#btn_refresh').on('click', function(e) {
        e.preventDefault();
        applyFilters();
      });

      $(document).on('keydown', function(e) {
        const isTypingInSearch = $(e.target).closest('.dataTables_filter').length > 0;
        if (isTypingInSearch) return;

        if (e.key === 'Enter') {
          applyFilters();
        }
      });

      loadStats();
    });
  </script>
@endpush
