@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0">Inventory Transfers</h1>
    </div>
    <div class="col-sm-6">
      <div class="float-sm-right">
        <a href="{{ company_route('inventory.transfers.create') }}" class="btn btn-success">
          <i class="fas fa-plus"></i> New Transfer
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <table id="transfers-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Reference</th>
            <th>From</th>
            <th>To</th>
            <th>Items</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      $('#transfers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ company_route('inventory.transfers.index') }}',
        columns: [{
            data: 'transfer_no',
            name: 'transfer_no'
          },
          {
            data: 'from',
            name: 'from',
            orderable: false
          },
          {
            data: 'to',
            name: 'to',
            orderable: false
          },
          {
            data: 'items_count',
            name: 'items_count',
            orderable: false
          },
          {
            data: 'status_badge',
            name: 'status_badge',
            orderable: false
          },
          {
            data: 'created_at',
            name: 'created_at'
          },
          {
            data: 'actions',
            name: 'actions',
            orderable: false
          }
        ]
      });
    });
  </script>
@endpush
