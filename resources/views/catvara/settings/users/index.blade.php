@extends('catvara.layouts.app')

@section('title', 'Team Members')

@section('content')
  <div class="space-y-8 animate-fade-in pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Team Members</h1>
        <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
          Manage users in {{ $company->name }}
        </p>
      </div>
      <div>
        <a href="{{ route('settings.users.create', ['company' => $company->uuid]) }}" class="btn btn-primary">
          <i class="fas fa-plus mr-2"></i> Add Member
        </a>
      </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      {{-- Total Users --}}
      <div class="card p-6 border-b-4 border-b-blue-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl">
            <i class="fas fa-users"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-wider">Total</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">All Members</p>
        <p class="text-2xl font-black text-slate-800" id="statTotalUsers">0</p>
      </div>

      {{-- Active Users --}}
      <div class="card p-6 border-b-4 border-b-emerald-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl">
            <i class="fas fa-user-check"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[9px] font-black uppercase tracking-wider">Active</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active</p>
        <p class="text-2xl font-black text-slate-800" id="statActiveUsers">0</p>
      </div>

      {{-- Inactive Users --}}
      <div class="card p-6 border-b-4 border-b-slate-400 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start mb-4">
          <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center text-xl">
            <i class="fas fa-user-slash"></i>
          </div>
          <span
            class="px-2 py-0.5 bg-slate-50 text-slate-600 rounded text-[9px] font-black uppercase tracking-wider">Inactive</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Inactive</p>
        <p class="text-2xl font-black text-slate-800" id="statInactiveUsers">0</p>
      </div>
    </div>

    {{-- Table Card --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0 overflow-x-auto">
        <table class="table-premium w-full text-left data-table">
          <thead>
            <tr>
              <th class="px-8!">Photo</th>
              <th>Member Information</th>
              <th class="text-center">Roles</th>
              <th class="text-center">Status</th>
              <th class="text-center">Last Login</th>
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
        $.get('{{ company_route('settings.users.index') }}', {
          stats: true
        }, function(data) {
          const total = data.data ? data.data.length : 0;
          const active = data.data ? data.data.filter(u => u.is_active).length : 0;
          const inactive = total - active;

          $('#statTotalUsers').text(total);
          $('#statActiveUsers').text(active);
          $('#statInactiveUsers').text(inactive);
        });
      }

      const table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
          [1, 'asc']
        ],
        ajax: {
          url: '{{ route('settings.users.index', ['company' => $company->uuid]) }}'
        },
        columns: [{
            data: 'photo',
            className: 'px-8 py-4',
            orderable: false,
            searchable: false
          },
          {
            data: 'name',
            className: 'py-4',
            render: (data, type, row) => `<div class="flex flex-col">
              <span class="font-bold text-slate-800 text-sm">${data}</span>
              <span class="text-[10px] text-slate-400 font-bold">${row.email}</span>
            </div>`
          },
          {
            data: 'roles',
            className: 'text-center py-4',
            orderable: false,
            searchable: false
          },
          {
            data: 'is_active',
            className: 'text-center py-4',
            orderable: false
          },
          {
            data: 'last_login_at',
            className: 'text-center py-4'
          },
          {
            data: 'action',
            orderable: false,
            searchable: false,
            className: 'text-right px-8 py-4'
          },
        ],
        dom: '<"flex justify-between items-center p-6"lBf>rt<"flex justify-between items-center p-6"ip>',
        language: {
          searchPlaceholder: "Search users...",
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
