@extends('acf::admin.layout')

@section('title', 'Health Check')

@section('content')
  <div class="acf-card bg-white p-4">
    <h5 class="fw-bold mb-4">System Health</h5>

    <div class="list-group list-group-flush">
      <div class="list-group-item d-flex justify-content-between align-items-center px-0">
        <div>
          <h6 class="mb-0 fw-bold">Orphan Values</h6>
          <p class="text-muted small mb-0">Values associated with fields that no longer exist.</p>
        </div>
        @if ($orphans > 0)
          <span class="badge bg-danger rounded-pill">{{ $orphans }} found</span>
        @else
          <span class="badge bg-success rounded-pill">0 found</span>
        @endif
      </div>

      <div class="list-group-item d-flex justify-content-between align-items-center px-0">
        <div>
          <h6 class="mb-0 fw-bold">Database Connectivity</h6>
          <p class="text-muted small mb-0">Status of the ACF database tables.</p>
        </div>
        <span class="badge bg-success rounded-pill">Healthy</span>
      </div>
    </div>

    @if ($orphans > 0)
      <div class="mt-4">
        <button class="btn btn-sm btn-outline-danger">Cleanup Orphan Values</button>
      </div>
    @endif
  </div>
@endsection
