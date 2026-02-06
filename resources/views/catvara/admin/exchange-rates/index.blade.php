@extends('catvara.layouts.app')

@section('title', 'Exchange Rates')

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
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Exchange Rates</h1>
        <p class="text-slate-500 font-medium mt-1">View exchange rates across all companies.</p>
      </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
            <i class="fas fa-exchange-alt text-blue-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Pricing\ExchangeRate::count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Total Rates</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
            <i class="fas fa-building text-purple-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Pricing\ExchangeRate::distinct('company_id')->count('company_id') }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Companies</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <i class="fas fa-coins text-emerald-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Pricing\ExchangeRate::distinct('base_currency_id')->count('base_currency_id') }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Base Currencies</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <i class="fas fa-calendar text-amber-500 text-lg"></i>
          </div>
          <div>
            @php
              $latestRate = \App\Models\Pricing\ExchangeRate::latest('effective_date')->first();
            @endphp
            <p class="text-2xl font-bold text-slate-800">{{ $latestRate ? \Carbon\Carbon::parse($latestRate->effective_date)->format('M d') : '—' }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Latest Update</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Exchange Rates Table --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-6">
        <table id="exchangeRatesTable" class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Currency Pair</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Rate</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Company</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Effective Date</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Source</th>
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
        ajax: '{{ route('admin.exchange-rates.index') }}',
        columns: [
          { data: 'pair_html', name: 'base_curr.code', orderable: true, searchable: true },
          { data: 'rate_html', name: 'exchange_rates.rate', orderable: true, searchable: false },
          { data: 'company_html', name: 'companies.name', orderable: true, searchable: true },
          { data: 'effective_date_html', name: 'exchange_rates.effective_date', orderable: true, searchable: false },
          { data: 'source_html', name: 'exchange_rates.source', orderable: true, searchable: true }
        ],
        order: [[3, 'desc']],
        pageLength: 25,
        language: {
          search: "",
          searchPlaceholder: "Search exchange rates...",
          lengthMenu: "Show _MENU_",
          info: "Showing _START_ to _END_ of _TOTAL_ rates",
          infoEmpty: "No exchange rates found",
          infoFiltered: "(filtered from _MAX_ total)",
          emptyTable: '<div class="py-12 text-center"><div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4"><i class="fas fa-exchange-alt text-2xl text-blue-300"></i></div><p class="text-slate-500 font-medium">No exchange rates yet</p></div>',
          zeroRecords: '<div class="py-8 text-center"><i class="fas fa-search text-slate-300 text-2xl mb-2"></i><p class="text-slate-500 font-medium">No matching exchange rates found</p></div>'
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
