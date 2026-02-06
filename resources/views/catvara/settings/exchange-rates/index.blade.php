@extends('catvara.layouts.app')

@section('title', 'Exchange Rates')

@section('content')
  <div class="w-full mx-auto animate-fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Settings
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Exchange Rates</h1>
        <p class="text-slate-500 font-medium mt-1">Manage currency conversion rates for your company.</p>
      </div>
      <a href="{{ company_route('settings.exchange-rates.create') }}" class="btn btn-primary shadow-lg shadow-brand-500/30">
        <i class="fas fa-plus-circle mr-2"></i> Add Rate
      </a>
    </div>

    {{-- Exchange Rates Table Card --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-6">
        <table id="exchangeRatesTable" class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Currency Pair</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Rate</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Effective Date</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Source</th>
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
      $('#exchangeRatesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ company_route('settings.exchange-rates.index') }}',
        columns: [
          { data: 'pair_html', name: 'base_currency_id', orderable: false, searchable: false },
          { data: 'rate_html', name: 'rate', orderable: true, searchable: false },
          { data: 'effective_date_html', name: 'effective_date', orderable: true, searchable: false },
          { data: 'source_html', name: 'source', orderable: false, searchable: true },
          { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right' }
        ],
        order: [[2, 'desc']],
        pageLength: 25,
        language: {
          search: "",
          searchPlaceholder: "Search rates...",
          lengthMenu: "Show _MENU_",
          info: "Showing _START_ to _END_ of _TOTAL_ rates",
          infoEmpty: "No exchange rates found",
          infoFiltered: "(filtered from _MAX_ total)",
          emptyTable: '<div class="py-12 text-center"><div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-4"><i class="fas fa-exchange-alt text-2xl text-emerald-300"></i></div><p class="text-slate-500 font-medium">No exchange rates yet</p><p class="text-slate-400 text-xs mt-1">Add your first currency conversion rate</p></div>',
          zeroRecords: '<div class="py-8 text-center"><i class="fas fa-search text-slate-300 text-2xl mb-2"></i><p class="text-slate-500 font-medium">No matching rates found</p></div>'
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
