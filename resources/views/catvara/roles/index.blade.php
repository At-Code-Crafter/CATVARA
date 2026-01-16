@extends('catvara.layouts.app')

@section('title', 'Role Management')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Access Roles</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Define and manage permission sets for
          <b>{{ $company->name }}</b>.
        </p>
      </div>
      <div>
        <a href="{{ route('settings.roles.create', ['company' => $company->uuid]) }}"
          class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-plus-circle mr-2"></i> Create New Role
        </a>
      </div>
    </div>

    <!-- Filters Card -->
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
            <label for="filterActive" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status
              Filter</label>
            <select id="filterActive" class="w-full">
              <option value="">All Statuses</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="filter-actions mt-6">
          <button id="btnClearFilters" class="btn btn-white min-w-[120px]">Clear Filter</button>
          <button id="btnApplyFilters" class="btn btn-primary min-w-[123px]">Apply Filter</button>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0">
        <table class="table-premium w-full text-left" id="roles-table">
          <thead>
            <tr>
              <th class="px-8! w-[80px]">#</th>
              <th>Role Name</th>
              <th>Slug</th>
              <th class="text-center">Permissions</th>
              <th class="text-center">Status</th>
              <th>Created</th>
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
      const table = $('#roles-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
          url: '{{ route('settings.roles.index', ['company' => $company->uuid]) }}',
          data: function(d) {
            d.is_active = $('#filterActive').val();
          }
        },
        columns: [{
            data: 'DT_RowIndex',
            orderable: false,
            searchable: false,
            className: 'px-8 py-4 font-bold text-slate-400 text-xs'
          },
          {
            data: 'name',
            className: 'py-4 font-bold text-slate-800'
          },
          {
            data: 'slug',
            className: 'py-4',
            render: (data) =>
              `<span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-wider border border-slate-200">${data}</span>`
          },
          {
            data: 'permissions_count',
            className: 'text-center py-4',
            render: (data) => `<div class="flex items-center justify-center gap-1.5 py-1 px-3 bg-indigo-50 border border-indigo-100 rounded-lg w-fit mx-auto">
                <i class="fas fa-key text-indigo-400 text-[10px]"></i>
                <span class="text-xs font-bold text-indigo-600">${data}</span>
              </div>`
          },
          {
            data: 'is_active',
            className: 'text-center py-4'
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
          searchPlaceholder: "Search roles...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400 text-2xl"></i>'
        },
        drawCallback: function() {
          // Apply Tailwind to dynamic elements if needed
        }
      });

      // Filter Toggle
      $('.filter-toggle-btn').on('click', function() {
        const $cardContent = $('.filter-card-content');
        const $icon = $('.filter-toggle-icon');

        $cardContent.slideToggle(300);
        $icon.toggleClass('fa-chevron-up fa-chevron-down');
      });

      $('#btnApplyFilters').on('click', () => table.ajax.reload());
      $('#btnClearFilters').on('click', () => {
        $('#filterActive').val('');
        table.ajax.reload();
      });
    });
  </script>
@endpush
