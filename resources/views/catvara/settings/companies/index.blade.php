@extends('catvara.layouts.app')

@section('title', 'Tenant Management')

@section('content')
  <div class="space-y-8 animate-fade-in pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Tenants Directory</h1>
        <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
          Manage system-wide companies and subscription states
        </p>
      </div>
      <div>
        <a href="{{ route('tenants.create') }}" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-plus-circle mr-2"></i> Create New Tenant
        </a>
      </div>
    </div>

    {{-- 1. Stats Row (4 Columns) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      {{-- Total --}}
      <div class="card p-6 border-b-4 border-b-blue-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl">
            <i class="fas fa-building"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-wider">Total</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">All Tenants</p>
        <p class="text-2xl font-black text-slate-800" id="statAllCompanies">0</p>
      </div>

      {{-- Active --}}
      <div class="card p-6 border-b-4 border-b-emerald-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl">
            <i class="fas fa-check-circle"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[9px] font-black uppercase tracking-wider">Operational</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active</p>
        <p class="text-2xl font-black text-slate-800" id="statActiveCompanies">0</p>
      </div>

      {{-- Suspended --}}
      <div class="card p-6 border-b-4 border-b-amber-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
            <i class="fas fa-pause-circle"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[9px] font-black uppercase tracking-wider">Pending</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Suspended</p>
        <p class="text-2xl font-black text-slate-800" id="statSuspendedCompanies">0</p>
      </div>

      {{-- Expired --}}
      <div class="card p-6 border-b-4 border-b-rose-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl">
            <i class="fas fa-calendar-x"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded text-[9px] font-black uppercase tracking-wider">Attention</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Expired</p>
        <p class="text-2xl font-black text-slate-800" id="statExpiredCompanies">0</p>
      </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-slate-100 bg-white shadow-soft mb-8">
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
            <label for="filterStatus" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status
              Filter</label>
            <select id="filterStatus" class="w-full">
              <option value="">All Statuses</option>
              @foreach ($statuses as $st)
                <option value="{{ $st->id }}">{{ $st->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="filter-actions">
          <button id="btnClearFilters" class="btn btn-white min-w-[120px]">Clear Filter</button>
          <button id="btnApplyFilters" class="btn btn-primary min-w-[123px]">Apply Filter</button>
        </div>
      </div>
    </div>

    {{-- Table Card --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0 overflow-x-auto">
        <table class="table-premium w-full text-left data-table">
          <thead>
            <tr>
              <th class="px-8! w-[80px]">Logo</th>
              <th>Company Details</th>
              <th>System Code</th>
              <th class="text-center">Active Users</th>
              <th class="text-center">Current Status</th>
              <th>Registration Date</th>
              <th class="text-right px-8!">Action</th>
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
      function getFilters() {
        return {
          company_status_id: $('#filterStatus').val()
        };
      }

      function loadStats() {
        $.get('{{ route('tenants.stats') }}', getFilters(), function(res) {
          $('#statAllCompanies').text(res.all_companies ?? 0);
          $('#statActiveCompanies').text(res.active_companies ?? 0);
          $('#statSuspendedCompanies').text(res.suspended_companies ?? 0);
          $('#statExpiredCompanies').text(res.expired_companies ?? 0);
        });
      }

      const table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
          [5, 'desc']
        ],
        ajax: {
          url: '{{ route('tenants.index') }}',
          data: function(d) {
            const f = getFilters();
            d.company_status_id = f.company_status_id;
          }
        },
        columns: [{
            data: 'logo',
            orderable: false,
            searchable: false,
            className: 'px-8 py-4',
            render: function(data) {
              // Return styled logo or placeholder
              return data; // The controller already sends an <img> tag, but let's ensure it has our classes
            }
          },
          {
            data: 'name',
            className: 'py-4',
            render: function(data, type, row) {
              return `
                  <div class="flex flex-col">
                    <span class="font-bold text-slate-800 text-sm">${data}</span>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">${row.legal_name || 'No Legal Name'}</span>
                  </div>
                `;
            }
          },
          {
            data: 'code',
            className: 'py-4',
            render: (data) =>
              `<span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-wider border border-slate-200">${data || '—'}</span>`
          },
          {
            data: 'users_count',
            className: 'text-center py-4',
            render: (data) => `<div class="flex items-center justify-center gap-1.5 py-1 px-3 bg-brand-50 border border-brand-100 rounded-lg w-fit mx-auto">
                <i class="fas fa-users text-brand-400 text-[10px]"></i>
                <span class="text-xs font-bold text-brand-600">${data}</span>
              </div>`
          },
          {
            data: 'company_status_badge',
            className: 'text-center py-4',
            name: 'companies.company_status_id'
          },
          {
            data: 'created_at',
            className: 'py-4 text-xs font-bold text-slate-500'
          },
          {
            data: 'action',
            orderable: false,
            searchable: false,
            className: 'text-right px-8 py-4'
          },
        ],
        dom: '<"flex justify-between items-center p-6"lBf>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Search tenants...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400 text-2xl"></i>'
        },
        drawCallback: function() {
          // Apply custom styling to the controller-generated logo if needed
          $('.company-logo').addClass('w-10 h-10 rounded-xl object-contain border border-slate-100 shadow-sm')
            .removeClass('img-sm');
        }
      });

      loadStats();

      $('#btnApplyFilters').on('click', function() {
        table.ajax.reload();
        loadStats();
      });

      $('#btnClearFilters').on('click', function() {
        $('#filterStatus').val('').trigger('change');
        table.ajax.reload();
        loadStats();
      });
    });
  </script>
@endpush
