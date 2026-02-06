@extends('catvara.layouts.app')

@section('title', 'Currencies')

@section('content')
  <div class="w-full mx-auto animate-fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Administration
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Currencies</h1>
        <p class="text-slate-500 font-medium mt-1">Manage system currencies and exchange settings.</p>
      </div>
      <a href="{{ route('currencies.create') }}" class="btn btn-primary shadow-lg shadow-brand-500/30">
        <i class="fas fa-plus-circle mr-2"></i> Add Currency
      </a>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <i class="fas fa-coins text-amber-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Pricing\Currency::count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Total Currencies</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Pricing\Currency::where('is_active', true)->count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Active</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
            <i class="fas fa-ban text-slate-400 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Pricing\Currency::where('is_active', false)->count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Inactive</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Currencies Table --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-6">
        <table id="currenciesTable" class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Currency</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Code</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Symbol</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Decimals</th>
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
      $('#currenciesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('currencies.index') }}',
        columns: [
          { data: 'name_html', name: 'name', orderable: true, searchable: true },
          { data: 'code_html', name: 'code', orderable: true, searchable: true },
          { data: 'symbol_html', name: 'symbol', orderable: false, searchable: false },
          { data: 'decimals_html', name: 'decimal_places', orderable: true, searchable: false },
          { data: 'status_html', name: 'is_active', orderable: true, searchable: false },
          { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right' }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
          search: "",
          searchPlaceholder: "Search currencies...",
          lengthMenu: "Show _MENU_",
          info: "Showing _START_ to _END_ of _TOTAL_ currencies",
          infoEmpty: "No currencies found",
          infoFiltered: "(filtered from _MAX_ total)",
          emptyTable: '<div class="py-12 text-center"><div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4"><i class="fas fa-coins text-2xl text-amber-300"></i></div><p class="text-slate-500 font-medium">No currencies yet</p></div>',
          zeroRecords: '<div class="py-8 text-center"><i class="fas fa-search text-slate-300 text-2xl mb-2"></i><p class="text-slate-500 font-medium">No matching currencies found</p></div>'
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
