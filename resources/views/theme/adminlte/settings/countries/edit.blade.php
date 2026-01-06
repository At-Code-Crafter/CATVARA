@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0">Edit Country</h1>
      <small class="text-muted">Update country: {{ $country->name }}</small>
    </div>
    <div class="col-sm-6 d-flex justify-content-end">
      <a href="{{ route('countries.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')
  <form action="{{ route('countries.update', $country->uuid) }}" method="POST" class="ajax-form">
    @csrf
    @method('PUT')

    <div class="row">
      <div class="col-lg-8">

        {{-- Basic Information --}}
        <div class="card card-outline card-secondary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-globe mr-1"></i> Country Information</h3>
          </div>

          <div class="card-body">
            <div class="form-group">
              <label>Country Name <span class="text-danger">*</span></label>
              <input type="text" name="name" value="{{ old('name', $country->name) }}"
                class="form-control @error('name') is-invalid @enderror" placeholder="e.g. United States">
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>ISO Code (2 chars) <span class="text-danger">*</span></label>
                  <input type="text" name="iso_code_2" value="{{ old('iso_code_2', $country->iso_code_2) }}" maxlength="2"
                    class="form-control text-uppercase @error('iso_code_2') is-invalid @enderror" placeholder="e.g. US">
                  @error('iso_code_2')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>ISO Code (3 chars) <span class="text-danger">*</span></label>
                  <input type="text" name="iso_code_3" value="{{ old('iso_code_3', $country->iso_code_3) }}" maxlength="3"
                    class="form-control text-uppercase @error('iso_code_3') is-invalid @enderror" placeholder="e.g. USA">
                  @error('iso_code_3')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>Numeric Code</label>
                  <input type="text" name="numeric_code" value="{{ old('numeric_code', $country->numeric_code) }}" maxlength="3"
                    class="form-control @error('numeric_code') is-invalid @enderror" placeholder="e.g. 840">
                  @error('numeric_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone Code</label>
                  <input type="text" name="phone_code" value="{{ old('phone_code', $country->phone_code) }}"
                    class="form-control @error('phone_code') is-invalid @enderror" placeholder="e.g. +1">
                  @error('phone_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Currency Code</label>
                  <input type="text" name="currency_code" value="{{ old('currency_code', $country->currency_code) }}" maxlength="3"
                    class="form-control text-uppercase @error('currency_code') is-invalid @enderror" placeholder="e.g. USD">
                  @error('currency_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Capital</label>
              <input type="text" name="capital" value="{{ old('capital', $country->capital) }}"
                class="form-control @error('capital') is-invalid @enderror" placeholder="e.g. Washington D.C.">
              @error('capital')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Region</label>
                  <input type="text" name="region" value="{{ old('region', $country->region) }}"
                    class="form-control @error('region') is-invalid @enderror" placeholder="e.g. Americas">
                  @error('region')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Subregion</label>
                  <input type="text" name="subregion" value="{{ old('subregion', $country->subregion) }}"
                    class="form-control @error('subregion') is-invalid @enderror" placeholder="e.g. Northern America">
                  @error('subregion')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- States Preview --}}
        @if($country->states->count() > 0)
        <div class="card card-outline card-info">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> States/Provinces ({{ $country->states->count() }})</h3>
            <div class="card-tools">
              <a href="{{ route('states.create', ['country_id' => $country->id]) }}" class="btn btn-tool">
                <i class="fas fa-plus"></i> Add State
              </a>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-sm table-bordered m-0">
              <thead class="thead-light">
                <tr>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Type</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($country->states->take(10) as $state)
                <tr>
                  <td>{{ $state->name }}</td>
                  <td>{{ $state->code ?? '-' }}</td>
                  <td>{{ $state->type ?? '-' }}</td>
                  <td>
                    @if($state->is_active)
                      <span class="badge badge-success">Active</span>
                    @else
                      <span class="badge badge-secondary">Inactive</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @if($country->states->count() > 10)
            <div class="card-footer text-center">
              <a href="{{ route('states.index', ['country_id' => $country->id]) }}">View all {{ $country->states->count() }} states</a>
            </div>
            @endif
          </div>
        </div>
        @endif

      </div>

      <div class="col-lg-4">
        {{-- Status Card --}}
        <div class="card card-outline card-secondary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cog mr-1"></i> Status</h3>
          </div>
          <div class="card-body">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                {{ old('is_active', $country->is_active) ? 'checked' : '' }}>
              <label class="custom-control-label" for="is_active">Active</label>
            </div>
            <small class="text-muted">Inactive countries won't appear in selection dropdowns.</small>
          </div>
        </div>

        {{-- Info Card --}}
        <div class="card card-outline card-secondary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Info</h3>
          </div>
          <div class="card-body">
            <dl class="row mb-0">
              <dt class="col-5">Created:</dt>
              <dd class="col-7">{{ $country->created_at->format('M d, Y') }}</dd>
              <dt class="col-5">Updated:</dt>
              <dd class="col-7">{{ $country->updated_at->format('M d, Y') }}</dd>
              <dt class="col-5">States:</dt>
              <dd class="col-7">{{ $country->states->count() }}</dd>
            </dl>
          </div>
        </div>

        {{-- Submit --}}
        <div class="card card-outline card-primary">
          <div class="card-body">
            <button type="submit" class="btn btn-primary btn-block">
              <i class="fas fa-save mr-1"></i> Update Country
            </button>
          </div>
        </div>
      </div>
    </div>

  </form>
@endsection
