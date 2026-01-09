@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0">Create Store</h1>
    </div>
    <div class="col-sm-6">
      <div class="float-sm-right">
        <a href="{{ company_route('inventory.stores.index') }}" class="btn btn-default">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Store Details</h3>
        </div>
        <form id="storeForm" action="{{ company_route('inventory.stores.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="form-group">
              <label>Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name') }}" placeholder="Downtown Store">
              <div class="invalid-feedback" id="name-error">@error('name') {{ $message }} @enderror</div>
            </div>
            <div class="form-group">
              <label>Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" id="code" value="{{ old('code') }}" placeholder="ST-01">
              <div class="invalid-feedback" id="code-error">@error('code') {{ $message }} @enderror</div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="3">{{ old('address') }}</textarea>
              <div class="invalid-feedback" id="address-error">@error('address') {{ $message }} @enderror</div>
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone') }}">
              <div class="invalid-feedback" id="phone-error">@error('phone') {{ $message }} @enderror</div>
            </div>
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" checked>
              <label class="custom-control-label" for="is_active">Active</label>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Create Store</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  $('#storeForm').on('submit', function(e) {
    // Clear previous errors
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    let isValid = true;
    let errors = {};

    // Validate Name (required, max 255)
    const name = $('#name').val().trim();
    if (!name) {
      errors.name = 'Store name is required.';
      isValid = false;
    } else if (name.length > 255) {
      errors.name = 'Name cannot exceed 255 characters.';
      isValid = false;
    }

    // Validate Code (required, max 50)
    const code = $('#code').val().trim();
    if (!code) {
      errors.code = 'Store code is required.';
      isValid = false;
    } else if (code.length > 50) {
      errors.code = 'Code cannot exceed 50 characters.';
      isValid = false;
    }

    // Validate Phone (optional, max 50)
    const phone = $('#phone').val().trim();
    if (phone && phone.length > 50) {
      errors.phone = 'Phone cannot exceed 50 characters.';
      isValid = false;
    }

    // Show errors if validation fails
    if (!isValid) {
      e.preventDefault();
      $.each(errors, function(field, message) {
        $('#' + field).addClass('is-invalid');
        $('#' + field + '-error').text(message);
      });

      // Scroll to first error
      const firstError = $('.is-invalid').first();
      if (firstError.length) {
        $('html, body').animate({
          scrollTop: firstError.offset().top - 100
        }, 300);
        firstError.focus();
      }
    }
  });

  // Clear error on input
  $('.form-control').on('input', function() {
    $(this).removeClass('is-invalid');
    $(this).siblings('.invalid-feedback').text('');
  });
});
</script>
@endpush
