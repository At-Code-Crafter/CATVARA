@extends('theme.adminlte.layouts.app')

@section('title', 'Create Customer')

@section('content-header')

<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-7">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-user-plus"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">Create Customer</h1>
          <div class="text-muted small">
            Add a new customer to <span class="font-weight-bold">{{ $company->name }}</span>.
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-5 d-flex justify-content-sm-end mt-3 mt-sm-0">
      <a href="{{ route('customers.index', $company->uuid) }}" class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')


  <form action="{{ route('customers.store', $company->uuid) }}" class="ajax-form" method="POST">
    @csrf

    <div class="row">
      {{-- LEFT --}}
      <div class="col-lg-8">

        {{-- CUSTOMER INFORMATION --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-id-card"></i> Customer Information
            </h3>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label>Display Name <span class="req">*</span></label>
                  <input type="text"
                         name="display_name"
                         value="{{ old('display_name') }}"
                         class="form-control ent-control @error('display_name') is-invalid @enderror"
                         placeholder="e.g. John Smith or Acme Corp">
                  @error('display_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>Type <span class="req">*</span></label>
                  <select name="type" id="customer_type" class="form-control ent-control @error('type') is-invalid @enderror">
                    <option value="INDIVIDUAL" {{ old('type', 'INDIVIDUAL') == 'INDIVIDUAL' ? 'selected' : '' }}>Individual</option>
                    <option value="COMPANY" {{ old('type') == 'COMPANY' ? 'selected' : '' }}>Company</option>
                  </select>
                  @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <input type="email"
                         name="email"
                         value="{{ old('email') }}"
                         class="form-control ent-control @error('email') is-invalid @enderror"
                         placeholder="customer@example.com">
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone</label>
                  <input type="text"
                         name="phone"
                         value="{{ old('phone') }}"
                         class="form-control ent-control @error('phone') is-invalid @enderror"
                         placeholder="+1 234 567 8900">
                  @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Payment Term</label>
                  <select name="payment_term_id" id="payment_term_id" class="form-control ent-control select2">
                    <option value="">-- Select Payment Term --</option>
                    @foreach ($paymentTerms as $term)
                      <option value="{{ $term->id }}" {{ old('payment_term_id') == $term->id ? 'selected' : '' }}>
                        {{ $term->name }}
                      </option>
                    @endforeach
                  </select>
                  <div class="help-hint">Used for invoices and due-date calculation.</div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                    <label>Percentage Discount (%)</label>
                    <input type="number"
                           name="percentage_discount"
                           value="{{ old('percentage_discount', 0) }}"
                           class="form-control ent-control @error('percentage_discount') is-invalid @enderror"
                           min="0" max="100" step="0.01"
                           placeholder="0.00">
                    <div class="help-hint">Auto-applied to new orders for this customer.</div>
                    @error('percentage_discount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- COMPANY DETAILS --}}
        <div class="card ent-card mb-3" id="company_details_card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-building"></i> Company Details <span class="text-muted ml-1" style="font-weight:700;">(Optional)</span>
            </h3>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Legal Name</label>
                  <input type="text"
                         name="legal_name"
                         value="{{ old('legal_name') }}"
                         class="form-control ent-control @error('legal_name') is-invalid @enderror"
                         placeholder="e.g. Acme Corporation Ltd">
                  <div class="help-hint">Official registered name (if different from display name).</div>
                  @error('legal_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Tax Number</label>
                  <input type="text"
                         name="tax_number"
                         value="{{ old('tax_number') }}"
                         class="form-control ent-control @error('tax_number') is-invalid @enderror"
                         placeholder="VAT / TRN / GST etc">
                  @error('tax_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ADDRESS INFORMATION --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-map-marker-alt"></i> Address Information
            </h3>
          </div>

          <div class="card-body">
            <div class="form-group">
              <label>Address Line 1</label>
              <textarea name="address_line_1"
                        rows="2"
                        class="form-control ent-control @error('address_line_1') is-invalid @enderror"
                        placeholder="Street address, building, floor, etc.">{{ old('address_line_1') }}</textarea>
              @error('address_line_1')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Address Line 2</label>
              <textarea name="address_line_2"
                        rows="2"
                        class="form-control ent-control @error('address_line_2') is-invalid @enderror"
                        placeholder="Street address, building, floor, etc.">{{ old('address_line_2') }}</textarea>
              @error('address_line_2')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Country</label>
                  <select name="country_id"
                          id="country_id"
                          class="form-control ent-control @error('country_id') is-invalid @enderror">
                    <option value="">-- Select Country --</option>
                    @foreach ($countries as $country)
                      <option value="{{ $country->id }}"
                              data-uuid="{{ $country->uuid }}"
                              {{ old('country_id') == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('country_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>State / Province</label>
                  <select name="state_id" id="state_id" class="form-control ent-control @error('state_id') is-invalid @enderror">
                    <option value="">-- Select State --</option>
                  </select>
                  @error('state_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Postal Code</label>
                  <input type="text"
                         name="zip_code"
                         value="{{ old('zip_code') }}"
                         class="form-control ent-control @error('zip_code') is-invalid @enderror"
                         placeholder="e.g. 10001">
                  @error('zip_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

      {{-- RIGHT --}}
      <div class="col-lg-4">
        <div class="sticky-side">

          <div class="card ent-card mb-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-clipboard-check"></i> Notes & Status
              </h3>
            </div>

            <div class="card-body">
              <div class="d-flex flex-wrap" style="gap:10px;">
                <span class="ent-side-chip">
                  <i class="fas fa-user-tag text-muted"></i>
                  <span id="chip_type">{{ old('type', 'INDIVIDUAL') }}</span>
                </span>

                <span class="ent-side-chip">
                  <i class="fas fa-circle text-muted"></i>
                  <span>New Customer</span>
                </span>
              </div>

              <div class="ent-divider"></div>

              <div class="form-group mb-3">
                <label>Notes</label>
                <textarea name="notes"
                          rows="4"
                          class="form-control ent-control @error('notes') is-invalid @enderror"
                          placeholder="Internal notes about this customer...">{{ old('notes') }}</textarea>
                @error('notes')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group mb-0">
                <label>Status</label>
                <div class="custom-control custom-switch">
                  <input type="hidden" name="is_active" value="0">
                  <input type="checkbox"
                         name="is_active"
                         value="1"
                         class="custom-control-input"
                         id="isActiveSwitch"
                         {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                  <label class="custom-control-label" for="isActiveSwitch">Active</label>
                </div>
                <div class="help-hint">Inactive customers won't appear in dropdowns.</div>
              </div>
            </div>

            <div class="card-footer d-flex justify-content-end" style="gap:10px;">
              <button type="submit" class="btn btn-primary btn-ent">
                <i class="fas fa-save mr-1"></i> Save
              </button>
              <a href="{{ route('customers.index', $company->uuid) }}" class="btn btn-outline-secondary btn-ent">
                Cancel
              </a>
            </div>
          </div>

          <div class="card ent-card">
            <div class="card-body">
              <div class="d-flex">
                <div class="mr-3" style="font-size:18px;color:var(--ent-accent);">
                  <i class="fas fa-info-circle"></i>
                </div>
                <div>
                  <div class="font-weight-bold" style="color:var(--ent-head);">Tips</div>
                  <div class="text-muted small">
                    Start with the essentials. You can refine address and company fields later without affecting transactions.
                  </div>
                </div>
              </div>
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
      // Select2 (bootstrap4 skin)
      if ($.fn.select2) {
        $('#payment_term_id').select2({
          theme: 'bootstrap4',
          width: '100%'
        });
      }

      // Show/hide company details card based on type (UX polish)
      function syncCompanyCard() {
        const type = $('#customer_type').val();
        $('#chip_type').text(type);

        if (type === 'INDIVIDUAL') {
          $('#company_details_card').slideUp(120);
        } else {
          $('#company_details_card').slideDown(120);
        }
      }

      $('#customer_type').on('change', syncCompanyCard);
      syncCompanyCard();

      // Load states when country changes
      $('#country_id').on('change', function() {
        const $selected = $(this).find('option:selected');
        const countryUuid = $selected.data('uuid');
        const $stateSelect = $('#state_id');

        $stateSelect.html('<option value="">-- Select State --</option>');

        if (countryUuid) {
          $.get('/settings/countries/' + countryUuid + '/states', function(states) {
            states.forEach(function(state) {
              $stateSelect.append(
                $('<option>', {
                  value: state.id,
                  text: state.name
                })
              );
            });
          });
        }
      });
    });
  </script>
@endpush
