@extends('catvara.layouts.app')

@section('title', 'Activity Logs')

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
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Activity Logs</h1>
        <p class="text-slate-500 font-medium mt-1">View system activity across all companies.</p>
      </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
            <i class="fas fa-history text-blue-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Common\ActivityLog::count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Total Logs</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <i class="fas fa-plus-circle text-emerald-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Common\ActivityLog::where('event', 'like', '%created%')->count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Created</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <i class="fas fa-edit text-amber-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Common\ActivityLog::where('event', 'like', '%updated%')->count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Updated</p>
          </div>
        </div>
      </div>
      <div class="card p-6 bg-white border-slate-100 shadow-soft hover:shadow-md transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
            <i class="fas fa-calendar-day text-purple-500 text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-800">{{ \App\Models\Common\ActivityLog::whereDate('created_at', today())->count() }}</p>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Today</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-white border-slate-100 shadow-soft mb-6">
      <div class="p-4 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500">Company:</label>
          <select id="filterCompany" class="rounded-lg border-slate-200 text-sm py-2 px-3 focus:border-brand-400 focus:ring-brand-400">
            <option value="">All Companies</option>
            @foreach($companies as $company)
              <option value="{{ $company->id }}">{{ $company->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500">Event:</label>
          <select id="filterEvent" class="rounded-lg border-slate-200 text-sm py-2 px-3 focus:border-brand-400 focus:ring-brand-400">
            <option value="">All Events</option>
            @foreach($events as $event)
              <option value="{{ $event }}">{{ ucwords(str_replace(['_', '.'], ' ', $event)) }}</option>
            @endforeach
          </select>
        </div>
        <button id="clearFilters" class="text-xs font-semibold text-slate-400 hover:text-brand-500 transition-colors">
          <i class="fas fa-times mr-1"></i> Clear Filters
        </button>
      </div>
    </div>

    {{-- Activity Logs Table --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-6">
        <table id="activityLogsTable" class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">User</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Event</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Subject</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Company</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Description</th>
              <th class="text-left py-3 px-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
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
      var table = $('#activityLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route('admin.activity-logs.index') }}',
          data: function(d) {
            d.company_id = $('#filterCompany').val();
            d.event = $('#filterEvent').val();
          }
        },
        columns: [
          { data: 'causer_html', name: 'activity_logs.causer_id', orderable: false, searchable: false },
          { data: 'event_html', name: 'activity_logs.event', orderable: true, searchable: true },
          { data: 'subject_html', name: 'activity_logs.subject_type', orderable: false, searchable: true },
          { data: 'company_html', name: 'companies.name', orderable: true, searchable: true },
          { data: 'description_html', name: 'activity_logs.description', orderable: false, searchable: true },
          { data: 'created_at_html', name: 'activity_logs.created_at', orderable: true, searchable: false }
        ],
        order: [[5, 'desc']],
        pageLength: 25,
        language: {
          search: "",
          searchPlaceholder: "Search logs...",
          lengthMenu: "Show _MENU_",
          info: "Showing _START_ to _END_ of _TOTAL_ logs",
          infoEmpty: "No activity logs found",
          infoFiltered: "(filtered from _MAX_ total)",
          emptyTable: '<div class="py-12 text-center"><div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4"><i class="fas fa-history text-2xl text-blue-300"></i></div><p class="text-slate-500 font-medium">No activity logs yet</p></div>',
          zeroRecords: '<div class="py-8 text-center"><i class="fas fa-search text-slate-300 text-2xl mb-2"></i><p class="text-slate-500 font-medium">No matching logs found</p></div>'
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

      // Filter handlers
      $('#filterCompany, #filterEvent').on('change', function() {
        table.ajax.reload();
      });

      $('#clearFilters').on('click', function() {
        $('#filterCompany, #filterEvent').val('');
        table.ajax.reload();
      });
    });
  </script>
@endpush
