@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0">Edit Payment Term</h1>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ company_route('settings.payment-terms.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Edit Payment Term: {{ $payment_term->name }}</h3>
        </div>
        <form action="{{ company_route('settings.payment-terms.update', ['payment_term' => $payment_term->id]) }}"
          method="POST">
          @csrf
          @method('PUT')
          <div class="card-body">
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="form-group">
              <label for="code">Code <span class="text-danger">*</span></label>
              <input type="text" name="code" class="form-control" id="code"
                value="{{ old('code', $payment_term->code) }}" required>
            </div>

            <div class="form-group">
              <label for="name">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" id="name"
                value="{{ old('name', $payment_term->name) }}" required>
            </div>

            <div class="form-group">
              <label for="due_days">Due Days <span class="text-danger">*</span></label>
              <input type="number" name="due_days" class="form-control" id="due_days"
                value="{{ old('due_days', $payment_term->due_days) }}" min="0" required>
            </div>

            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                  {{ old('is_active', $payment_term->is_active) ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active">Active</label>
              </div>
            </div>

          </div>
          <div class="card-footer d-flex justify-content-between">
            <button type="button" class="btn btn-danger"
              onclick="document.getElementById('delete-form').submit();">Delete</button>
            <button type="submit" class="btn btn-primary">Update Payment Term</button>
          </div>
        </form>
        <form id="delete-form"
          action="{{ company_route('settings.payment-terms.destroy', ['payment_term' => $payment_term->id]) }}"
          method="POST" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      </div>
    </div>
  </div>
@endsection
