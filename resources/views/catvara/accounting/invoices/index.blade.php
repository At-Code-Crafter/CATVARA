@extends('catvara.layouts.app')

@section('title', 'Invoices')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Invoices</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Manage and monitor customer billing documents.</p>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card border-none shadow-soft overflow-hidden bg-white">
      <div class="p-0">
        <table id="invoicesTable" class="table-premium w-full text-sm">
          <thead>
            <tr>
              <th class="px-6 py-4">Invoice #</th>
              <th class="px-6 py-4">Customer</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Total</th>
              <th class="px-6 py-4">Created</th>
              <th class="px-6 py-4 w-24">Actions</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      $(function() {
        $('#invoicesTable').DataTable({
          processing: true,
          serverSide: true,
          ajax: "{{ company_route('accounting.invoices.data') }}",
          columns: [{
              data: 'invoice_number',
              name: 'invoice_number'
            },
            {
              data: 'customer_name',
              name: 'customer.display_name'
            },
            {
              data: 'status',
              name: 'status.name',
              className: 'text-center'
            },
            {
              data: 'grand_total',
              name: 'grand_total',
              className: 'text-right'
            },
            {
              data: 'created_at',
              name: 'created_at'
            },
            {
              data: 'actions',
              name: 'actions',
              orderable: false,
              searchable: false,
              className: 'text-center'
            }
          ],
          language: {
            search: "",
            searchPlaceholder: "Search invoices...",
            processing: '<i class="fas fa-spinner fa-spin text-brand-500"></i>'
          },
          dom: '<"flex flex-col sm:flex-row items-center justify-between p-6 border-b border-slate-50"<"flex items-center"l><"flex items-center"f>>rt<"flex flex-col sm:flex-row items-center justify-between p-6 border-t border-slate-50"ip>'
        });
      });
    </script>
  @endpush
@endsection
