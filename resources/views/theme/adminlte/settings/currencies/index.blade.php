@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0"><i class="fas fa-coins mr-2 text-primary"></i>Currencies</h1>
      <small class="text-muted">Manage system currencies and exchange rates.</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('currencies.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus mr-1"></i> Add Currency
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
          <h3 class="card-title">List of Currencies</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <table class="table table-hover table-striped data-table w-100">
            <thead class="thead-light">
              <tr>
                <th>No</th>
                <th>Code</th>
                <th>Name</th>
                <th>Symbol</th>
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
        ajax: "{{ route('currencies.index') }}",
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'code',
            name: 'code',
            render: function(data) {
              return '<span class="font-weight-bold text-primary">' + data + '</span>';
            }
          },
          {
            data: 'name',
            name: 'name'
          },
          {
            data: 'symbol',
            name: 'symbol'
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
