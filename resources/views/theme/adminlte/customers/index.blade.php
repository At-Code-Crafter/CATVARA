@extends('theme.adminlte.layouts.app')

@section('title', 'Customers')

@section('content-header')
<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-7">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-users"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">Customers</h1>
          <div class="text-muted small">
            Manage customer records for <span class="font-weight-bold">{{ $company->name }}</span>.
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-5 d-flex justify-content-sm-end mt-3 mt-sm-0">
      <div class="btn-group">
        <a href="{{ route('customers.create', $company->uuid) }}" class="btn btn-primary btn-ent">
          <i class="fas fa-plus mr-1"></i> Create Customer
        </a>
        <button type="button" class="btn btn-primary btn-ent dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
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
            <div class="ent-stat-title">All Customers</div>
            <div class="ent-stat-value" id="statAllCustomers">0</div>
          </div>
          <div class="ent-stat-icon">
            <i class="fas fa-users"></i>
          </div>
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
            <div class="ent-stat-title">Active Customers</div>
            <div class="ent-stat-value" id="statActiveCustomers">0</div>
          </div>
          <div class="ent-stat-icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
        <div class="ent-stat-foot">
          <span>Currently enabled</span>
          <span class="ent-stat-badge">Active</span>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="ent-stat">
        <div class="ent-stat-body">
          <div>
            <div class="ent-stat-title">Company Type</div>
            <div class="ent-stat-value" id="statCompanyCustomers">0</div>
          </div>
          <div class="ent-stat-icon">
            <i class="fas fa-building"></i>
          </div>
        </div>
        <div class="ent-stat-foot">
          <span>B2B profiles</span>
          <span class="ent-stat-badge">Company</span>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="ent-stat">
        <div class="ent-stat-body">
          <div>
            <div class="ent-stat-title">Individual Type</div>
            <div class="ent-stat-value" id="statIndividualCustomers">0</div>
          </div>
          <div class="ent-stat-icon">
            <i class="fas fa-user"></i>
          </div>
        </div>
        <div class="ent-stat-foot">
          <span>B2C profiles</span>
          <span class="ent-stat-badge">Individual</span>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTERS --}}
  <div class="card ent-card mb-3">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <h3 class="card-title m-0">
            <i class="fas fa-sliders-h"></i> Filters
          </h3>

          <span class="ml-3 ent-chip" id="activeFiltersChip" style="display:none;">
            <i class="fas fa-filter mr-1"></i> Active
          </span>
        </div>

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
          <label class="filter-label">Customer Type</label>
          <select id="filterType" class="form-control ent-control select2">
            <option value="">All Types</option>
            <option value="INDIVIDUAL">Individual</option>
            <option value="COMPANY">Company</option>
          </select>
        </div>

        <div class="col-md-2 mb-3">
          <label class="filter-label">Status</label>
          <select id="filterStatus" class="form-control ent-control">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
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

        <div class="col-md-3 mb-3">
          <label class="filter-label">Payment Term</label>
          <select id="filterPaymentTerm" class="form-control ent-control select2">
            <option value="">All Terms</option>
            @foreach (\App\Models\Accounting\PaymentTerm::where('is_active', true)->get() as $term)
              <option value="{{ $term->id }}">{{ $term->name }}</option>
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
        <h3 class="card-title m-0">
          <i class="fas fa-table"></i> Customer Directory
        </h3>
        
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-enterprise mb-0" id="customers-table" style="width:100%">
          <thead>
            <tr>
              <th style="min-width: 200px;">Name</th>
              <th style="min-width: 120px;">Type</th>
              <th style="min-width: 220px;">Email</th>
              <th style="min-width: 140px;">Phone</th>
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

      function initSelect2() {
        if (!$.fn.select2) return;

        $('#filterType').select2({
          theme: 'bootstrap4',
          width: '100%',
          placeholder: 'All Types'
        });

        $('#filterPaymentTerm').select2({
          theme: 'bootstrap4',
          width: '100%',
          placeholder: 'All Terms'
        });
      }

      initSelect2();

      function getFilters() {
        return {
          type: $('#filterType').val() || '',
          is_active: $('#filterStatus').val() || '',
          payment_term_id: $('#filterPaymentTerm').val() || '',
          date_from: $('#filter_date_from').val() || '',
          date_to: $('#filter_date_to').val() || ''
        };
      }

      function hasActiveFilters() {
        const f = getFilters();
        return !!(f.type || f.is_active || f.payment_term_id || f.date_from || f.date_to);
      }

      function syncActiveFiltersChip() {
        if (hasActiveFilters()) {
          $('#activeFiltersChip').show();
        } else {
          $('#activeFiltersChip').hide();
        }
      }

      function setStatsLoading(isLoading) {
        const ids = ['#statAllCustomers', '#statActiveCustomers', '#statCompanyCustomers', '#statIndividualCustomers'];
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

        $.get('{{ route('customers.stats', $company->uuid) }}', getFilters())
          .done(function(res) {
            $('#statAllCustomers').text(res.all_customers ?? 0);
            $('#statActiveCustomers').text(res.active_customers ?? 0);
            $('#statCompanyCustomers').text(res.company_customers ?? 0);
            $('#statIndividualCustomers').text(res.individual_customers ?? 0);
          })
          .fail(function() {
            $('#statAllCustomers').text($('#statAllCustomers').data('prev') || 0);
            $('#statActiveCustomers').text($('#statActiveCustomers').data('prev') || 0);
            $('#statCompanyCustomers').text($('#statCompanyCustomers').data('prev') || 0);
            $('#statIndividualCustomers').text($('#statIndividualCustomers').data('prev') || 0);
          });
      }

      const table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
          [5, 'desc']
        ],

        ajax: {
          url: '{{ route('customers.index', $company->uuid) }}',
          data: function(d) {
            const f = getFilters();
            d.type = f.type;
            d.is_active = f.is_active;
            d.payment_term_id = f.payment_term_id;
            d.date_from = f.date_from;
            d.date_to = f.date_to;
          }
        },

        columns: [{
            data: 'display_name',
            name: 'customers.display_name'
          },
          {
            data: 'type_badge',
            name: 'customers.type',
            orderable: false,
            searchable: false
          },
          {
            data: 'email',
            name: 'customers.email'
          },
          {
            data: 'phone',
            name: 'customers.phone'
          },
          {
            data: 'status_badge',
            name: 'customers.is_active',
            searchable: false
          },
          {
            data: 'created_at',
            name: 'customers.created_at'
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
          searchPlaceholder: "Quick search customers...",
          search: ""
        },

        drawCallback: function() {
          // keep chip synced when table redraws
          syncActiveFiltersChip();
        }
      });

      function applyFilters() {
        table.ajax.reload(null, true);
        loadStats();
        syncActiveFiltersChip();
      }

      $('#btn_apply_filters').on('click', function(e) {
        e.preventDefault();
        applyFilters();
      });

      $('#btn_reset_filters').on('click', function(e) {
        e.preventDefault();

        $('#filterStatus').val('');
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');

        if ($.fn.select2) {
          $('#filterType').val('').trigger('change');
          $('#filterPaymentTerm').val('').trigger('change');
        } else {
          $('#filterType').val('');
          $('#filterPaymentTerm').val('');
        }

        applyFilters();
      });

      $('#btn_refresh').on('click', function(e) {
        e.preventDefault();
        applyFilters();
      });

      // Optional: auto-apply on Enter (not while typing in datatable search)
      $(document).on('keydown', function(e) {
        const isTypingInSearch = $(e.target).closest('.dataTables_filter').length > 0;
        if (isTypingInSearch) return;

        if (e.key === 'Enter') {
          applyFilters();
        }
      });

      // Initial load
      loadStats();
      syncActiveFiltersChip();
    });
  </script>
@endpush
