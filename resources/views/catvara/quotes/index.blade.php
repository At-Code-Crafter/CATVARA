@extends('catvara.layouts.app')

@section('title', 'Quotations')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Quotations</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Manage and track your company's sales quotations.</p>
      </div>
      <div>
        <a href="{{ company_route('quotes.create') }}" class="btn btn-primary">
          <i class="fas fa-plus-circle mr-2"></i> Add New Quote
        </a>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-slate-100 bg-white shadow-soft">
      <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/20">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
          <i class="fas fa-filter text-brand-400"></i> Filters
        </h3>
        <button
          class="filter-toggle-btn h-8 w-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-all">
          <i class="fas fa-chevron-up filter-toggle-icon"></i>
        </button>
      </div>
      <div class="p-6 filter-card-content">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6">
          <div class="space-y-1.5">
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Customer</label>
            <select id="filter_customer" class="w-full">
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
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Date Range</label>
            <div class="input-icon-group group">
              <i class="far fa-calendar-alt text-slate-400 group-focus-within:text-brand-400 transition-colors"></i>
              <input type="text" id="filter_date_range"
                class="w-full rounded-xl border-slate-200 text-sm h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all font-semibold"
                placeholder="Select Date Range...">
            </div>
            <input type="hidden" id="filter_date_from">
            <input type="hidden" id="filter_date_to">
          </div>
        </div>
        <div class="filter-actions">
          <button id="btn_reset_filters" class="btn btn-white min-w-[120px]">Clear Filter</button>
          <button id="btn_apply_filters" class="btn btn-primary min-w-[123px]">Apply Filter</button>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0">
        <table class="table-premium w-full text-left" id="quotes-table">
          <thead>
            <tr>
              <th class="px-8!">Quote Number</th>
              <th>Date</th>
              <th>Customer</th>
              <th>Valid Until</th>
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
      // DataTable
      var table = $('#quotes-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: '{{ company_route('quotes.data') }}',
          data: function(d) {
            d.customer_id = $('#filter_customer').val();
            d.status_id = $('#filter_status').val();
            d.date_from = $('#filter_date_from').val();
            d.date_to = $('#filter_date_to').val();
          }
        },
        columns: [{
            data: 'quote_number',
            name: 'quote_number',
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
            data: 'valid_until',
            name: 'valid_until'
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
          searchPlaceholder: "Search quotes...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400"></i>'
        }
      });

      // Date Range Picker
      flatpickr("#filter_date_range", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function(selectedDates, dateStr, instance) {
          if (selectedDates.length === 2) {
            $('#filter_date_from').val(instance.formatDate(selectedDates[0], "Y-m-d"));
            $('#filter_date_to').val(instance.formatDate(selectedDates[1], "Y-m-d"));
          } else {
            $('#filter_date_from').val('');
            $('#filter_date_to').val('');
          }
        }
      });

      $('#btn_apply_filters').on('click', function() {
        table.ajax.reload();
      });

      $('#btn_reset_filters').on('click', function() {
        $('#filter_customer').val('').trigger('change');
        $('#filter_status').val('');

        // Reset Date Picker
        const picker = document.querySelector("#filter_date_range")._flatpickr;
        picker.clear();
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');

        table.ajax.reload();
      });
    });
  </script>
@endpush
