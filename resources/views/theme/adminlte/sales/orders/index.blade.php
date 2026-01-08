@extends('theme.adminlte.layouts.app')

@section('title', 'Sales Orders')

@section('content-header')
<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8 col-md-12">
      <div class="d-flex align-items-start">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-file-invoice"></i>
          </span>
        </div>

        <div>
          <h1 class="m-0">Sales Orders</h1>
          <div class="help-hint mb-0">Manage and track your company’s sales transactions and orders.</div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end mt-3 mt-lg-0">
      <a href="{{ company_route('sales-orders.create') }}" class="btn btn-primary btn-ent">
        <i class="fas fa-plus mr-1"></i> Create New Order
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">

      {{-- FILTERS (Enterprise) --}}
      <div class="card ent-card mb-3">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
            <span class="ent-chip"><i class="fas fa-sliders-h"></i> Refine results</span>
          </div>
        </div>

        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-3">
              <label class="filter-label">Customer</label>
              <select id="filter_customer" class="form-control ent-control select2">
                <option value="">All Customers</option>
                @foreach ($customers as $customer)
                  <option value="{{ $customer->id }}">{{ $customer->display_name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label class="filter-label">Status</label>
              <select id="filter_status" class="form-control ent-control">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                  <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label class="filter-label">Date From</label>
              <input type="date" id="filter_date_from" class="form-control ent-control">
            </div>

            <div class="col-md-2">
              <label class="filter-label">Date To</label>
              <input type="date" id="filter_date_to" class="form-control ent-control">
            </div>

            <div class="col-md-3">
              <div class="ent-actions">
                <button type="button" id="btn_reset_filters" class="btn btn-outline-secondary btn-ent">
                  Clear
                </button>
                <button type="button" id="btn_apply_filters" class="btn btn-secondary btn-ent">
                  <i class="fas fa-filter mr-1"></i> Apply
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- TABLE (Enterprise) --}}
      <div class="card ent-card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-list"></i> Orders</h3>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-enterprise mb-0" id="orders-table" style="width:100%">
              <thead>
                <tr>
                  <th class="pl-4">Order Number</th>
                  <th>Date</th>
                  <th>Customer</th>
                  <th>Status</th>
                  <th>Total Amount</th>
                  <th class="text-right pr-4">Actions</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {

      // Select2
      $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
      });

      // DataTable
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
        dom: '<"row p-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-md-end"f>>rt<"row p-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
          searchPlaceholder: "Quick search...",
          search: ""
        },
        drawCallback: function() {
          // Tooltips inside table (for action icons)
          if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
        }
      });

      // Make the built-in search input use enterprise input look (without adding page CSS)
      setTimeout(function() {
        $('.dataTables_filter input')
          .addClass('form-control ent-control')
          .css('width', '260px');
      }, 0);

      // Apply filters
      $('#btn_apply_filters').on('click', function() {
        table.ajax.reload();
      });

      // Clear filters
      $('#btn_reset_filters').on('click', function() {
        $('#filter_customer').val('').trigger('change');
        $('#filter_status').val('');
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');
        table.ajax.reload();
      });

    });
  </script>
@endpush
