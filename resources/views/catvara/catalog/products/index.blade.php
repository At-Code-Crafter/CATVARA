@extends('catvara.layouts.app')

@section('title', 'Products')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Products</h1>
            <p class="text-slate-500 mt-1">Manage your complete product catalog and variants.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ company_route('catalog.products.create') }}"
                class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-brand-600 rounded-xl hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-lg shadow-brand-500/30 transition-all">
                <i class="fas fa-plus mr-2"></i> Add Product
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="filter_category"
                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category</label>
                <select id="filter_category" class="select2 w-full">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_status"
                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                <select id="filter_status" class="select2 w-full">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div>
                <label for="filter_stock" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Stock
                    Level</label>
                <select id="filter_stock" class="select2 w-full">
                    <option value="">All Stock</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-3 mt-6 pt-6 border-t border-slate-50">
            <button id="btn_apply_filters"
                class="w-32 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-md transition-all flex items-center justify-center">
                <i class="fas fa-filter mr-2 text-xs"></i> Filter
            </button>
            <button id="btn_reset_filters"
                class="w-32 py-2.5 text-sm font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all flex items-center justify-center">
                <i class="fas fa-undo mr-2 text-xs"></i> Reset
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
        <div class="p-6">
            <table class="w-full text-left" id="productsTable">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Product</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Category</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Variants</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- DataTables handled --}}
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const table = $('#productsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ company_route('catalog.products.index') }}",
                    data: function (d) {
                        d.category_id = $('#filter_category').val();
                        d.status = $('#filter_status').val();
                        d.stock_level = $('#filter_stock').val();
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'variants_count', name: 'variants_count', searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search products...",
                    processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
            });

            $('#btn_apply_filters').on('click', function () {
                table.ajax.reload();
            });

            $('#btn_reset_filters').on('click', function () {
                $('#filter_category, #filter_status, #filter_stock').val('').trigger('change');
                table.ajax.reload();
            });
        });
    </script>
@endpush