@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0 font-weight-bold text-dark">
        <i class="fas fa-edit mr-2 text-primary"></i>Edit Product: <span class="text-primary">{{ $product->name }}</span>
      </h1>
      <ol class="breadcrumb mt-2">
        <li class="breadcrumb-item"><a href="{{ company_route('catalog.products.index') }}">Catalog</a></li>
        <li class="breadcrumb-item active">Product Editor</li>
      </ol>
    </div>
    <div class="col-sm-6">
      <div class="float-sm-right d-flex">
        <a href="{{ company_route('catalog.products.index') }}" class="btn btn-outline-secondary mr-2 px-4 shadow-sm">
          <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
        <button type="submit" form="product-form" class="btn btn-primary px-4 shadow">
          <i class="fas fa-save mr-1"></i> Save Changes
        </button>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      margin-bottom: 2rem;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }

    .status-badge {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 5px;
    }

    .status-active {
      background-color: #28a745;
      box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
    }

    .product-img-card {
      position: relative;
      overflow: hidden;
      border-radius: 12px;
      transition: transform 0.3s ease;
    }

    .product-img-card:hover {
      transform: translateY(-5px);
    }

    .media-item {
      position: relative;
      border-radius: 8px;
      overflow: hidden;
      border: 2px solid transparent;
      transition: all 0.2s;
    }

    .media-item:hover {
      border-color: #007bff;
    }

    .media-item .overlay-actions {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.4);
      display: flex;
      justify-content: center;
      align-items: center;
      opacity: 0;
      transition: opacity 0.2s;
    }

    .media-item:hover .overlay-actions {
      opacity: 1;
    }

    .variant-row:hover {
      background-color: rgba(var(--primary-rgb), 0.05) !important;
    }

    .form-section-title {
      font-size: 1rem;
      font-weight: 700;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
    }

    .form-section-title i {
      width: 30px;
      color: #3b82f6;
    }

    .input-group-modern {
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #e2e8f0;
    }

    .input-group-modern .form-control {
      border: none;
    }

    .input-group-modern .input-group-text {
      background: #f8fafc;
      border: none;
      color: #64748b;
    }
  </style>

  <form id="product-form" action="{{ company_route('catalog.products.update', ['product' => $product->id]) }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
      {{-- MAIN CONTENT (LEFT 7) --}}
      <div class="col-lg-8 col-md-12">

        {{-- GENERAL INFORMATION --}}
        <div class="card glass-card">
          <div class="card-body">
            <h4 class="form-section-title"><i class="fas fa-info-circle"></i> General Information</h4>
            <div class="row">
              <div class="col-md-12 mb-3">
                <label class="form-label text-muted small font-weight-bold">PRODUCT NAME</label>
                <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                  name="name" value="{{ old('name', $product->name) }}" placeholder="Enter product name">
                @error('name')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label text-muted small font-weight-bold">CATEGORY</label>
                <select class="form-control select2 shadow-sm" name="category_id">
                  @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                      {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                      {{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label text-muted small font-weight-bold">SLUG (URL KEY)</label>
                <div class="input-group input-group-modern">
                  <div class="input-group-prepend">
                    <span class="input-group-text small">/products/</span>
                  </div>
                  <input type="text" class="form-control" name="slug" value="{{ $product->slug }}" readonly>
                </div>
              </div>
              <div class="col-md-12">
                <label class="form-label text-muted small font-weight-bold">DESCRIPTION</label>
                <textarea class="form-control" name="description" rows="5" placeholder="Detailed product description...">{{ old('description', $product->description) }}</textarea>
              </div>
            </div>
          </div>
        </div>

        {{-- VARIANTS & PRICING --}}
        <div class="card glass-card">
          <div class="card-header border-0 bg-transparent pt-4">
            <h4 class="form-section-title mb-0"><i class="fas fa-tags"></i> Variants & Channel Pricing</h4>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small font-weight-bold">
                  <tr>
                    <th class="px-4">SKU & DETAILS</th>
                    <th width="150">COST ({{ $currency->symbol }})</th>
                    @foreach ($channels as $ch)
                      <th width="150">{{ strtoupper($ch->name) }} ({{ $currency->symbol }})</th>
                    @endforeach
                    <th width="120" class="text-center">INVENTORY</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($product->variants as $variant)
                    <tr class="variant-row">
                      <td class="px-4">
                        <div class="d-flex align-items-center mb-1">
                          <div class="custom-control custom-switch mr-2">
                            <input type="checkbox" class="custom-control-input" id="v_active_{{ $variant->id }}"
                              name="variants[{{ $variant->id }}][is_active]"
                              {{ $variant->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="v_active_{{ $variant->id }}"></label>
                          </div>
                          <input type="text" class="form-control form-control-sm font-weight-bold border-0 bg-light"
                            name="variants[{{ $variant->id }}][sku]" value="{{ $variant->sku }}" placeholder="SKU">
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                          @foreach ($variant->attributeValues as $val)
                            <span class="badge badge-light border text-muted small mr-1 mb-1">
                              {{ strtoupper($val->attribute->name) }}: {{ $val->value }}
                            </span>
                          @endforeach
                        </div>
                      </td>
                      <td>
                        <input type="number" step="0.01" class="form-control border-0 bg-transparent"
                          name="variants[{{ $variant->id }}][cost_price]" value="{{ $variant->cost_price }}"
                          placeholder="0.00">
                      </td>
                      @foreach ($channels as $ch)
                        @php
                          $price = $variant->prices->where('price_channel_id', $ch->id)->first();
                          $val = $price ? $price->price : '';
                        @endphp
                        <td>
                          <input type="number" step="0.01"
                            class="form-control border-0 bg-transparent text-primary font-weight-bold"
                            name="prices[{{ $variant->id }}][{{ $ch->id }}]" value="{{ $val }}"
                            placeholder="0.00">
                        </td>
                      @endforeach
                      <td class="text-center">
                        <a href="{{ company_route('inventory.variant.details', ['product_variant' => $variant->id]) }}"
                          class="btn btn-icon btn-outline-primary btn-sm rounded-circle" target="_blank"
                          title="Manage Inventory">
                          <i class="fas fa-boxes"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="p-3 bg-light rounded-bottom small text-muted">
              <i class="fas fa-info-circle mr-1"></i> Changes to pricing will be applied across all selected channels.
              Ensure accuracy before saving.
            </div>
          </div>
        </div>

        {{-- MEDIA GALLERY --}}
        <div class="card glass-card">
          <div class="card-body">
            <h4 class="form-section-title"><i class="fas fa-images"></i> Media Gallery</h4>
            <div class="row">
              @foreach ($product->attachments as $atch)
                <div class="col-6 col-sm-4 col-md-3 mb-3">
                  <div class="media-item shadow-sm">
                    <img src="{{ asset('storage/' . $atch->path) }}" class="img-fluid"
                      style="height: 150px; width: 100%; object-fit: cover;">
                    <div class="overlay-actions">
                      <button type="button" class="btn btn-xs btn-danger mr-1" title="Delete"><i
                          class="fas fa-trash"></i></button>
                      @if (!$atch->is_primary)
                        <button type="button" class="btn btn-xs btn-primary" title="Set Primary"><i
                            class="fas fa-star"></i></button>
                      @else
                        <span class="badge badge-warning">Primary</span>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
              <div class="col-6 col-sm-4 col-md-3 mb-3">
                <label
                  class="media-item d-flex flex-column justify-content-center align-items-center bg-light border-dashed"
                  style="height: 150px; cursor: pointer; border: 2px dashed #cbd5e1;">
                  <i class="fas fa-plus-circle text-muted fa-2x mb-2"></i>
                  <span class="text-muted small">Upload Image</span>
                  <input type="file" name="images[]" class="d-none" multiple>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- SIDEBAR CONTENT (RIGHT 3) --}}
      <div class="col-lg-4 col-md-12">

        {{-- STATUS & VISIBILITY --}}
        <div class="card glass-card border-left-lg border-primary">
          <div class="card-body">
            <h5 class="font-weight-bold mb-3">Status & Visibility</h5>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <span class="status-badge status-active"></span>
                <span class="font-weight-bold">Active</span>
              </div>
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                  {{ $product->is_active ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active"></label>
              </div>
            </div>
            <hr>
            <div class="small text-muted mb-2">
              <i class="far fa-calendar-alt mr-1"></i> Created: <b>{{ $product->created_at->format('M d, Y') }}</b>
            </div>
            <div class="small text-muted mb-3">
              <i class="far fa-clock mr-1"></i> Last Updated: <b>{{ $product->updated_at->diffForHumans() }}</b>
            </div>
            <button type="submit" form="product-form" class="btn btn-primary btn-block shadow">
              <i class="fas fa-save mr-1"></i> Update Product
            </button>
          </div>
        </div>

        {{-- PRIMARY PREVIEW --}}
        <div class="card glass-card">
          <div class="card-header border-0 bg-transparent pt-3 pb-0">
            <label class="form-label text-muted small font-weight-bold">MAIN PRODUCT PREVIEW</label>
          </div>
          <div class="card-body text-center pt-2">
            @php
              $primaryImg = $product->attachments->where('is_primary', true)->first();
              $primarySrc = $primaryImg
                  ? asset('storage/' . $primaryImg->path)
                  : asset('theme/adminlte/dist/img/default-150x150.png');
            @endphp
            <div class="product-img-card border p-2 shadow-sm bg-white">
              <img src="{{ $primarySrc }}" class="img-fluid rounded" style="max-height: 250px;">
            </div>
            <div class="mt-3">
              <label class="btn btn-outline-primary btn-sm btn-block">
                <i class="fas fa-camera mr-1"></i> Change Main Image
                <input type="file" name="primary_image" class="d-none">
              </label>
            </div>
          </div>
        </div>

        {{-- QUICK LINKS --}}
        <div class="card glass-card">
          <div class="card-body">
            <h5 class="font-weight-bold mb-3">Quick Navigation</h5>
            <ul class="list-group list-group-flush small">
              <li class="list-group-item px-0 bg-transparent border-bottom">
                <a href="{{ company_route('inventory.index') }}"
                  class="text-dark d-flex justify-content-between align-items-center">
                  <span><i class="fas fa-boxes mr-2 text-info"></i> View Stock Levels</span>
                  <i class="fas fa-chevron-right text-muted x-small"></i>
                </a>
              </li>
              <li class="list-group-item px-0 bg-transparent border-bottom">
                <a href="#" class="text-dark d-flex justify-content-between align-items-center">
                  <span><i class="fas fa-chart-line mr-2 text-success"></i> Sales Performance</span>
                  <i class="fas fa-chevron-right text-muted x-small"></i>
                </a>
              </li>
              <li class="list-group-item px-0 bg-transparent border-bottom">
                <a href="#" class="text-dark d-flex justify-content-between align-items-center">
                  <span><i class="fas fa-history mr-2 text-warning"></i> Audit History</span>
                  <i class="fas fa-chevron-right text-muted x-small"></i>
                </a>
              </li>
            </ul>
          </div>
        </div>

      </div>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Initialize Select2
      $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
      });
    });
  </script>
@endpush
