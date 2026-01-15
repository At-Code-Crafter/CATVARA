@extends('catvara.layouts.app')

@section('page-title', 'Company Management')

@section('page-actions')
    <a href="{{ route('tenants.create') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-lg shadow-accent/20 text-sm font-semibold text-white bg-accent hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
        Create Company
    </a>
@endsection

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card p-4">
            <div class="flex items-center">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl mr-4 text-xl">
                    <i data-lucide="building"></i>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</h4>
                    <span id="statAllCompanies" class="text-2xl font-bold text-slate-800">0</span>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl mr-4 text-xl">
                    <i data-lucide="check-circle-2"></i>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active</h4>
                    <span id="statActiveCompanies" class="text-2xl font-bold text-slate-800">0</span>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl mr-4 text-xl">
                    <i data-lucide="pause-circle"></i>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Suspended</h4>
                    <span id="statSuspendedCompanies" class="text-2xl font-bold text-slate-800">0</span>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center">
                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl mr-4 text-xl">
                    <i data-lucide="calendar-x"></i>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Expired</h4>
                    <span id="statExpiredCompanies" class="text-2xl font-bold text-slate-800">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-8">
        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 flex items-center">
                <i data-lucide="filter" class="w-4 h-4 mr-2 text-slate-400"></i>
                Filter Tenants
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Status</label>
                    <select id="filterStatus" class="select2">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex space-x-3">
                    <button type="button" id="btnApplyFilters" class="flex-1 px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-semibold hover:bg-slate-900 transition-all">
                        Apply
                    </button>
                    <button type="button" id="btnClearFilters" class="flex-1 px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-all">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card">
        <div class="p-6 border-b border-slate-50">
            <h3 class="font-bold text-slate-800 flex items-center">
                <i data-lucide="list" class="w-4 h-4 mr-2 text-slate-400"></i>
                Tenants Overview
            </h3>
        </div>
        <div class="p-0 overflow-x-auto">
            <table class="w-full text-left border-collapse data-table">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Logo</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Legal Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Users</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('head')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin: 0 !important;
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem;
            color: #64748b;
            padding-top: 1.5rem !important;
            padding-left: 1.5rem !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 1.5rem !important;
            padding-right: 1.5rem !important;
        }
        .dataTables_wrapper .dataTables_length {
             padding-top: 1.5rem !important;
             padding-left: 1.5rem !important;
             font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_filter {
             padding-top: 1.5rem !important;
             padding-right: 1.5rem !important;
             font-size: 0.875rem;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #f1f5f9 !important;
        }
        table.dataTable td {
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .company-logo {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
            padding: 2px;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function() {
            function getFilters() {
                return {
                    company_status_id: $('#filterStatus').val()
                };
            }

            function loadStats() {
                $.get('{{ route('tenants.stats') }}', getFilters(), function(res) {
                    $('#statAllCompanies').text(res.all_companies ?? 0);
                    $('#statActiveCompanies').text(res.active_companies ?? 0);
                    $('#statSuspendedCompanies').text(res.suspended_companies ?? 0);
                    $('#statExpiredCompanies').text(res.expired_companies ?? 0);
                });
            }

            const table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [[6, 'desc']],
                dom: '<"flex flex-col md:flex-row justify-between"lf>rt<"flex flex-col md:flex-row justify-between"ip>',
                ajax: {
                    url: '{{ route('tenants.index') }}',
                    data: function(d) {
                        const f = getFilters();
                        d.company_status_id = f.company_status_id;
                    }
                },
                columns: [
                    { data: 'logo', name: 'companies.logo', orderable: false, searchable: false, className: 'px-6 py-4' },
                    { data: 'name', name: 'companies.name', className: 'px-6 py-4 font-semibold text-slate-700 text-sm' },
                    { data: 'legal_name', name: 'companies.legal_name', className: 'px-6 py-4 text-slate-500 text-sm' },
                    { data: 'code', name: 'companies.code', className: 'px-6 py-4' },
                    { data: 'company_status_badge', name: 'companies.company_status_id', className: 'px-6 py-4' },
                    { data: 'users_count', name: 'users_count', searchable: false, className: 'px-6 py-4 text-right' },
                    { data: 'created_at', name: 'companies.created_at', className: 'px-6 py-4 text-slate-400 text-xs' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' },
                ],
                drawCallback: function() {
                    lucide.createIcons();
                    // Re-style badges for Tailwind in the JS if needed, but the controller already sends HTML.
                    // We'll trust the controller's badges for now, but CATVARA should eventually send Tailwind-ready badges.
                }
            });

            loadStats();

            $('#btnApplyFilters').on('click', function() {
                table.ajax.reload();
                loadStats();
            });

            $('#btnClearFilters').on('click', function() {
                $('#filterStatus').val('').trigger('change');
                table.ajax.reload();
                loadStats();
            });
        });
    </script>
@endpush
