@extends('catvara.layouts.app')

@section('title', 'Sales Orders')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Sales List</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Manage and track your company’s sales transactions.</p>
      </div>
      <div>
        <a href="{{ company_route('sales-orders.create') }}" class="btn btn-primary">
          <i class="fas fa-plus-circle mr-2"></i> Add New Order
        </a>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="card p-6 border-slate-100 bg-white shadow-soft">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Customer</label>
          <select id="filter_customer" class="select2 w-full">
            <option value="">All Customers</option>
            @foreach ($customers as $customer)
              <option value="{{ $customer->id }}">{{ $customer->display_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status</label>
          <select id="filter_status" class="w-full">
            <option value="">All Statuses</option>
            @foreach ($statuses as $status)
              <option value="{{ $status->id }}">{{ $status->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Date From</label>
          <input type="date" id="filter_date_from" class="w-full">
        </div>
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Date To</label>
          <input type="date" id="filter_date_to" class="w-full">
        </div>
      </div>
      <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-slate-50">
        <button id="btn_reset_filters" class="btn btn-white">Reset</button>
        <button id="btn_apply_filters" class="btn btn-primary px-8">Filter</button>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0">
        <table class="table-premium w-full text-left" id="orders-table">
          <thead>
            <tr>
              <th class="px-8!">Order Number</th>
              <th>Date</th>
              <th>Customer</th>
              <th>Status</th>
              <th>Amount</th>
              <th class="text-right px-8!">Actions</th>
            </tr>
          </thead>
          <tbody>
            {{-- Populated by DataTables --}}
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Select2
      $('#filter_customer').select2({
        width: '100%'
      });

      // DataTable
      var table = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
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
            className: 'px-8 font-bold text-slate-700'
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
            name: 'grand_total',
            className: 'font-bold'
          },
          {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false,
            className: 'text-right px-8'
          }
        ],
        order: [
          [1, 'desc']
        ],
        dom: '<"flex justify-between items-center p-6"l>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Search orders...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400"></i>'
        }
      });

      $('#btn_apply_filters').on('click', function() {
        table.ajax.reload();
      });

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
