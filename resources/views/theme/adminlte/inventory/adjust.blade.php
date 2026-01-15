@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0">Adjust Stock</h1>
    </div>
    <div class="col-sm-6">
      <div class="float-sm-right">
        <a href="{{ company_route('inventory.index') }}" class="btn btn-default">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  {{-- Validation Errors --}}
  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <h5><i class="icon fas fa-ban"></i> Validation Error!</h5>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card card-warning">
    <div class="card-header">
      <h3 class="card-title">Manual Stock Adjustment</h3>
    </div>
    <form action="{{ route('inventory.store', ['company' => request()->company->uuid]) }}" method="POST">
      @csrf

      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Adjustment Type *</label>
              <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                <option value="add" {{ old('type') == 'add' ? 'selected' : '' }}>Add Stock</option>
                <option value="remove" {{ old('type') == 'remove' ? 'selected' : '' }}>Remove Stock</option>
              </select>
              @error('type')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>Location *</label>
              <select name="inventory_location_id" class="form-control select2 @error('inventory_location_id') is-invalid @enderror" required>
                @foreach ($locations as $loc)
                  <option value="{{ $loc->id }}" {{ old('inventory_location_id') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->locatable->name ?? $loc->type . ' #' . $loc->id }}
                  </option>
                @endforeach
              </select>
              @error('inventory_location_id')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="col-md-5">
            <div class="form-group">
              <label>Product Variant *</label>
              <select name="product_variant_id" class="form-control select2 @error('product_variant_id') is-invalid @enderror" required>
                <option value="">Select Product...</option>
                @foreach ($variants as $v)
                  <option value="{{ $v->id }}" {{ old('product_variant_id') == $v->id ? 'selected' : '' }}>
                    {{ $v->sku }} | {{ $v->product->name }}
                    ({{ $v->attributeValues->pluck('value')->join(', ') }})
                  </option>
                @endforeach
              </select>
              @error('product_variant_id')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Quantity *</label>
              <input type="number" step="0.000001" min="0.01" name="quantity"
                class="form-control @error('quantity') is-invalid @enderror"
                placeholder="e.g. 10" value="{{ old('quantity') }}" required>
              <small class="text-muted">Enter the quantity to add or remove.</small>
              @error('quantity')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Reason</label>
              <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror"
                placeholder="Optional reason" value="{{ old('reason') }}">
              @error('reason')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Notes</label>
              <input type="text" name="notes" class="form-control" placeholder="Optional reference" value="{{ old('notes') }}">
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer">
        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Process Adjustment</button>
        <a href="{{ company_route('inventory.index') }}" class="btn btn-default">Cancel</a>
      </div>
    </form>
  </div>
@endsection
