@extends('catvara.layouts.app')

@section('title', 'Delivery Services')

@section('content')
  <div class="space-y-8 animate-fade-in pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Delivery Services</h1>
        <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
          Manage couriers used on delivery notes
        </p>
      </div>
      <div>
        <a href="{{ company_route('settings.delivery-services.create') }}"
          class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-plus-circle mr-2"></i> Add Service
        </a>
      </div>
    </div>

    @if (session('success'))
      <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-700 text-sm font-bold">
        {{ session('success') }}
      </div>
    @endif
    @if (session('error'))
      <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl text-rose-700 text-sm font-bold">
        {{ session('error') }}
      </div>
    @endif

    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0 overflow-x-auto">
        <table class="table-premium w-full text-left data-table">
          <thead>
            <tr>
              <th class="px-8!">Name</th>
              <th>Code</th>
              <th>Sort</th>
              <th class="text-center">Status</th>
              <th class="text-right px-8!">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[2, 'asc']],
        ajax: {
          url: '{{ company_route('settings.delivery-services.index') }}'
        },
        columns: [
          { data: 'name', className: 'px-8 py-4 font-bold text-slate-800' },
          { data: 'code', className: 'py-4 text-slate-600', defaultContent: '—' },
          { data: 'sort_order', className: 'py-4 text-slate-600' },
          { data: 'status_badge', className: 'text-center py-4', orderable: false },
          { data: 'actions', orderable: false, searchable: false, className: 'text-right px-8 py-4' },
        ],
        dom: '<"flex justify-between items-center p-6"lBf>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Search services...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400 text-2xl"></i>'
        }
      });
    });
  </script>
@endpush
