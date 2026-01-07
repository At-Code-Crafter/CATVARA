@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0">Payment Terms</h1>
      <small class="text-muted">Manage payment terms.</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('payment-terms.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Create Payment Term
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title">List of Payment Terms</h3>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-hover data-table w-100">
            <thead>
              <tr>
                <th>No</th>
                <th>Code</th>
                <th>Name</th>
                <th>Due Days</th>
                <th>Status</th>
                <th width="100px">Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: "{{ route('payment-terms.index') }}",
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'code',
            name: 'code'
          },
          {
            data: 'name',
            name: 'name'
          },
          {
            data: 'due_days',
            name: 'due_days'
          },
          {
            data: 'is_active',
            name: 'is_active'
          },
          {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
          },
        ]
      });
    });
  </script>
@endpush
