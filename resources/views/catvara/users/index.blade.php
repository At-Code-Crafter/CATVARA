@extends('catvara.layouts.app')

@section('title', 'User Management')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">User Directory</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Manage administrative users and permissions across companies.
        </p>
      </div>
      <div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
          <i class="fas fa-user-plus mr-2"></i> Add New User
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="space-y-1.5">
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">User Type</label>
            <select id="filterType" class="w-full">
              <option value="">All Types</option>
              <option value="ADMIN">ADMIN</option>
              <option value="SUPER_ADMIN">SUPER_ADMIN</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status</label>
            <select id="filterActive" class="w-full">
              <option value="">All Statuses</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="filter-actions mt-6 flex justify-end gap-3">
          <button id="btnClear" class="btn btn-white min-w-[120px]">Clear Filter</button>
          <button id="btnApply" class="btn btn-primary min-w-[123px]">Apply Filter</button>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0">
        <table class="table-premium w-full text-left" id="users-table">
          <thead>
            <tr>
              <th class="px-8! w-[80px]">Photo</th>
              <th>User Information</th>
              <th>System Type</th>
              <th>Companies</th>
              <th class="text-center">Status</th>
              <th>Last Login</th>
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
      const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
          url: '{{ route('users.index') }}',
          data: function(d) {
            d.is_active = $('#filterActive').val();
            d.user_type = $('#filterType').val();
          }
        },
        columns: [{
            data: 'photo',
            orderable: false,
            searchable: false,
            className: 'px-8 py-4'
          },
          {
            data: 'name',
            className: 'py-4',
            render: function(data, type, row) {
              return `
                <div class="flex flex-col">
                  <span class="font-bold text-slate-800 text-sm">${data}</span>
                  <span class="text-xs text-slate-400 font-medium">${row.email}</span>
                </div>
              `;
            }
          },
          {
            data: 'user_type',
            name: 'user_type',
            className: 'py-4'
          },
          {
            data: 'companies_count',
            name: 'companies_count',
            searchable: false,
            className: 'py-4',
            render: (data) =>
              `<div class="flex items-center gap-1.5 py-1 px-3 bg-slate-50 border border-slate-100 rounded-lg w-fit">
                <i class="fas fa-building text-slate-400 text-[10px]"></i>
                <span class="text-xs font-bold text-slate-600">${data}</span>
              </div>`
          },
          {
            data: 'is_active',
            name: 'is_active',
            className: 'text-center py-4'
          },
          {
            data: 'last_login_at',
            name: 'last_login_at',
            className: 'py-4'
          },
          {
            data: 'action',
            orderable: false,
            searchable: false,
            className: 'text-right px-8 py-4'
          },
        ],
        order: [
          [1, 'asc']
        ],
        dom: '<"flex justify-between items-center p-6"lBf>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Quick search...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400"></i>'
        },
        drawCallback: function() {
          // Any post-draw initialization if needed
        }
      });

      // Filter Toggle
      $('.filter-toggle-btn').on('click', function() {
        $('.filter-card-content').slideToggle(300);
        $(this).find('.filter-toggle-icon').toggleClass('rotate-180');
      });

      $('#btnApply').on('click', () => table.ajax.reload());
      $('#btnClear').on('click', () => {
        $('#filterActive, #filterType').val('').trigger('change');
        table.ajax.reload();
      });
    });
  </script>
@endpush
