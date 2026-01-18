@extends('catvara.layouts.app')

@section('title', 'Customers Directory')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Customers Directory</h1>
        <p class="text-slate-400 text-sm mt-1 font-medium flex items-center gap-2">
          Manage profiles for <span class="text-brand-500 font-bold">{{ $company->name }}</span>
        </p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('customers.export', $company->uuid) }}"
          class="btn btn-white shadow-sm hover:shadow-md transition-all">
          <i class="fas fa-file-export mr-2 text-brand-500"></i> Export Customers
        </a>
        <a href="{{ route('customers.create', $company->uuid) }}" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-plus-circle mr-2"></i> Register Customer
        </a>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Total Customers -->
      <div
        class="card p-6 bg-white border-slate-100 shadow-soft group hover:shadow-xl hover:shadow-brand-500/5 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
          <div
            class="h-12 w-12 rounded-2xl bg-brand-50 text-brand-500 flex items-center justify-center text-xl transition-transform group-hover:scale-110">
            <i class="fas fa-users"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-wider">Overall</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Records</p>
        <p class="text-2xl font-black text-slate-800" id="statAllCustomers">0</p>
      </div>

      <!-- Active Customers -->
      <div
        class="card p-6 bg-white border-slate-100 shadow-soft group hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
          <div
            class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl transition-transform group-hover:scale-110">
            <i class="fas fa-check-circle"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-emerald-100 text-emerald-600 rounded text-[9px] font-black uppercase tracking-wider">Active</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Live Profiles</p>
        <p class="text-2xl font-black text-slate-800" id="statActiveCustomers">0</p>
      </div>

      <!-- Company Type -->
      <div
        class="card p-6 bg-white border-slate-100 shadow-soft group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
          <div
            class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl transition-transform group-hover:scale-110">
            <i class="fas fa-building"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-indigo-100 text-indigo-600 rounded text-[9px] font-black uppercase tracking-wider">B2B</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Corporate</p>
        <p class="text-2xl font-black text-slate-800" id="statCompanyCustomers">0</p>
      </div>

      <!-- Individual Type -->
      <div
        class="card p-6 bg-white border-slate-100 shadow-soft group hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
          <div
            class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl transition-transform group-hover:scale-110">
            <i class="fas fa-user-tie"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-amber-100 text-amber-600 rounded text-[9px] font-black uppercase tracking-wider">B2C</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Private Users</p>
        <p class="text-2xl font-black text-slate-800" id="statIndividualCustomers">0</p>
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
          <!-- Type Filter -->
          <div class="space-y-1.5">
            <label for="filterType" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Profile
              Type</label>
            <select id="filterType" class="w-full">
              <option value="">All Types</option>
              <option value="INDIVIDUAL">Individual</option>
              <option value="COMPANY">Company</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="space-y-1.5">
            <label for="filterStatus"
              class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status</label>
            <select id="filterStatus" class="w-full">
              <option value="">All Statuses</option>
              <option value="1">Active Only</option>
              <option value="0">Inactive Only</option>
            </select>
          </div>

          <!-- Payment Term Filter -->
          <div class="space-y-1.5 md:col-span-1 lg:col-span-1">
            <label for="filterPaymentTerm"
              class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Payment Term</label>
            <select id="filterPaymentTerm" class="w-full">
              <option value="">All Terms</option>
              @foreach (\App\Models\Accounting\PaymentTerm::where('is_active', true)->get() as $term)
                <option value="{{ $term->id }}">{{ $term->name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Date Range Filter -->
          <div class="space-y-1.5">
            <label for="filterDateRange"
              class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Registration Window</label>
            <div class="input-icon-group group date-range-fix">
              <i class="far fa-calendar-alt text-slate-400 group-focus-within:text-brand-400 transition-colors"></i>
              <input type="text" id="filterDateRange" class="w-full pl-10" placeholder="Select date range...">
            </div>
          </div>
        </div>

        <div class="filter-actions mt-6 flex justify-end gap-3">
          <button id="btnClearFilters" class="btn btn-white min-w-[120px]">Clear Filter</button>
          <button id="btnApplyFilters" class="btn btn-primary min-w-[123px]">Apply Filter</button>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0 overflow-x-auto">
        <table class="table-premium w-full text-left" id="customers-table">
          <thead>
            <tr>
              <th class="px-8! w-[300px]">Identity</th>
              <th class="w-[150px]">Type</th>
              <th>Contact Details</th>
              <th class="text-center">Discount</th>
              <th class="text-center">Status</th>
              <th>Registration</th>
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
    $(function () {
      // Date Range Picker Initialize
      const dateRangePicker = flatpickr("#filterDateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "M j, Y",
        onReady: (selectedDates, dateStr, instance) => {
          $(instance.altInput).addClass(
            'w-full pl-10 border-slate-200 rounded-xl text-sm h-[44px] font-semibold transition-all focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10'
          );
        }
      });

      const table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
          url: '{{ route('customers.index', $company->uuid) }}',
          data: function (d) {
            d.type = $('#filterType').val();
            d.is_active = $('#filterStatus').val();
            d.payment_term_id = $('#filterPaymentTerm').val();
            const dateRange = $('#filterDateRange').val();
            if (dateRange && dateRange.includes(' to ')) {
              const dates = dateRange.split(' to ');
              d.date_from = dates[0];
              d.date_to = dates[1];
            }
          }
        },
        columns: [{
          data: 'display_name',
          name: 'customers.display_name',
          className: 'px-8 py-4',
          render: (data, type, row) => `
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-brand-500 font-black text-xs shadow-sm">
                    ${data ? data.charAt(0).toUpperCase() : '?'}
                  </div>
                  <div>
                    <div class="font-bold text-slate-800 leading-tight">${data}</div>
                    <div class="text-[10px] text-slate-400 font-medium mt-0.5">
                      ${row.type === 'COMPANY' ? (row.legal_name || 'Legal Entity Not Set') : 'Individual Profile'}
                    </div>
                  </div>
                </div>`
        },
        {
          data: 'type',
          name: 'customers.type',
          className: 'py-4',
          render: (data, type, row) => {
            const isCompany = data === 'COMPANY';
            return `<span class="px-2.5 py-1 rounded-lg ${isCompany ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600'} text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit">
                  <i class="fas ${isCompany ? 'fa-building' : 'fa-user'}"></i>
                  ${data}
                </span>`;
          }
        },
        {
          data: 'contact_info',
          name: 'customers.email',
          className: 'py-4',
          orderable: false,
          render: (data, type, row) => `
                <div class="space-y-1">
                  <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <i class="far fa-envelope text-slate-300 w-4"></i>
                    ${row.email || '<span class="text-slate-300">N/A</span>'}
                  </div>
                  <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <i class="fas fa-phone-alt text-slate-300 w-4"></i>
                    ${row.phone || '<span class="text-slate-300">N/A</span>'}
                  </div>
                </div>`
        },
        {
          data: 'percentage_discount',
          className: 'text-center py-4',
          render: (data) => data > 0 ?
            `<span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded font-black text-xs">-${parseFloat(data)}%</span>` :
            `<span class="text-slate-300">-</span>`
        },
        {
          data: 'status_badge',
          name: 'customers.is_active',
          className: 'text-center py-4',
          render: (data, type, row) => row.is_active ?
            `<span class="status-dot dot-success">Active</span>` :
            `<span class="status-dot dot-danger">Inactive</span>`
        },
        {
          data: 'created_at',
          className: 'py-4',
          render: (data) => `
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none">${data}</div>
                <div class="text-[9px] text-slate-400 mt-1">Acquired Date</div>`
        },
        {
          data: 'action',
          orderable: false,
          searchable: false,
          className: 'text-right px-8 py-4'
        }
        ],
        dom: '<"flex justify-between items-center p-6"lBf>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Search ID, name, email...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400 text-2xl"></i>'
        }
      });

      // Filter Toggle
      $('.filter-toggle-btn').on('click', function () {
        $('.filter-card-content').slideToggle(300);
        $('.filter-toggle-icon').toggleClass('fa-chevron-up fa-chevron-down');
      });

      // Status Stats Loading
      function loadStats() {
        const params = {
          type: $('#filterType').val(),
          is_active: $('#filterStatus').val(),
          payment_term_id: $('#filterPaymentTerm').val()
        };

        const dateRange = $('#filterDateRange').val();
        if (dateRange && dateRange.includes(' to ')) {
          const dates = dateRange.split(' to ');
          params.date_from = dates[0];
          params.date_to = dates[1];
        }

        $.get('{{ route('customers.stats', $company->uuid) }}', params).done(function (res) {
          $('#statAllCustomers').text(res.all_customers).addClass('animate-pulse-once');
          $('#statActiveCustomers').text(res.active_customers).addClass('animate-pulse-once');
          $('#statCompanyCustomers').text(res.company_customers).addClass('animate-pulse-once');
          $('#statIndividualCustomers').text(res.individual_customers).addClass('animate-pulse-once');

          setTimeout(() => $('.animate-pulse-once').removeClass('animate-pulse-once'), 500);
        });
      }

      $('#btnApplyFilters').on('click', () => {
        table.ajax.reload();
        loadStats();
      });

      $('#btnClearFilters').on('click', () => {
        $('#filterType').val('');
        $('#filterStatus').val('');
        $('#filterPaymentTerm').val('');
        dateRangePicker.clear();
        table.ajax.reload();
        loadStats();
      });

      // Initial stats load
      loadStats();
    });
  </script>
@endpush