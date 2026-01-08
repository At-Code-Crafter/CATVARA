@extends('theme.adminlte.layouts.app')

@section('title', 'Edit Product')

@section('content-header')
<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-8">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-edit"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">Edit Product</h1>
          <div class="text-muted small">
            <span class="font-weight-bold">{{ $product->name }}</span>
            <span class="mx-1">•</span>
            Update product information, variants, pricing and media.
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-4 d-flex justify-content-sm-end mt-3 mt-sm-0" style="gap:10px;">
      <a href="{{ company_route('catalog.products.index') }}" class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>

      <button type="submit" form="product-form" class="btn btn-primary btn-ent">
        <i class="fas fa-save mr-1"></i> Save Changes
      </button>
    </div>
  </div>
@endsection

@section('content')
  <form id="product-form" action="{{ company_route('catalog.products.update', ['product' => $product->id]) }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
      {{-- MAIN (LEFT) --}}
      <div class="col-lg-8 col-md-12">

        {{-- GENERAL INFORMATION --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-info-circle"></i> General Information
            </h3>
          </div>

          <div class="card-body">
            <div class="row">

              <div class="col-12 mb-3">
                <label>Product Name <span class="req">*</span></label>
                <input type="text" class="form-control ent-control @error('name') is-invalid @enderror"
                  name="name" value="{{ old('name', $product->name) }}" placeholder="Enter product name">
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6 mb-3">
                <label>Category <span class="req">*</span></label>
                <select class="form-control ent-control select2" name="category_id">
                  @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                      {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                      {{ $cat->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label>Slug</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text ent-input-addon">/products/</span>
                  </div>
                  <input type="text" class="form-control ent-control" name="slug" value="{{ $product->slug }}" readonly>
                </div>
                <div class="help-hint">Slug is auto-managed and cannot be edited here.</div>
              </div>

              <div class="col-12">
                <label>Description</label>
                <textarea class="form-control ent-control" name="description" rows="5"
                  placeholder="Detailed product description...">{{ old('description', $product->description) }}</textarea>
              </div>

            </div>
          </div>
        </div>

        {{-- VARIANTS & PRICING --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <h3 class="card-title">
                <i class="fas fa-tags"></i> Variants & Channel Pricing
              </h3>
              <span class="ent-chip">
                <i class="fas fa-coins"></i>
                Currency: {{ $currency->symbol }}
              </span>
            </div>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-enterprise mb-0 ent-variant-table">
                <thead>
                  <tr>
                    <th style="min-width: 330px;">SKU & Details</th>
                    <th style="min-width: 150px;">Cost ({{ $currency->symbol }})</th>
                    @foreach ($channels as $ch)
                      <th style="min-width: 160px;">{{ strtoupper($ch->name) }} ({{ $currency->symbol }})</th>
                    @endforeach
                    <th style="min-width: 120px;" class="text-center">Inventory</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach ($product->variants as $variant)
                    <tr>
                      <td>
                        <div class="d-flex align-items-center" style="gap:10px;">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="v_active_{{ $variant->id }}"
                              name="variants[{{ $variant->id }}][is_active]" value="1"
                              {{ $variant->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="v_active_{{ $variant->id }}"></label>
                          </div>

                          <input type="text" class="form-control ent-control ent-variant-sku"
                            name="variants[{{ $variant->id }}][sku]" value="{{ $variant->sku }}" placeholder="SKU">
                        </div>

                        <div class="mt-2 d-flex flex-wrap" style="gap:6px;">
                          @foreach ($variant->attributeValues as $val)
                            <span class="ent-chip">
                              {{ strtoupper($val->attribute->name) }}: {{ $val->value }}
                            </span>
                          @endforeach
                        </div>
                      </td>

                      <td>
                        <input type="number" step="0.01" class="form-control ent-control ent-variant-price"
                          name="variants[{{ $variant->id }}][cost_price]" value="{{ $variant->cost_price ?? '' }}"
                          placeholder="0.00">
                      </td>

                      @foreach ($channels as $ch)
                        @php
                          $price = $variant->prices->where('price_channel_id', $ch->id)->first();
                          $val = $price ? $price->price : '';
                        @endphp
                        <td>
                          <input type="number" step="0.01" class="form-control ent-control ent-variant-price ent-price-accent"
                            name="prices[{{ $variant->id }}][{{ $ch->id }}]" value="{{ $val }}" placeholder="0.00">
                        </td>
                      @endforeach

                      <td class="text-center">
                        <a href="{{ company_route('inventory.variant.details', ['product_variant' => $variant->id]) }}"
                          class="btn btn-sm btn-outline-primary btn-ent ent-icon-btn"
                          target="_blank" data-toggle="tooltip" title="Manage Inventory">
                          <i class="fas fa-boxes"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="ent-callout">
              <i class="fas fa-info-circle mr-2"></i>
              Changes to pricing will be applied across all channels for each variant. Please review before saving.
            </div>
          </div>
        </div>

        {{-- MEDIA GALLERY --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-images"></i> Media Gallery
            </h3>
          </div>

          <div class="card-body">
            <div class="row">
              @foreach ($product->attachments as $atch)
                <div class="col-6 col-sm-4 col-md-3 mb-3">
                  <div class="ent-media-item" title="Media">
                    <img src="{{ asset('storage/' . $atch->path) }}" alt="media"
                      class="img-fluid ent-media-img">
                    <div class="ent-media-overlay">
                      {{-- Keep your actions as-is (wire to routes/ajax later) --}}
                      <button type="button" class="btn btn-sm btn-danger btn-ent ent-icon-btn" data-toggle="tooltip" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>

                      @if (!$atch->is_primary)
                        <button type="button" class="btn btn-sm btn-primary btn-ent ent-icon-btn" data-toggle="tooltip" title="Set Primary">
                          <i class="fas fa-star"></i>
                        </button>
                      @else
                        <span class="badge badge-warning">Primary</span>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach

              <div class="col-6 col-sm-4 col-md-3 mb-3">
                <label class="ent-media-upload">
                  <i class="fas fa-plus-circle"></i>
                  <span>Upload</span>
                  <input type="file" name="images[]" class="d-none" multiple>
                </label>
              </div>
            </div>

            <div class="help-hint">
              Tip: Upload multiple images at once. Primary image is used as thumbnail across listings.
            </div>
          </div>
        </div>

      </div>

      {{-- SIDEBAR (RIGHT) --}}
      <div class="col-lg-4 col-md-12">
        <div class="sticky-side">

          {{-- STATUS --}}
          <div class="card ent-card mb-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-toggle-on"></i> Status & Visibility
              </h3>
            </div>

            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center" style="gap:10px;">
                  <span class="ent-status-dot {{ $product->is_active ? 'is-on' : 'is-off' }}"></span>
                  <div>
                    <div class="font-weight-bold">{{ $product->is_active ? 'Active' : 'Inactive' }}</div>
                    <div class="text-muted small">Controls visibility in dropdowns and listings.</div>
                  </div>
                </div>

                <div class="custom-control custom-switch">
                  <input type="hidden" name="is_active" value="0">
                  <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                    {{ $product->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label" for="is_active"></label>
                </div>
              </div>

              <div class="ent-divider"></div>

              <div class="small text-muted mb-2">
                <i class="far fa-calendar-alt mr-1"></i>
                Created: <b>{{ $product->created_at->format('M d, Y') }}</b>
              </div>

              <div class="small text-muted mb-3">
                <i class="far fa-clock mr-1"></i>
                Last Updated: <b>{{ $product->updated_at->diffForHumans() }}</b>
              </div>

              <button type="submit" form="product-form" class="btn btn-primary btn-ent btn-block">
                <i class="fas fa-save mr-1"></i> Update Product
              </button>
            </div>
          </div>

          {{-- PRIMARY PREVIEW --}}
          <div class="card ent-card mb-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-image"></i> Primary Preview
              </h3>
            </div>

            <div class="card-body text-center">
              @php
                $primaryImg = $product->attachments->where('is_primary', true)->first();
                $primarySrc = $primaryImg
                    ? asset('storage/' . $primaryImg->path)
                    : asset('theme/adminlte/dist/img/default-150x150.png');
              @endphp

              <div class="ent-preview-card">
                <img src="{{ $primarySrc }}" class="img-fluid rounded" style="max-height: 260px;" alt="primary">
              </div>

              <div class="mt-3">
                <label class="btn btn-outline-primary btn-ent btn-block" style="cursor:pointer;">
                  <i class="fas fa-camera mr-1"></i> Change Main Image
                  <input type="file" name="primary_image" class="d-none">
                </label>
              </div>
            </div>
          </div>

          {{-- QUICK LINKS --}}
          <div class="card ent-card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-link"></i> Quick Navigation
              </h3>
            </div>

            <div class="card-body p-0">
              <a href="{{ company_route('inventory.index') }}" class="ent-quick-link">
                <span><i class="fas fa-boxes mr-2 text-info"></i> View Stock Levels</span>
                <i class="fas fa-chevron-right"></i>
              </a>

              <a href="javascript:void(0)" class="ent-quick-link">
                <span><i class="fas fa-chart-line mr-2 text-success"></i> Sales Performance</span>
                <i class="fas fa-chevron-right"></i>
              </a>

              <a href="javascript:void(0)" class="ent-quick-link">
                <span><i class="fas fa-history mr-2 text-warning"></i> Audit History</span>
                <i class="fas fa-chevron-right"></i>
              </a>
            </div>
          </div>

        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    $(function() {

      // Tooltips
      if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
      }

      // Select2
      if ($.fn.select2) {
        $('.select2').select2({
          theme: 'bootstrap4',
          width: '100%'
        });
      }
    });
  </script>
@endpush
