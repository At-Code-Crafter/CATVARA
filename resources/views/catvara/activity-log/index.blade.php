@extends('catvara.layouts.app')

@section('title', 'Activity Log')

@section('content')
  <div class="space-y-8 animate-fade-in pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">System Activity Logs</h1>
        <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
          Enterprise Audit Trail for <span class="text-brand-500">{{ active_company()->name }}</span>
        </p>
      </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-slate-100 bg-white shadow-soft">
      <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/20">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
          <i class="fas fa-filter text-brand-400"></i> Audit Filters
        </h3>
        <button
          class="filter-toggle-btn h-8 w-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-all">
          <i class="fas fa-chevron-up filter-toggle-icon"></i>
        </button>
      </div>
      <div class="p-6 filter-card-content">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div class="space-y-1.5">
            <label for="filterEvent" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Event
              Type</label>
            <select id="filterEvent" class="w-full">
              <option value="">All Events</option>
              @foreach ($events as $event)
                <option value="{{ $event }}">{{ ucfirst(str_replace(['_', '.'], ' ', $event)) }}</option>
              @endforeach
            </select>
          </div>

          <div class="space-y-1.5 lg:col-span-2">
            <label for="filterDateRange" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Date
              Range</label>
            <div class="input-icon-group group">
              <i class="far fa-calendar-alt text-slate-400 group-focus-within:text-brand-400 transition-colors"></i>
              <input type="text" id="filterDateRange" class="w-full pl-10 h-[44px]" placeholder="Select period...">
            </div>
          </div>
        </div>
        <div class="filter-actions mt-6 flex justify-end gap-3">
          <button id="btnClearFilters" class="btn btn-white min-w-[120px]">Reset</button>
          <button id="btnApplyFilters" class="btn btn-primary min-w-[123px]">Filter Logs</button>
        </div>
      </div>
    </div>

    {{-- Table Card --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0 overflow-x-auto">
        <table class="table-premium w-full text-left" id="logs-table">
          <thead>
            <tr>
              <th class="px-8! w-[50px]">#</th>
              <th class="w-[200px]">Performed By</th>
              <th class="w-[180px]">Activity Event</th>
              <th>Resource Subject</th>
              <th class="text-center">Modifications</th>
              <th>Timestamp</th>
              <th class="text-right px-8!">Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Details Modal --}}
  <div id="detailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm modal-close-trigger"></div>
    <div
      class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white shadow-2xl animate-slide-in-right overflow-y-auto">
      <div
        class="p-8 border-b border-slate-50 flex items-center justify-between sticky top-0 bg-white/90 backdrop-blur z-10">
        <div>
          <h2 class="text-xl font-black text-slate-800 tracking-tight">Activity Details</h2>
          <p class="text-xs text-slate-400 font-bold uppercase tracking-widest" id="modalSubTitle"></p>
        </div>
        <button
          class="modal-close-trigger h-10 w-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="p-8 space-y-8">
        {{-- Info Grid --}}
        <div class="grid grid-cols-2 gap-8">
          <div>
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Causer</label>
            <div id="modalCauser" class="text-sm font-bold text-slate-700"></div>
          </div>
          <div>
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Timestamp</label>
            <div id="modalTime" class="text-sm font-bold text-slate-700"></div>
          </div>
        </div>

        <div>
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">Action
            Description</label>
          <div id="modalDescription"
            class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-sm font-medium text-slate-600 leading-relaxed italic">
          </div>
        </div>

        {{-- Changes Table --}}
        <div id="changesContainer">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">State Changes</label>
          <div class="border border-slate-100 rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-4 py-3 font-black text-slate-400 uppercase tracking-tighter w-1/4">Field</th>
                  <th class="px-4 py-3 font-black text-slate-400 uppercase tracking-tighter">Previous Value</th>
                  <th class="px-4 py-3 font-black text-slate-400 uppercase tracking-tighter">New Value</th>
                </tr>
              </thead>
              <tbody id="changesBody"></tbody>
            </table>
          </div>
        </div>

        <div id="noChanges"
          class="hidden py-12 text-center text-slate-400 font-bold text-sm bg-slate-50 rounded-3xl border border-dashed border-slate-200">
          <i class="fas fa-info-circle mb-2 text-xl opacity-20"></i><br>
          No attribute-level changes recorded for this entry.
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Date Range Picker
      const dateRangePicker = flatpickr("#filterDateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "M j, Y",
        onReady: (selectedDates, dateStr, instance) => {
          $(instance.altInput).addClass(
            'w-full pl-10 border-slate-200 rounded-xl text-sm font-semibold transition-all focus:border-brand-400 h-[44px]'
            );
        }
      });

      const table = $('#logs-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        ajax: {
          url: '{{ company_route('activity-logs.index') }}',
          data: function(d) {
            d.event = $('#filterEvent').val();
            const dateRange = $('#filterDateRange').val();
            if (dateRange && dateRange.includes(' to ')) {
              const dates = dateRange.split(' to ');
              d.date_from = dates[0];
              d.date_to = dates[1];
            }
          }
        },
        columns: [{
            data: 'DT_RowIndex',
            name: 'id',
            orderable: false,
            searchable: false,
            className: 'px-8 py-4 font-bold text-slate-400 text-xs'
          },
          {
            data: 'causer',
            name: 'causer_id',
            className: 'py-4'
          },
          {
            data: 'event',
            className: 'py-4'
          },
          {
            data: 'subject',
            name: 'subject_type',
            className: 'py-4'
          },
          {
            data: 'changes_count',
            orderable: false,
            className: 'text-center py-4'
          },
          {
            data: 'created_at',
            name: 'created_at',
            className: 'py-4'
          },
          {
            data: 'action',
            orderable: false,
            searchable: false,
            className: 'text-right px-8 py-4'
          }
        ],
        dom: '<"flex justify-between items-center p-6"lBf>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Quick search...",
          search: "",
          processing: '<i class="fas fa-spinner fa-spin text-brand-400 text-2xl"></i>'
        },
        order: [
          [5, 'desc']
        ]
      });

      // Show Details
      $(document).on('click', '.view-changes', function() {
        const id = $(this).data('id');
        $.get(`{{ company_route('activity-logs.index') }}/${id}`).done(function(res) {
          const log = res.log;
          $('#modalSubTitle').text(
            `${res.subject_type_simple} • ${log.event.replace(/_/g, ' ').toUpperCase()}`);
          $('#modalCauser').text(log.causer ? log.causer.name : 'System');
          $('#modalTime').text(new Date(log.created_at).toLocaleString());
          $('#modalDescription').text(log.description || 'No description available');

          const changesBody = $('#changesBody').empty();
          const changes = log.properties ? log.properties.changes : null;

          if (changes && Object.keys(changes).length > 0) {
            $('#changesContainer').show();
            $('#noChanges').hide();

            Object.keys(changes).map(field => {
              const change = changes[field];
              changesBody.append(`
                  <tr class="border-t border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="px-4 py-3 font-bold text-slate-500 uppercase tracking-tighter" style="font-size: 10px">${field.replace(/_/g, ' ')}</td>
                    <td class="px-4 py-3 text-slate-400 font-medium">${change.old === null || change.old === '' ? '<span class="italic opacity-50">Empty</span>' : e(change.old)}</td>
                    <td class="px-4 py-3 text-brand-600 font-bold">${change.new === null || change.new === '' ? '<span class="italic opacity-50">Empty</span>' : e(change.new)}</td>
                  </tr>
                `);
            });
          } else {
            $('#changesContainer').hide();
            $('#noChanges').show();
          }

          $('#detailsModal').removeClass('hidden').addClass('flex');
          $('body').css('overflow', 'hidden');
        });
      });

      function e(str) {
        if (typeof str !== 'string') return JSON.stringify(str);
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      }

      $('.modal-close-trigger').on('click', function() {
        $('#detailsModal').addClass('hidden');
        $('body').css('overflow', 'auto');
      });

      $('.filter-toggle-btn').on('click', function() {
        $('.filter-card-content').slideToggle(300);
        $('.filter-toggle-icon').toggleClass('fa-chevron-up fa-chevron-down');
      });

      $('#btnApplyFilters').on('click', function() {
        table.ajax.reload();
      });

      $('#btnClearFilters').on('click', function() {
        $('#filterEvent').val('');
        dateRangePicker.clear();
        table.ajax.reload();
      });
    });
  </script>
@endpush
