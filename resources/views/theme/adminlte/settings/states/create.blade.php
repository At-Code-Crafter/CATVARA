@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0">Add State</h1>
      <small class="text-muted">Create a new state/province record.</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('states.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')
  <form action="{{ route('states.store') }}" method="POST" class="ajax-form">
    @csrf

    <div class="row">
      <div class="col-lg-8">

        {{-- Basic Information --}}
        <div class="card card-outline card-secondary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> State Information</h3>
          </div>

          <div class="card-body">
            <div class="form-group">
              <label>Country <span class="text-danger">*</span></label>
              <select name="country_id" class="form-control @error('country_id') is-invalid @enderror">
                <option value="">-- Select Country --</option>
                @foreach ($countries as $country)
                  <option value="{{ $country->id }}" {{ old('country_id', $selectedCountry) == $country->id ? 'selected' : '' }}>
                    {{ $country->name }} ({{ $country->iso_code_2 }})
                  </option>
                @endforeach
              </select>
              @error('country_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label>State/Province Name <span class="text-danger">*</span></label>
              <input type="text" name="name" value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror" placeholder="e.g. California">
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>State Code</label>
                  <input type="text" name="code" value="{{ old('code') }}" maxlength="10"
                    class="form-control text-uppercase @error('code') is-invalid @enderror" placeholder="e.g. CA">
                  <small class="text-muted">Unique code within the country.</small>
                  @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Type</label>
                  <select name="type" class="form-control @error('type') is-invalid @enderror">
                    <option value="">-- Select Type --</option>
                    <option value="State" {{ old('type') == 'State' ? 'selected' : '' }}>State</option>
                    <option value="Province" {{ old('type') == 'Province' ? 'selected' : '' }}>Province</option>
                    <option value="Territory" {{ old('type') == 'Territory' ? 'selected' : '' }}>Territory</option>
                    <option value="Region" {{ old('type') == 'Region' ? 'selected' : '' }}>Region</option>
                    <option value="District" {{ old('type') == 'District' ? 'selected' : '' }}>District</option>
                    <option value="County" {{ old('type') == 'County' ? 'selected' : '' }}>County</option>
                    <option value="Prefecture" {{ old('type') == 'Prefecture' ? 'selected' : '' }}>Prefecture</option>
                    <option value="Emirate" {{ old('type') == 'Emirate' ? 'selected' : '' }}>Emirate</option>
                    <option value="Other" {{ old('type') == 'Other' ? 'selected' : '' }}>Other</option>
                  </select>
                  @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

      <div class="col-lg-4">
        {{-- Status Card --}}
        <div class="card card-outline card-secondary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cog mr-1"></i> Status</h3>
          </div>
          <div class="card-body">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" checked>
              <label class="custom-control-label" for="is_active">Active</label>
            </div>
            <small class="text-muted">Inactive states won't appear in selection dropdowns.</small>
          </div>
        </div>

        {{-- Submit --}}
        <div class="card card-outline card-primary">
          <div class="card-body">
            <button type="submit" class="btn btn-primary btn-block">
              <i class="fas fa-save mr-1"></i> Create State
            </button>
          </div>
        </div>
      </div>
    </div>

  </form>
@endsection
