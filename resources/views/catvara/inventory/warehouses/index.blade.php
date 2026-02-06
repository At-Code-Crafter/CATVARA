@extends('catvara.layouts.app')

@section('title', 'Warehouses')

@section('content')
  <div class="w-full mx-auto animate-fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Inventory
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Warehouses</h1>
        <p class="text-slate-500 font-medium mt-1">Manage your storage facilities and distribution centers.</p>
      </div>
      <a href="{{ company_route('inventory.warehouses.create') }}" class="btn btn-primary shadow-lg shadow-brand-500/30">
        <i class="fas fa-plus-circle mr-2"></i> Add Warehouse
      </a>
    </div>

    {{-- Warehouses Table Card --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-6">
        <table id="warehousesTable" class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Warehouse</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Code</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Location</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Phone</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Stock</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
              <th class="text-right py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400 w-24">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {
      $('#warehousesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ company_route('inventory.warehouses.index') }}',
        columns: [
          { data: 'name_html', name: 'name', orderable: true, searchable: true },
          { data: 'code_html', name: 'code', orderable: true, searchable: true },
          { data: 'address_html', name: 'address', orderable: false, searchable: true },
          { data: 'phone_html', name: 'phone', orderable: false, searchable: true },
          { data: 'stock_html', name: 'stock', orderable: false, searchable: false },
          { data: 'status_html', name: 'is_active', orderable: true, searchable: false },
          { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right' }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
          search: "",
          searchPlaceholder: "Search warehouses...",
          lengthMenu: "Show _MENU_",
          info: "Showing _START_ to _END_ of _TOTAL_ warehouses",
          infoEmpty: "No warehouses found",
          infoFiltered: "(filtered from _MAX_ total)",
          emptyTable: '<div class="py-12 text-center"><div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4"><i class="fas fa-warehouse text-2xl text-amber-300"></i></div><p class="text-slate-500 font-medium">No warehouses yet</p><p class="text-slate-400 text-xs mt-1">Create your first warehouse to get started</p></div>',
          zeroRecords: '<div class="py-8 text-center"><i class="fas fa-search text-slate-300 text-2xl mb-2"></i><p class="text-slate-500 font-medium">No matching warehouses found</p></div>'
        },
        dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6"<"flex items-center gap-3"l><"flex-1"f>>rtip',
        drawCallback: function() {
          $('.dataTables_filter input').addClass('rounded-xl border-slate-200 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 shadow-sm text-sm py-2.5 px-4 min-w-[280px]');
          $('.dataTables_filter label').addClass('text-slate-600 font-medium');
          $('.dataTables_length select').addClass('rounded-lg border-slate-200 text-sm py-2 pr-8 focus:border-brand-400 focus:ring-brand-400');
          $('.dataTables_info').addClass('text-xs text-slate-500 font-medium pt-4');
          $('.dataTables_paginate').addClass('pt-4');
        }
      });
    });
  </script>
@endpush
