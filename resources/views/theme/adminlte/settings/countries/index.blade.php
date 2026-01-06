@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0">Countries</h1>
      <small class="text-muted">Manage countries and their settings.</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('countries.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Add Country
      </a>
    </div>
  </div>
@endsection

@section('content')
  {{-- TOP STATS --}}
  <div class="row">
    <div class="col-lg-3 col-md-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3 id="statTotalCountries">0</h3>
          <p>Total Countries</p>
        </div>
        <div class="icon"><i class="fas fa-globe"></i></div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3 id="statActiveCountries">0</h3>
          <p>Active Countries</p>
        </div>
        <div class="icon"><i class="fas fa-check-circle"></i></div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3 id="statInactiveCountries">0</h3>
          <p>Inactive Countries</p>
        </div>
        <div class="icon"><i class="fas fa-ban"></i></div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="small-box bg-secondary">
        <div class="inner">
          <h3 id="statTotalStates">0</h3>
          <p>Total States</p>
        </div>
        <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
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
        <div class="col-md-3">
          <label class="mb-1">Status</label>
          <select id="filterStatus" class="form-control">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="mb-1">Region</label>
          <select id="filterRegion" class="form-control">
            <option value="">All Regions</option>
            @foreach ($regions as $region)
              <option value="{{ $region }}">{{ $region }}</option>
            @endforeach
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
        <i class="fas fa-list mr-1"></i> Country List
      </h3>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover data-table w-100">
          <thead class="thead-light">
            <tr>
              <th>Name</th>
              <th>ISO Code (2)</th>
              <th>ISO Code (3)</th>
              <th>Phone Code</th>
              <th>Capital</th>
              <th>Region</th>
              <th>States</th>
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
          is_active: $('#filterStatus').val(),
          region: $('#filterRegion').val()
        };
      }

      function loadStats() {
        $.get('{{ route('countries.stats') }}', getFilters(), function(res) {
          $('#statTotalCountries').text(res.total_countries ?? 0);
          $('#statActiveCountries').text(res.active_countries ?? 0);
          $('#statInactiveCountries').text(res.inactive_countries ?? 0);
          $('#statTotalStates').text(res.total_states ?? 0);
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
          url: '{{ route('countries.index') }}',
          data: function(d) {
            const f = getFilters();
            d.is_active = f.is_active;
            d.region = f.region;
          }
        },
        columns: [
          { data: 'name', name: 'name' },
          { data: 'iso_code_2', name: 'iso_code_2' },
          { data: 'iso_code_3', name: 'iso_code_3' },
          { data: 'phone_code', name: 'phone_code' },
          { data: 'capital', name: 'capital' },
          { data: 'region', name: 'region' },
          { data: 'states_count', name: 'states_count', searchable: false },
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
        $('#filterStatus').val('');
        $('#filterRegion').val('');
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
