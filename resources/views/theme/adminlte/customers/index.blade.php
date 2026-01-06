@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0">Customers</h1>
      <small class="text-muted">Manage customer records for {{ $company->name }}.</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('customers.create', $company->uuid) }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Create Customer
      </a>
    </div>
  </div>
@endsection

@section('content')
  <style>
    .enterprise-filter-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 0.1em 0.5em;
    }

    .table-enterprise thead th {
      background-color: #f1f5f9;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.05em;
      font-weight: 700;
      color: #475569;
      border-bottom: 2px solid #e2e8f0;
    }

    .table-enterprise tbody td {
      vertical-align: middle;
      padding: 1rem 0.75rem;
    }

    .filter-label {
      font-size: 0.7rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      margin-bottom: 0.25rem;
    }
  </style>
  {{-- TOP STATS --}}
  <div class="row">
    <div class="col-lg-3 col-md-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3 id="statAllCustomers">0</h3>
          <p>All Customers</p>
        </div>
        <div class="icon"><i class="fas fa-users"></i></div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3 id="statActiveCustomers">0</h3>
          <p>Active Customers</p>
        </div>
        <div class="icon"><i class="fas fa-check-circle"></i></div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="small-box bg-primary">
        <div class="inner">
          <h3 id="statCompanyCustomers">0</h3>
          <p>Company Type</p>
        </div>
        <div class="icon"><i class="fas fa-building"></i></div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="small-box bg-secondary">
        <div class="inner">
          <h3 id="statIndividualCustomers">0</h3>
          <p>Individual Type</p>
        </div>
        <div class="icon"><i class="fas fa-user"></i></div>
      </div>
    </div>
  </div>

  {{-- FILTERS CARD --}}


  <div class="card enterprise-filter-card shadow-sm border-0">
    <div class="card-body p-3">
      <div class="row align-items-end">
        <div class="col-md-3">
          <label class="filter-label">Customer Type</label>
          <select id="filterType" class="form-control select2 shadow-sm">
            <option value="">All Types</option>
            <option value="INDIVIDUAL">Individual</option>
            <option value="COMPANY">Company</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="filter-label">Status</label>
          <select id="filterStatus" class="form-control shadow-sm">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="filter-label">Date From</label>
          <input type="date" id="filter_date_from" class="form-control shadow-sm">
        </div>
        <div class="col-md-2">
          <label class="filter-label">Date To</label>
          <input type="date" id="filter_date_to" class="form-control shadow-sm">
        </div>
        <div class="col-md-3 text-right">
          <button id="btn_reset_filters" class="btn btn-link text-muted pr-3">Clear Filters</button>
          <button id="btn_apply_filters" class="btn btn-secondary px-4 shadow-sm">
            <i class="fas fa-filter mr-1"></i> Apply
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- TABLE CARD --}}
  <div class="card shadow-sm border-0 bg-white">
    <div class="card-body p-0">
      <table class="table table-hover table-enterprise mb-0" id="customers-table" style="width:100%">
        <thead>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Created</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {

      function getFilters() {
        return {
          type: $('#filterType').val(),
          is_active: $('#filterStatus').val()
        };
      }

      function loadStats() {
        $.get('{{ route('customers.stats', $company->uuid) }}', getFilters(), function(res) {
          $('#statAllCustomers').text(res.all_customers ?? 0);
          $('#statActiveCustomers').text(res.active_customers ?? 0);
          $('#statCompanyCustomers').text(res.company_customers ?? 0);
          $('#statIndividualCustomers').text(res.individual_customers ?? 0);
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
        ], // Created
        ajax: {
          url: '{{ route('customers.index', $company->uuid) }}',
          data: function(d) {
            const f = getFilters();
            d.type = f.type;
            d.is_active = f.is_active;
          }
        },
        columns: [{
            data: 'display_name',
            name: 'customers.display_name'
          },
          {
            data: 'type_badge',
            name: 'customers.type'
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
          },
        ],
        order: [
          [1, 'desc']
        ],
        dom: '<"row p-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row p-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
          searchPlaceholder: "Quick search...",
          search: ""
        }
      });

      // Initial load
      loadStats();

      $('#btn_apply_filters').on('click', function() {
        table.ajax.reload();
        loadStats();
      });

      $('#btn_reset_filters').on('click', function() {
        $('#filterType').val('');
        $('#filterStatus').val('');
        table.ajax.reload();
        loadStats();
      });

    });
  </script>
@endpush
