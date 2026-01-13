@extends('theme.adminlte.layouts.app')

@section('title', 'Payments')

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8 col-md-12">
      <div class="d-flex align-items-start">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-money-bill-wave"></i>
          </span>
        </div>

        <div>
          <h1 class="m-0">Payments</h1>
          <div class="help-hint mb-0">Track and manage all payment transactions for your company.</div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end mt-3 mt-lg-0">
      <a href="{{ company_route('accounting.payments.create') }}" class="btn btn-primary btn-ent">
        <i class="fas fa-plus mr-1"></i> Record Payment
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">

      {{-- SUMMARY STATS --}}
      <div class="row mb-3">
        <div class="col-md-3">
          <div class="info-box bg-success mb-0">
            <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Received</span>
              <span class="info-box-number" id="stat-received">Loading...</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-danger mb-0">
            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Refunded</span>
              <span class="info-box-number" id="stat-refunded">Loading...</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-warning mb-0">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Unallocated</span>
              <span class="info-box-number" id="stat-unallocated">Loading...</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-info mb-0">
            <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Payment Count</span>
              <span class="info-box-number" id="stat-count">Loading...</span>
            </div>
          </div>
        </div>
      </div>

      {{-- FILTERS --}}
      <div class="card ent-card mb-3">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
            <span class="ent-chip"><i class="fas fa-sliders-h"></i> Refine results</span>
          </div>
        </div>

        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-2">
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
              <label class="filter-label">Method</label>
              <select id="filter_method" class="form-control ent-control">
                <option value="">All Methods</option>
                @foreach ($methods as $method)
                  <option value="{{ $method->id }}">{{ $method->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-1">
              <label class="filter-label">Source</label>
              <select id="filter_source" class="form-control ent-control">
                <option value="">All</option>
                <option value="MANUAL">Manual</option>
                <option value="POS">POS</option>
                <option value="WEB">Web</option>
                <option value="API">API</option>
              </select>
            </div>

            <div class="col-md-1">
              <label class="filter-label">Direction</label>
              <select id="filter_direction" class="form-control ent-control">
                <option value="">All</option>
                <option value="IN">Received</option>
                <option value="OUT">Refund</option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="filter-label">Date Range</label>
              <div class="d-flex">
                <input type="date" id="filter_date_from" class="form-control ent-control mr-1" placeholder="From">
                <input type="date" id="filter_date_to" class="form-control ent-control" placeholder="To">
              </div>
            </div>

            <div class="col-md-2">
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

      {{-- TABLE --}}
      <div class="card ent-card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-list"></i> Payment Records</h3>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-enterprise mb-0" id="payments-table" style="width:100%">
              <thead>
                <tr>
                  <th class="pl-4">Payment #</th>
                  <th>Date</th>
                  <th>Customer</th>
                  <th>Method</th>
                  <th>Direction</th>
                  <th>Amount</th>
                  <th>Unallocated</th>
                  <th>Status</th>
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
    $(function () {

      // Select2
      $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
      });

      // Load stats
      function loadStats() {
        $.get('{{ company_route("accounting.payments.stats") }}', {
          date_from: $('#filter_date_from').val(),
          date_to: $('#filter_date_to').val()
        }, function(data) {
          $('#stat-received').text(parseFloat(data.total_received || 0).toLocaleString('en-US', {minimumFractionDigits: 2}));
          $('#stat-refunded').text(parseFloat(data.total_refunded || 0).toLocaleString('en-US', {minimumFractionDigits: 2}));
          $('#stat-unallocated').text(parseFloat(data.total_unallocated || 0).toLocaleString('en-US', {minimumFractionDigits: 2}));
          $('#stat-count').text(data.payment_count || 0);
        });
      }
      loadStats();

      // DataTable
      var table = $('#payments-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        ajax: {
          url: '{{ company_route("accounting.payments.data") }}',
          data: function (d) {
            d.customer_id = $('#filter_customer').val();
            d.status_id = $('#filter_status').val();
            d.payment_method_id = $('#filter_method').val();
            d.source = $('#filter_source').val();
            d.direction = $('#filter_direction').val();
            d.date_from = $('#filter_date_from').val();
            d.date_to = $('#filter_date_to').val();
          }
        },
        columns: [
          { data: 'payment_number', name: 'payment_number', className: 'pl-4' },
          { data: 'paid_at', name: 'paid_at' },
          { data: 'customer_name', name: 'customer.display_name' },
          { data: 'method_name', name: 'method.name', orderable: false },
          { data: 'direction', name: 'direction' },
          { data: 'amount', name: 'amount' },
          { data: 'unallocated', name: 'unallocated_amount' },
          { data: 'status', name: 'status.name', orderable: false },
          { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'd-flex justify-content-end pr-4' }
        ],
        order: [[1, 'desc']],
        dom: '<"row p-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-md-end"f>>rt<"row p-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
          searchPlaceholder: "Quick search...",
          search: ""
        },
        drawCallback: function () {
          if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
        }
      });

      // Style search input
      setTimeout(function () {
        $('.dataTables_filter input').addClass('form-control ent-control').css('width', '260px');
      }, 0);

      // Apply filters
      $('#btn_apply_filters').on('click', function () {
        table.ajax.reload();
        loadStats();
      });

      // Clear filters
      $('#btn_reset_filters').on('click', function () {
        $('#filter_customer').val('').trigger('change');
        $('#filter_status').val('');
        $('#filter_method').val('');
        $('#filter_source').val('');
        $('#filter_direction').val('');
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');
        table.ajax.reload();
        loadStats();
      });

    });
  </script>
@endpush
