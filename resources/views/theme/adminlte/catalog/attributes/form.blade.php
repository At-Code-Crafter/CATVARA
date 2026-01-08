@extends('theme.adminlte.layouts.app')

@section('title', isset($attribute) ? 'Edit Attribute' : 'Create Attribute')

@section('content-header')

<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-7">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-sliders-h"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">{{ isset($attribute) ? 'Edit Attribute' : 'Create Attribute' }}</h1>
          <div class="text-muted small">
            {{ isset($attribute) ? 'Update attribute definition and manage values.' : 'Create a new attribute and seed initial values.' }}
            <span class="d-none d-sm-inline">for <span class="font-weight-bold">{{ active_company()?->name ?? 'Company' }}</span>.</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-5 d-flex justify-content-sm-end mt-3 mt-sm-0">
      <a href="{{ company_route('catalog.attributes.index') }}" class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')
  <form
    action="{{ isset($attribute) ? company_route('catalog.attributes.update', ['attribute' => $attribute->id]) : company_route('catalog.attributes.store') }}"
    method="POST" class="ajax-form">
    @csrf
    @if (isset($attribute))
      @method('PUT')
    @endif

    <div class="row">
      {{-- LEFT --}}
      <div class="col-lg-8">

        {{-- ATTRIBUTE INFO --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-tag"></i> Attribute Information
            </h3>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <div class="form-group">
                  <label for="name">Attribute Name <span class="req">*</span></label>
                  <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $attribute->name ?? '') }}"
                    class="form-control ent-control @error('name') is-invalid @enderror"
                    placeholder="e.g. Color, Size, Material">

                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror

                  <div class="help-hint">Customer-friendly name that appears in filters and variant options.</div>
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label for="code">Attribute Code <span class="req">*</span></label>
                  <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code', $attribute->code ?? '') }}"
                    class="form-control ent-control @error('code') is-invalid @enderror"
                    placeholder="e.g. color, size"
                    {{ isset($attribute) ? 'readonly' : '' }}>

                  @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror

                  @if (isset($attribute))
                    <div class="help-hint">Code cannot be changed once created.</div>
                  @else
                    <div class="help-hint">Lowercase, no spaces. Used internally for matching and APIs.</div>
                  @endif
                </div>
              </div>
            </div>

            <div class="ent-divider"></div>

            <div class="d-flex flex-wrap" style="gap:10px;">
              <span class="ent-chip">
                <i class="fas fa-shield-alt text-muted"></i>
                Keep codes stable for integrations
              </span>
              <span class="ent-chip">
                <i class="fas fa-layer-group text-muted"></i>
                Values drive variant combinations
              </span>
            </div>
          </div>
        </div>

        {{-- VALUES --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-list"></i> Attribute Values
            </h3>
          </div>

          <div class="card-body">
            @if (isset($attribute))
              <div class="form-group mb-3">
                <label class="mb-2">Existing Values</label>

                <div class="table-responsive">
                  <table class="table table-hover table-enterprise mb-0">
                    <thead>
                      <tr>
                        <th style="min-width:260px;">Value</th>
                        <th style="width: 140px;">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($attribute->values as $val)
                        <tr>
                          <td>
                            <input
                              type="text"
                              name="existing_values[{{ $val->id }}][value]"
                              class="form-control ent-control form-control-sm"
                              value="{{ $val->value }}"
                              readonly>
                          </td>
                          <td class="text-center">
                            <div class="custom-control custom-switch d-inline-block">
                              <input type="hidden" name="existing_values[{{ $val->id }}][is_active]" value="0">
                              <input
                                type="checkbox"
                                class="custom-control-input"
                                id="switch_{{ $val->id }}"
                                name="existing_values[{{ $val->id }}][is_active]"
                                value="1"
                                {{ $val->is_active ? 'checked' : '' }}>
                              <label class="custom-control-label" for="switch_{{ $val->id }}"></label>
                            </div>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="2" class="text-center text-muted">No values found.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="help-hint mt-2">Disable values to hide them in variant selections without deleting history.</div>
              </div>

              <div class="form-group mb-0">
                <label for="new_values">Add New Values</label>
                <textarea
                  class="form-control ent-control"
                  id="new_values"
                  name="new_values"
                  rows="2"
                  placeholder="Comma separated, e.g. Yellow, Purple">{{ old('new_values') }}</textarea>
                <div class="help-hint">Tip: use consistent casing (e.g., Red, Blue) to keep filters clean.</div>
              </div>
            @else
              <div class="form-group mb-0">
                <label for="values">Values <span class="req">*</span></label>
                <textarea
                  class="form-control ent-control @error('values') is-invalid @enderror"
                  id="values"
                  name="values"
                  rows="3"
                  placeholder="Comma separated, e.g. Red, Blue, Green">{{ old('values') }}</textarea>

                @error('values')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div class="help-hint">These values will be created immediately and can be enabled/disabled later.</div>
              </div>
            @endif
          </div>
        </div>

      </div>

      {{-- RIGHT --}}
      <div class="col-lg-4">
        <div class="card ent-card sticky-side">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-cogs"></i> Actions
            </h3>
          </div>

          <div class="card-body">
            <div class="d-flex flex-column" style="gap:10px;">
              <button type="submit" class="btn btn-primary btn-ent">
                <i class="fas fa-save mr-1"></i> {{ isset($attribute) ? 'Update Attribute' : 'Save Attribute' }}
              </button>

              <a href="{{ company_route('catalog.attributes.index') }}" class="btn btn-outline-secondary btn-ent">
                Cancel
              </a>

              <div class="ent-divider"></div>

              <div class="text-muted small">
                <div class="mb-1"><i class="fas fa-info-circle mr-1"></i> Notes</div>
                <ul class="pl-3 mb-0">
                  <li>Codes should be stable and unique per company.</li>
                  <li>Values should be short and consistent for filters.</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">
              <i class="far fa-clock mr-1"></i>
              {{ isset($attribute) ? 'Editing mode' : 'Creation mode' }}
            </span>
            <span class="ent-side-chip">
              <i class="fas fa-layer-group text-muted"></i>
              Catalog
            </span>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    $(function () {
      // Optional: if you later add select2 on this form
      if ($.fn.select2) {
        $('.select2').select2({
          theme: 'bootstrap4',
          width: '100%'
        });
      }

      // Tooltips
      if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
      }
    });
  </script>
@endpush
