@extends('catvara.layouts.app')

@section('title', 'All Payment Terms')

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
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Payment Terms</h1>
        <p class="text-slate-500 font-medium mt-1">View all payment terms across all companies.</p>
      </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
            <i class="fas fa-calendar-alt text-teal-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Accounting\PaymentTerm::count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Total Terms</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Accounting\PaymentTerm::where('is_active', true)->count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Active</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
            <i class="fas fa-building text-purple-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Accounting\PaymentTerm::distinct('company_id')->count('company_id') }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Companies</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <i class="fas fa-clock text-amber-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Accounting\PaymentTerm::where('due_days', 0)->count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Due Immediately</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Payment Terms Table --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-6">
        <table id="paymentTermsTable" class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Term</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Company</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Due Days</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
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
      $('#paymentTermsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.payment-terms.index') }}',
        columns: [
          { data: 'name_html', name: 'payment_terms.name', orderable: true, searchable: true },
          { data: 'company_html', name: 'companies.name', orderable: true, searchable: true },
          { data: 'due_days_html', name: 'payment_terms.due_days', orderable: true, searchable: false },
          { data: 'status_html', name: 'payment_terms.is_active', orderable: true, searchable: false }
        ],
        order: [[1, 'asc'], [0, 'asc']],
        pageLength: 25,
        language: {
          search: "",
          searchPlaceholder: "Search payment terms...",
          lengthMenu: "Show _MENU_",
          info: "Showing _START_ to _END_ of _TOTAL_ terms",
          infoEmpty: "No payment terms found",
          infoFiltered: "(filtered from _MAX_ total)",
          emptyTable: '<div class="py-12 text-center"><div class="w-16 h-16 rounded-2xl bg-teal-50 flex items-center justify-center mx-auto mb-4"><i class="fas fa-calendar-alt text-2xl text-teal-300"></i></div><p class="text-slate-500 font-medium">No payment terms yet</p></div>',
          zeroRecords: '<div class="py-8 text-center"><i class="fas fa-search text-slate-300 text-2xl mb-2"></i><p class="text-slate-500 font-medium">No matching terms found</p></div>'
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
