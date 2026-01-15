@extends('catvara.layouts.app')

@section('title', 'Roles')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Role Directory</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Define and manage access roles for <b>{{ $company->name }}</b>.
        </p>
      </div>
      <div>
        <a href="{{ route('settings.roles.create', ['company' => $company->uuid]) }}" class="btn btn-primary">
          <i class="fas fa-plus-circle mr-2"></i> Create New Role
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
        <table class="table-premium w-full text-left" id="roles-table">
          <thead>
            <tr>
              <th class="px-8! w-[60px]">#</th>
              <th>Role Identification</th>
              <th>System Slug</th>
              <th class="text-center">Permissions</th>
              <th class="text-center">Status</th>
              <th>Created On</th>
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
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false,
            className: 'px-8 py-5'
          },
          {
            data: 'name',
            name: 'name',
            className: 'py-5',
            render: function(data) {
              return `<span class="font-bold text-slate-800 text-sm">${data}</span>`;
            }
          },
          {
            data: 'slug',
            name: 'slug',
            className: 'py-5',
            render: function(data) {
              return data ?
                `<code class="text-[10px] px-1.5 py-0.5 bg-slate-50 border border-slate-100 rounded text-slate-500 font-bold">${data}</code>` :
                '<span class="text-slate-300">—</span>';
            }
          },
          {
            data: 'permissions_count',
            name: 'permissions_count',
            className: 'text-center py-5',
            render: (data) =>
              `<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-500 border border-blue-100 rounded-md text-[10px] font-bold"><i class="fas fa-key text-[8px]"></i> ${data}</span>`
          },
          {
            data: 'is_active',
            name: 'is_active',
            className: 'text-center py-5'
          },
          {
            data: 'created_at',
            name: 'created_at',
            className: 'py-5 text-slate-500 font-medium text-xs'
          },
          {
            data: 'action',
            orderable: false,
            searchable: false,
            className: 'text-right px-8 py-5'
          },
        ],
        order: [
          [1, 'asc']
        ],
        dom: '<"flex justify-between items-center p-6"lBf>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Search roles...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400"></i>'
        }
      });

      // Filter Toggle
      $('.filter-toggle-btn').on('click', function() {
        $('.filter-card-content').slideToggle(300);
        $(this).find('.filter-toggle-icon').toggleClass('rotate-180');
      });

      $('#btnApply').on('click', () => table.ajax.reload());
      $('#btnClear').on('click', () => {
        $('#filterActive').val('');
        table.ajax.reload();
      });
    });
  </script>
@endpush
