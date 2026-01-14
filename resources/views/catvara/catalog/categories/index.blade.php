@extends('catvara.layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Categories</h1>
            <p class="text-slate-500 mt-1">Manage your product categories and hierarchy.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ company_route('catalog.categories.create') }}"
                class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-brand-600 rounded-xl hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-lg shadow-brand-500/30 transition-all">
                <i class="fas fa-plus mr-2"></i> Add Category
            </a>
        </div>
    </div>

    <!-- Filter Card (Optional, for future) -->

    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
        <div class="p-6">
            <table class="w-full text-left" id="categoriesTable">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Slug</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Parent</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Children</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
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
            $('#categoriesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ company_route('catalog.categories.index') }}",
                columns: [
                    { data: 'name_html', name: 'name', orderable: false }, // HTML for indentation
                    { data: 'slug_html', name: 'slug' },
                    { data: 'parent_html', name: 'parent_name' },
                    { data: 'children_html', name: 'children_count', searchable: false },
                    { data: 'status_badge', name: 'is_active' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search categories...",
                    processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
                drawCallback: function () {
                    // Re-init any plugins if needed (tooltips)
                }
            });
        });
    </script>
@endpush