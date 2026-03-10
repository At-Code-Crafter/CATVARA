@extends('catvara.layouts.app')

@section('title', 'Tax Groups')

@section('content')
    <div class="space-y-8 animate-fade-in pb-12">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Tax Groups</h1>
                <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
                    Manage tax groups for your company
                </p>
            </div>
            <div>
                <a href="{{ company_route('settings.tax-groups.create') }}"
                    class="btn btn-primary shadow-lg shadow-brand-500/30">
                    <i class="fas fa-plus-circle mr-2"></i> Create Tax Group
                </a>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card p-6 border-b-4 border-b-blue-400 hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <span
                        class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-wider">Total</span>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">All Groups</p>
                <p class="text-2xl font-black text-slate-800" id="statTotalGroups">0</p>
            </div>

            <div class="card p-6 border-b-4 border-b-emerald-400 hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div
                        class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <span
                        class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[9px] font-black uppercase tracking-wider">Active</span>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active Groups</p>
                <p class="text-2xl font-black text-slate-800" id="statActiveGroups">0</p>
            </div>

            <div class="card p-6 border-b-4 border-b-amber-400 hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <span
                        class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[9px] font-black uppercase tracking-wider">Inclusive</span>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tax Inclusive</p>
                <p class="text-2xl font-black text-slate-800" id="statInclusiveGroups">0</p>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="table-premium w-full text-left data-table">
                    <thead>
                        <tr>
                            <th class="px-8!">Code</th>
                            <th>Group Info</th>
                            <th class="text-center">Tax Mode</th>
                            <th class="text-center">Rates</th>
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
            function loadStats() {
                $.get('{{ company_route('settings.tax-groups.index') }}', {
                    stats: true
                }, function(data) {
                    const rows = data.data || [];
                    const total = rows.length;
                    const active = rows.filter(r => r.is_active).length;
                    const inclusive = rows.filter(r => r.is_tax_inclusive).length;

                    $('#statTotalGroups').text(total);
                    $('#statActiveGroups').text(active);
                    $('#statInclusiveGroups').text(inclusive);
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
                    url: '{{ company_route('settings.tax-groups.index') }}'
                },
                columns: [{
                        data: 'code_badge',
                        orderable: false,
                        searchable: false,
                        className: 'px-8 py-4'
                    },
                    {
                        data: 'group_info',
                        orderable: false,
                        searchable: false,
                        className: 'py-4'
                    },
                    {
                        data: 'tax_mode_badge',
                        orderable: false,
                        searchable: false,
                        className: 'text-center py-4'
                    },
                    {
                        data: 'rates_summary',
                        orderable: false,
                        searchable: false,
                        className: 'text-center py-4'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false,
                        className: 'text-center py-4'
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
                    searchPlaceholder: "Search groups...",
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
