@extends('catvara.layouts.app')

@section('title', 'Brands')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6 animate-fade-in">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Brands</h1>
            <p class="text-slate-400 mt-1 font-medium">Manage your product brands and hierarchy.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ company_route('catalog.brands.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i> Add Brand
            </a>
        </div>
    </div>

    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
        <div class="p-6">
            <table class="table-premium w-full text-left" id="brandsTable">
                <thead>
                    <tr>
                        <th class="px-8!">Logo</th>
                        <th>Name</th>
                        <th>Parent Brand</th>
                        <th>Status</th>
                        <th class="text-right px-8!">Actions</th>
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
            var table = $('#brandsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ company_route('catalog.brands.index') }}",
                },
                columns: [
                    {
                        data: 'logo_html',
                        name: 'logo',
                        orderable: false,
                        searchable: false,
                        className: 'px-8!'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'parent_name',
                        name: 'parent.name'
                    },
                    {
                        data: 'status_badge',
                        name: 'is_active'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-right px-8!'
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search brands...",
                    processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
            });
        });

        function confirmDelete(url) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.createElement('form');
                    form.action = url;
                    form.method = 'POST';
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@endpush