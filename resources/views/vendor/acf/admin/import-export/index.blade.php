@extends('acf::admin.layout')

@section('title', 'Import / Export')

@section('content')
  <div class="row">
    <div class="col-md-6">
      <div class="acf-card bg-white p-4">
        <h5 class="fw-bold mb-3">Export Definitions</h5>
        <p class="text-muted">Save your field groups and fields to a JSON file. This is recommended before running
          <code>migrate:fresh</code>.</p>
        <div class="alert alert-info py-2">
          <i class="bi bi-info-circle me-2"></i> Current Path: <code>{{ $exportPath }}</code>
        </div>

        @if ($mismatch)
          <div class="alert alert-warning py-2">
            <i class="bi bi-exclamation-triangle me-2"></i> Unexported changes detected in database!
          </div>
        @endif

        <form action="{{ route('acf.admin.export') }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-primary w-100">Export to JSON</button>
        </form>
      </div>
    </div>

    <div class="col-md-6">
      <div class="acf-card bg-white p-4">
        <h5 class="fw-bold mb-3">Import Definitions</h5>
        <p class="text-muted">Restore your field groups and fields from a JSON file.</p>

        @if ($fileExists)
          <div class="alert alert-success py-2">
            <i class="bi bi-file-earmark-check me-2"></i> Definitions file found.
          </div>
          <form action="{{ route('acf.admin.import') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary w-100">Import from JSON</button>
          </form>
        @else
          <div class="alert alert-danger py-2">
            <i class="bi bi-file-earmark-x me-2"></i> Definitions file not found.
          </div>
          <button class="btn btn-outline-secondary w-100" disabled>Import Disabled</button>
        @endif
      </div>
    </div>
  </div>
@endsection
