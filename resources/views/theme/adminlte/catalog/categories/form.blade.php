@extends('theme.adminlte.layouts.app')

@section('title', isset($category) ? 'Edit Category' : 'Create Category')

@section('content-header')

<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-7">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-sitemap"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">{{ isset($category) ? 'Edit Category' : 'Create Category' }}</h1>
          <div class="text-muted small">
            {{ isset($category) ? 'Update category settings and attribute rules.' : 'Add a new category to your catalog.' }}
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-5 d-flex justify-content-sm-end mt-3 mt-sm-0">
      <a href="{{ company_route('catalog.categories.index') }}" class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')
  <form
    action="{{ isset($category) ? company_route('catalog.categories.update', ['category' => $category->id]) : company_route('catalog.categories.store') }}"
    method="POST" class="ajax-form">
    @csrf
    @if (isset($category))
      @method('PUT')
    @endif

    <div class="row">
      {{-- LEFT SIDE --}}
      <div class="col-lg-8">

        {{-- CATEGORY INFO --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-tag"></i> Category Information
            </h3>
          </div>

          <div class="card-body">

            <div class="row">
              <div class="col-md-7">
                <div class="form-group">
                  <label for="name">Category Name <span class="req">*</span></label>
                  <input type="text"
                    class="form-control ent-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    placeholder="e.g. Electronics, Vape Kits, E-Liquids"
                    value="{{ old('name', $category->name ?? '') }}">
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="help-hint">This is the display name shown in admin and storefront.</div>
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label for="parent_id">Parent Category</label>
                  <select class="form-control ent-control select2 @error('parent_id') is-invalid @enderror"
                    id="parent_id" name="parent_id">
                    <option value="">None (Root)</option>

                    @foreach ($categories as $parent)
                      @if (!isset($category) || $parent->id != $category->id)
                        <option value="{{ $parent->id }}"
                          {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                          {{ $parent->name }}
                        </option>
                      @endif
                    @endforeach
                  </select>
                  @error('parent_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="help-hint">Optional. Use parent categories to create hierarchy like WordPress.</div>
                </div>
              </div>
            </div>

            <div class="ent-divider"></div>

            <div class="d-flex align-items-center justify-content-between">
              <div class="ent-chip">
                <i class="fas fa-info-circle"></i>
                Tip: Use attributes to control which variant options products can have under this category.
              </div>
              <div class="text-muted small">
                Changes apply to future product creation.
              </div>
            </div>

          </div>
        </div>

        {{-- ATTRIBUTES --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-sliders-h"></i> Allowed Attributes (Product Variants)
            </h3>
          </div>

          <div class="card-body">
            <div class="form-group mb-2">
              <label>Attributes</label>
              <select name="attributes[]" class="form-control ent-control select2" multiple id="attributes">
                @foreach ($attributes as $attr)
                  <option value="{{ $attr->id }}"
                    {{ (collect(old('attributes', isset($category) ? $category->attributes->pluck('id')->toArray() : []))->contains($attr->id)) ? 'selected' : '' }}>
                    {{ $attr->name }} ({{ $attr->code }})
                  </option>
                @endforeach
              </select>

              @error('attributes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror

              <div class="help-hint">
                Only selected attributes will be available when creating products in this category (e.g., Color, Size, Nicotine Strength).
              </div>
            </div>

            <div class="ent-divider"></div>

            <div class="row">
              <div class="col-md-6">
                <div class="ent-side-chip">
                  <i class="fas fa-shield-alt text-muted"></i>
                  This helps keep your product variant combinations clean.
                </div>
              </div>
              <div class="col-md-6 text-md-right mt-2 mt-md-0">
                <div class="ent-side-chip">
                  <i class="fas fa-bolt text-muted"></i>
                  Faster product creation with correct options.
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

      {{-- RIGHT SIDE (Sticky) --}}
      <div class="col-lg-4">

        <div class="card ent-card sticky-side">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-toggle-on"></i> Status & Actions
            </h3>
          </div>

          <div class="card-body">

            <div class="form-group mb-0">
              <label>Status</label>

              <div class="custom-control custom-switch">
                {{-- Default to active on create --}}
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox"
                  class="custom-control-input"
                  id="is_active"
                  name="is_active"
                  value="1"
                  {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active">Active</label>
              </div>

              <div class="help-hint">
                Inactive categories can be hidden from dropdowns and catalog selection.
              </div>
            </div>

            @if (isset($category))
              <div class="ent-divider"></div>

              <div class="text-muted small">
                <div class="d-flex justify-content-between">
                  <span>Created</span>
                  <span class="font-weight-bold">{{ optional($category->created_at)->format('Y-m-d') }}</span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                  <span>Updated</span>
                  <span class="font-weight-bold">{{ optional($category->updated_at)->format('Y-m-d') }}</span>
                </div>
              </div>
            @endif

          </div>

          <div class="card-footer d-flex justify-content-end" style="gap:10px;">
            <button type="submit" class="btn btn-primary btn-ent">
              <i class="fas fa-save mr-1"></i> {{ isset($category) ? 'Update' : 'Save Category' }}
            </button>
            <a href="{{ company_route('catalog.categories.index') }}" class="btn btn-outline-secondary btn-ent">
              Cancel
            </a>
          </div>
        </div>

      </div>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Select2 bootstrap4 skin (must match enterprise controls)
      if ($.fn.select2) {
        $('#parent_id').select2({
          theme: 'bootstrap4',
          width: '100%',
          placeholder: 'None (Root)',
          allowClear: true
        });

        $('#attributes').select2({
          theme: 'bootstrap4',
          width: '100%',
          placeholder: 'Select allowed attributes',
          closeOnSelect: false
        });
      }
    });
  </script>
@endpush
