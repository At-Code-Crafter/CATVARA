@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0">States / Provinces</h1>
      <small class="text-muted">Manage states and provinces.</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('states.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Add State
      </a>
    </div>
  </div>
@endsection

@section('content')
  {{-- TOP STATS --}}
  <div class="row">
    <div class="col-lg-4 col-md-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3 id="statTotalStates">0</h3>
          <p>Total States</p>
        </div>
        <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
      </div>
    </div>

    <div class="col-lg-4 col-md-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3 id="statActiveStates">0</h3>
          <p>Active States</p>
        </div>
        <div class="icon"><i class="fas fa-check-circle"></i></div>
      </div>
    </div>

    <div class="col-lg-4 col-md-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3 id="statInactiveStates">0</h3>
          <p>Inactive States</p>
        </div>
        <div class="icon"><i class="fas fa-ban"></i></div>
      </div>
    </div>
  </div>

  {{-- FILTERS CARD --}}
  <div class="card card-outline card-secondary">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-filter mr-1"></i> Filters
      </h3>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="col-md-4">
          <label class="mb-1">Country</label>
          <select id="filterCountry" class="form-control">
            <option value="">All Countries</option>
            @foreach ($countries as $country)
              <option value="{{ $country->id }}">{{ $country->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="mb-1">Status</label>
          <select id="filterStatus" class="form-control">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <div class="row">
        <div class="col-12 d-flex justify-content-start" style="gap:10px;">
          <button type="button" id="btnApplyFilters" class="btn btn-primary">
            <i class="fas fa-search mr-1"></i> Apply
          </button>
          <button type="button" id="btnClearFilters" class="btn btn-outline-secondary">
            <i class="fas fa-times mr-1"></i> Clear Filters
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- TABLE CARD --}}
  <div class="card card-outline card-secondary">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-list mr-1"></i> State List
      </h3>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover data-table w-100">
          <thead class="thead-light">
            <tr>
              <th>Name</th>
              <th>Code</th>
              <th>Country</th>
              <th>Type</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      function getFilters() {
        return {
          country_id: $('#filterCountry').val(),
          is_active: $('#filterStatus').val()
        };
      }

      function loadStats() {
        $.get('{{ route('states.stats') }}', getFilters(), function(res) {
          $('#statTotalStates').text(res.total_states ?? 0);
          $('#statActiveStates').text(res.active_states ?? 0);
          $('#statInactiveStates').text(res.inactive_states ?? 0);
        });
      }

      const table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[0, 'asc']],
        ajax: {
          url: '{{ route('states.index') }}',
          data: function(d) {
            const f = getFilters();
            d.country_id = f.country_id;
            d.is_active = f.is_active;
          }
        },
        columns: [
          { data: 'name', name: 'name' },
          { data: 'code', name: 'code' },
          { data: 'country_name', name: 'country.name' },
          { data: 'type', name: 'type' },
          { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
          { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
      });

      loadStats();

      $('#btnApplyFilters').on('click', function() {
        table.ajax.reload();
        loadStats();
      });

      $('#btnClearFilters').on('click', function() {
        $('#filterCountry').val('');
        $('#filterStatus').val('');
        table.ajax.reload();
        loadStats();
      });

      // Delete confirmation
      $(document).on('click', '.btn-delete', function() {
        const url = $(this).data('url');

        Swal.fire({
          title: 'Are you sure?',
          text: "This action cannot be undone!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: url,
              type: 'DELETE',
              headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
              success: function(response) {
                if (response.success) {
                  Swal.fire('Deleted!', response.message, 'success');
                  table.ajax.reload();
                  loadStats();
                } else {
                  Swal.fire('Error!', response.message, 'error');
                }
              },
              error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'An error occurred.';
                Swal.fire('Error!', msg, 'error');
              }
            });
          }
        });
      });
    });
  </script>
@endpush
