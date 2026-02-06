@extends('catvara.layouts.app')

@section('title', 'Permissions')

@push('styles')
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" rel="stylesheet">
@endpush

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <span class="text-[10px] font-black px-2 py-0.5 rounded bg-orange-100 text-orange-500 uppercase tracking-widest mb-2 inline-block">
          Administration
        </span>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Permissions</h1>
        <p class="text-slate-500 font-medium mt-1">Manage system permissions and access controls.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('permissions.create') }}" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-plus mr-2"></i> Add Permission
        </a>
      </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all cursor-pointer">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
            <i class="fas fa-key text-orange-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Auth\Permission::count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Total Permissions</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all cursor-pointer">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
            <i class="fas fa-cubes text-purple-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Auth\Module::count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Modules</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all cursor-pointer">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Auth\Permission::where('is_active', true)->count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Active Permissions</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Permissions Table --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      {{-- Table Header --}}
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
            <i class="fas fa-key text-sm"></i>
          </div>
          <h3 class="font-bold text-slate-700 text-sm uppercase tracking-wider">Permissions List</h3>
        </div>
      </div>

      {{-- Table --}}
      <div class="overflow-x-auto">
        <table id="permissionsTable" class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50/80 text-left">
              <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">Permission</th>
              <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">Module</th>
              <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
              <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            {{-- DataTables will populate --}}
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#permissionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('permissions.index') }}",
        columns: [
          { data: 'name_display', name: 'permissions.name' },
          { data: 'module_display', name: 'modules.name', searchable: false },
          { data: 'status', name: 'is_active', className: 'text-center', orderable: false, searchable: false },
          { data: 'actions', name: 'actions', className: 'text-right', orderable: false, searchable: false }
        ],
        pageLength: 25,
        language: {
          search: "",
          searchPlaceholder: "Search permissions...",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ permissions",
          paginate: {
            first: '<i class="fas fa-angle-double-left"></i>',
            last: '<i class="fas fa-angle-double-right"></i>',
            next: '<i class="fas fa-angle-right"></i>',
            previous: '<i class="fas fa-angle-left"></i>'
          }
        },
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6 border-b border-slate-100"lf>rtip',
        drawCallback: function() {
          // Style the search input
          $('.dataTables_filter input').addClass('rounded-xl border-slate-200 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 shadow-sm text-sm py-2 px-4 w-64');
          // Style the length select
          $('.dataTables_length select').addClass('rounded-xl border-slate-200 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 shadow-sm text-sm py-2 px-3');
          // Style pagination
          $('.dataTables_paginate').addClass('flex items-center gap-1 p-4');
          $('.dataTables_paginate a').addClass('px-3 py-2 rounded-lg text-sm font-medium transition-colors');
          $('.dataTables_paginate a.current').addClass('bg-brand-500 text-white');
          // Style info
          $('.dataTables_info').addClass('text-sm text-slate-500 p-4');
        }
      });
    });
  </script>
@endpush
