@extends('catvara.layouts.app')

@section('title', 'Attributes')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Attributes</h1>
            <p class="text-slate-500 mt-1">Manage product attributes and their predefined values.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ company_route('catalog.attributes.create') }}"
                class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-brand-600 rounded-xl hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-lg shadow-brand-500/30 transition-all">
                <i class="fas fa-plus mr-2"></i> Add Attribute
            </a>
        </div>
    </div>

    <!-- Stats Row (Optional, for future) -->

    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
        <div class="p-6">
            <table class="w-full text-left" id="attributesTable">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Values</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Created</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- DataTables will populate this --}}
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#attributesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ company_route('catalog.attributes.index') }}",
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'values_badges', name: 'values_badges', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'is_active' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search attributes...",
                    processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
                drawCallback: function () {
                    // Re-init tooltips if needed
                    if ($.fn.tooltip) {
                        $('[data-toggle="tooltip"]').tooltip();
                    }
                }
            });
        });
    </script>
@endpush