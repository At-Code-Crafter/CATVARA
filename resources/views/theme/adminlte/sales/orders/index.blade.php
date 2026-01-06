@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0 font-weight-bold text-dark">
        <i class="fas fa-file-invoice mr-2 text-primary"></i>Sales Orders
      </h1>
      <p class="text-muted small mt-1 mb-0">Manage and track your company's sales transactions and orders.</p>
    </div>
    <div class="col-sm-6 text-right">
      <a href="{{ company_route('sales-orders.create') }}" class="btn btn-primary shadow-sm px-4">
        <i class="fas fa-plus mr-1"></i> Create New Order
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

  <div class="row">
    <div class="col-12">

      {{-- ENTERPRISE FILTERS --}}
      <div class="card enterprise-filter-card shadow-sm border-0">
        <div class="card-body p-3">
          <div class="row align-items-end">
            <div class="col-md-3">
              <label class="filter-label">Customer</label>
              <select id="filter_customer" class="form-control select2 shadow-sm">
                <option value="">All Multiple Customers</option>
                @foreach ($customers as $customer)
                  <option value="{{ $customer->id }}">{{ $customer->display_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="filter-label">Status</label>
              <select id="filter_status" class="form-control shadow-sm">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                  <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
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

      {{-- SALES ORDER LIST --}}
      <div class="card shadow-sm border-0 bg-white">
        <div class="card-body p-0">
          <table class="table table-hover table-enterprise mb-0" id="orders-table" style="width:100%">
            <thead>
              <tr>
                <th class="pl-4">ORDER NUMBER</th>
                <th>DATE</th>
                <th>CUSTOMER</th>
                <th>STATUS</th>
                <th>TOTAL AMOUNT</th>
                <th class="text-right pr-4">ACTIONS</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>

    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {

      var table = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        ajax: {
          url: '{{ company_route('sales-orders.data') }}',
          data: function(d) {
            d.customer_id = $('#filter_customer').val();
            d.status_id = $('#filter_status').val();
            d.date_from = $('#filter_date_from').val();
            d.date_to = $('#filter_date_to').val();
          }
        },
        columns: [{
            data: 'order_number',
            name: 'order_number',
            className: 'pl-4'
          },
          {
            data: 'created_at',
            name: 'created_at'
          },
          {
            data: 'customer_name',
            name: 'customer.display_name'
          },
          {
            data: 'status',
            name: 'status.name',
            orderable: false
          },
          {
            data: 'grand_total',
            name: 'grand_total'
          },
          {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false,
            className: 'text-right pr-4'
          }
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

      // Style Search Input
      $('.dataTables_filter input').addClass('form-control shadow-sm bg-light border-0').css('width', '250px');

      // Filter events
      $('#btn_apply_filters').click(function() {
        table.ajax.reload();
      });

      $('#btn_reset_filters').click(function() {
        $('#filter_customer').val('').trigger('change');
        $('#filter_status').val('');
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');
        table.ajax.reload();
      });
    });
  </script>
@endpush
