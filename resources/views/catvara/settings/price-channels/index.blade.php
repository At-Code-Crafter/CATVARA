@extends('catvara.layouts.app')

@section('title', 'Pricing Channels')

@section('content')
  <div class="space-y-8 animate-fade-in pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Pricing Channels</h1>
        <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
          Manage sales channels for pricing strategies
        </p>
      </div>
      <div>
        <a href="{{ route('price-channels.create') }}" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-plus-circle mr-2"></i> Create New Channel
        </a>
      </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      {{-- Total Channels --}}
      <div class="card p-6 border-b-4 border-b-purple-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center text-xl">
            <i class="fas fa-tags"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-wider">Total</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">All Channels</p>
        <p class="text-2xl font-black text-slate-800" id="statTotalChannels">0</p>
      </div>

      {{-- Active Channels --}}
      <div class="card p-6 border-b-4 border-b-emerald-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl">
            <i class="fas fa-check-circle"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[9px] font-black uppercase tracking-wider">Active</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active</p>
        <p class="text-2xl font-black text-slate-800" id="statActiveChannels">0</p>
      </div>

      {{-- Inactive Channels --}}
      <div class="card p-6 border-b-4 border-b-slate-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center text-xl">
            <i class="fas fa-ban"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-slate-50 text-slate-600 rounded text-[9px] font-black uppercase tracking-wider">Inactive</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Inactive</p>
        <p class="text-2xl font-black text-slate-800" id="statInactiveChannels">0</p>
      </div>
    </div>

    {{-- Table Card --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0 overflow-x-auto">
        <table class="table-premium w-full text-left data-table">
          <thead>
            <tr>
              <th class="px-8!">Channel Code</th>
              <th>Channel Name</th>
              <th class="text-center">Status</th>
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
      function loadStats() {
        $.get('{{ route('price-channels.index') }}', {
          stats: true
        }, function(data) {
          // Calculate stats from data
          const total = data.data ? data.data.length : 0;
          const active = data.data ? data.data.filter(c => c.is_active).length : 0;
          const inactive = total - active;

          $('#statTotalChannels').text(total);
          $('#statActiveChannels').text(active);
          $('#statInactiveChannels').text(inactive);
        });
      }

      const table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
          [0, 'asc']
        ],
        ajax: {
          url: '{{ route('price-channels.index') }}'
        },
        columns: [{
            data: 'code',
            className: 'px-8 py-4 font-bold text-slate-800',
            render: (data) =>
              `<span class="px-3 py-1.5 rounded-lg bg-purple-50 text-purple-600 text-xs font-black uppercase tracking-wider border border-purple-100">${data}</span>`
          },
          {
            data: 'name',
            className: 'py-4',
            render: (data) => `<span class="font-bold text-slate-800 text-sm">${data}</span>`
          },
          {
            data: 'status_badge',
            className: 'text-center py-4',
            orderable: false
          },
          {
            data: 'actions',
            orderable: false,
            searchable: false,
            className: 'text-right px-8 py-4'
          },
        ],
        dom: '<"flex justify-between items-center p-6"lBf>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Search channels...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400 text-2xl"></i>'
        },
        drawCallback: function() {
          loadStats();
        }
      });

      loadStats();
    });
  </script>
@endpush
