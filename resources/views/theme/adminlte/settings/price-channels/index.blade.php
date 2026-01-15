@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0"><i class="fas fa-tags mr-2 text-primary"></i>Price Channels</h1>
      <small class="text-muted">Manage pricing channels (POS, Website, B2B, etc.)</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      @can('create', 'price-channels')
        <a href="{{ route('price-channels.create') }}" class="btn btn-primary shadow-sm">
          <i class="fas fa-plus mr-1"></i> Add Price Channel
        </a>
      @endcan
    </div>
  </div>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
          <h3 class="card-title">List of Price Channels</h3>
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
                <th>Status</th>
                <th width="120px">Action</th>
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
        ajax: "{{ route('price-channels.index') }}",
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
            data: 'status_badge',
            name: 'is_active',
            orderable: false,
            searchable: false
          },
          {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false
          }
        ]
      });
    });
  </script>
@endpush
