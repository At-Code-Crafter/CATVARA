@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">Edit Currency</h1>
      <small class="text-muted">Update currency details.</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('currencies.index') }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to List
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card card-primary card-outline shadow-lg">
        <div class="card-header">
          <h3 class="card-title lead"><i class="fas fa-edit mr-2"></i>Edit: {{ $currency->code }}</h3>
        </div>
        <form action="{{ route('currencies.update', $currency->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="card-body">
            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="code" class="font-weight-bold">Currency Code <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                  </div>
                  <input type="text" name="code" class="form-control" id="code"
                    value="{{ old('code', $currency->code) }}" required maxlength="3" style="text-transform: uppercase;">
                </div>
              </div>

              <div class="form-group col-md-6">
                <label for="symbol" class="font-weight-bold">Symbol</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                  </div>
                  <input type="text" name="symbol" class="form-control" id="symbol"
                    value="{{ old('symbol', $currency->symbol) }}">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="name" class="font-weight-bold">Currency Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" id="name"
                value="{{ old('name', $currency->name) }}" required>
            </div>

            <div class="form-group">
              <label for="decimal_places" class="font-weight-bold">Decimal Places <span
                  class="text-danger">*</span></label>
              <input type="number" name="decimal_places" class="form-control" id="decimal_places"
                value="{{ old('decimal_places', $currency->decimal_places) }}" min="0" max="8" required>
            </div>

            <hr>

            <div class="form-group">
              <div class="custom-control custom-switch custom-switch-lg">
                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                  {{ old('is_active', $currency->is_active) ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-bold text-success" for="is_active">Active Status</label>
              </div>
            </div>

          </div>
          <div class="card-footer bg-light d-flex justify-content-between">
            <button type="button" class="btn btn-outline-danger"
              onclick="if(confirm('Are you sure you want to delete this currency?')) document.getElementById('delete-form').submit();">
              <i class="fas fa-trash-alt mr-1"></i> Delete
            </button>
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> Update Changes</button>
          </div>
        </form>
        <form id="delete-form" action="{{ route('currencies.destroy', $currency->id) }}" method="POST"
          style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      </div>
    </div>
  </div>
@endsection
