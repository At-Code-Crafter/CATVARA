@extends('catvara.layouts.app')

@section('title', 'Categories')

@section('content')
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6 animate-fade-in">
    <div>
      <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Categories</h1>
      <p class="text-slate-400 mt-1 font-medium">Manage your product categories and hierarchy.</p>
    </div>
    <div class="mt-4 sm:mt-0">
      <a href="{{ company_route('catalog.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle mr-2"></i> Add Category
      </a>
    </div>
  </div>

  <!-- Filters placeholder or simple header if needed -->

  <!-- Filter Card (Optional, for future) -->

  <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
    <div class="p-0">
      <table class="table-premium w-full text-left" id="categoriesTable">
        <thead>
          <tr>
            <th class="px-8!">Name</th>
            <th>Slug</th>
            <th>Parent</th>
            <th>Children</th>
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
    $(document).ready(function() {
      $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ company_route('catalog.categories.index') }}",
        columns: [{
            data: 'name_html',
            name: 'name',
            orderable: false
          }, // HTML for indentation
          {
            data: 'slug_html',
            name: 'slug'
          },
          {
            data: 'parent_html',
            name: 'parent_name'
          },
          {
            data: 'children_html',
            name: 'children_count',
            searchable: false
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
            className: 'text-right'
          }
        ],
        language: {
          search: "",
          searchPlaceholder: "Search categories...",
          processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
        },
        dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
        drawCallback: function() {
          // Re-init any plugins if needed (tooltips)
        }
      });
    });
  </script>
@endpush
