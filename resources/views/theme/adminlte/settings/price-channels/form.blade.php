@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">{{ isset($priceChannel) ? 'Edit Price Channel' : 'Create Price Channel' }}</h1>
      <small class="text-muted">{{ isset($priceChannel) ? 'Modify price channel details.' : 'Define a new pricing channel.' }}</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('price-channels.index') }}" class="btn btn-secondary shadow-sm">
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
          <h3 class="card-title lead">
            <i class="fas {{ isset($priceChannel) ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
            {{ isset($priceChannel) ? 'Price Channel Details' : 'New Price Channel Details' }}
          </h3>
        </div>
        <form action="{{ isset($priceChannel) ? route('price-channels.update', $priceChannel->id) : route('price-channels.store') }}" method="POST">
          @csrf
          @if (isset($priceChannel))
            @method('PUT')
          @endif

          <div class="card-body">
            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
                <label for="code" class="font-weight-bold">Channel Code <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                  </div>
                  <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" id="code"
                    placeholder="e.g. POS, WEBSITE, B2B"
                    value="{{ old('code', $priceChannel->code ?? '') }}"
                    required maxlength="50" style="text-transform: uppercase;">
                  @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <small class="form-text text-muted">Unique identifier code (letters, numbers, dashes, underscores only).</small>
              </div>

              <div class="form-group col-md-6">
                <label for="name" class="font-weight-bold">Channel Name <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-font"></i></span>
                  </div>
                  <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name"
                    placeholder="e.g. Point of Sale, Website, B2B Wholesale"
                    value="{{ old('name', $priceChannel->name ?? '') }}" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <hr>

            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                  {{ old('is_active', $priceChannel->is_active ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-bold" for="is_active">Active</label>
              </div>
              <small class="form-text text-muted">Inactive channels will not be available for pricing.</small>
            </div>
          </div>

          <div class="card-footer text-right">
            <a href="{{ route('price-channels.index') }}" class="btn btn-secondary">
              <i class="fas fa-times mr-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save mr-1"></i> {{ isset($priceChannel) ? 'Update' : 'Create' }} Price Channel
            </button>
          </div>
        </form>
      </div>

      @if (isset($priceChannel))
        <div class="card card-danger card-outline shadow-sm mt-4">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-trash-alt mr-2"></i>Danger Zone</h3>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">Deleting this price channel will remove it permanently. This action cannot be undone.</p>
            <form action="{{ route('price-channels.destroy', $priceChannel->id) }}" method="POST"
              onsubmit="return confirm('Are you sure you want to delete this price channel? This action cannot be undone.');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt mr-1"></i> Delete Price Channel
              </button>
            </form>
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
