@extends('theme.adminlte.layouts.app')

@section('title', 'New Sales Order - Billing')

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8">
      <h1 class="m-0">
        <i class="fas fa-file-invoice mr-2 text-primary"></i> New Sales Order
      </h1>
      <div class="text-muted">Step 3: Billing & Terms</div>
    </div>
  </div>
@endsection

@section('content')
  <div class="container-fluid pos-shell">

    <div class="card ent-card">
      <div class="card-header p-0 pt-3 border-bottom-0">
        <ul class="nav nav-tabs pos-steps" role="tablist">
          <li class="nav-item">
            <a class="nav-link" href="{{ company_route('sales.orders.create') }}">
              <i class="fas fa-user mr-2"></i> 1. Customer
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ company_route('sales.orders.products', ['order' => $order->uuid]) }}">
              <i class="fas fa-cubes mr-2"></i> 2. Products
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="#">
              <i class="fas fa-file-invoice-dollar mr-2"></i> 3. Terms
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="#">
              <i class="fas fa-check-circle mr-2"></i> 4. Preview
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body p-4">

        <form action="{{ company_route('sales.orders.store.billing', ['order' => $order->uuid]) }}" method="POST">
          @csrf

          <div class="row">
            {{-- Payment Terms & Notes --}}
            <div class="col-md-6 mb-4">
              <div class="card shadow-sm h-100">
                <div class="card-header font-weight-bold bg-light">Payment & Details</div>
                <div class="card-body">
                  <div class="form-group">
                    <label class="font-weight-bold">Payment Terms</label>
                    <select name="payment_term_id" class="form-control ent-control">
                      <option value="">Select Payment Term</option>
                      @foreach ($paymentTerms as $term)
                        <option value="{{ $term->id }}" {{ $order->payment_term_id == $term->id ? 'selected' : '' }}>
                          {{ $term->name }} ({{ $term->days_to_due }} Days)
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="font-weight-bold">Customer Notes / Comments</label>
                    <textarea name="notes" class="form-control ent-control" rows="4">{{ $order->notes }}</textarea>
                  </div>
                </div>
              </div>
            </div>

            {{-- Additional Costs --}}
            <div class="col-md-6 mb-4">
              <div class="card shadow-sm h-100">
                <div class="card-header font-weight-bold bg-light">Additional Costs</div>
                <div class="card-body">
                  <div class="form-group row">
                    <label class="col-sm-4 col-form-label font-weight-bold">Subtotal</label>
                    <div class="col-sm-8">
                      <input type="text" readonly class="form-control-plaintext text-right font-weight-bold"
                        value="{{ number_format($order->subtotal, 2) }}">
                    </div>
                  </div>

                  <div class="form-group row">
                    <label class="col-sm-4 col-form-label font-weight-bold">Shipping Cost</label>
                    <div class="col-sm-8">
                      <input type="number" name="shipping_total" class="form-control ent-control text-right"
                        step="0.01" value="{{ $order->shipping_total }}">
                    </div>
                  </div>

                  <div class="form-group row">
                    <label class="col-sm-4 col-form-label font-weight-bold">Additional Charges</label>
                    <div class="col-sm-8">
                      <input type="number" name="additional_total" class="form-control ent-control text-right"
                        step="0.01" value="{{ $order->additional_total }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Billing Address --}}
            <div class="col-md-6">
              <div class="card shadow-sm">
                <div class="card-header font-weight-bold bg-light">Billing Address</div>
                <div class="card-body">
                  @php $bill = is_array($order->billing_address) ? $order->billing_address : []; @endphp
                  <div class="form-group">
                    <label class="small text-muted">Bill To Name</label>
                    <input type="text" name="billing[name]" class="form-control form-control-sm"
                      value="{{ $bill['name'] ?? '' }}">
                  </div>
                  <div class="form-group">
                    <label class="small text-muted">Address Line</label>
                    <input type="text" name="billing[address_line_1]" class="form-control form-control-sm"
                      value="{{ $bill['address_line_1'] ?? '' }}">
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-4">
                      <label class="small text-muted">City</label>
                      <input type="text" name="billing[city]" class="form-control form-control-sm"
                        value="{{ $bill['city'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-4">
                      <label class="small text-muted">State</label>
                      <input type="text" name="billing[state]" class="form-control form-control-sm"
                        value="{{ $bill['state'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-4">
                      <label class="small text-muted">Zip</label>
                      <input type="text" name="billing[postal_code]" class="form-control form-control-sm"
                        value="{{ $bill['postal_code'] ?? '' }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Shipping Address --}}
            <div class="col-md-6">
              <div class="card shadow-sm">
                <div class="card-header font-weight-bold bg-light d-flex justify-content-between align-items-center">
                  <span>Shipping Address</span>
                  <small class="text-primary" style="cursor:pointer;" onclick="copyBilling()">Copy Billing</small>
                </div>
                <div class="card-body">
                  @php $ship = is_array($order->shipping_address) ? $order->shipping_address : []; @endphp
                  <div class="form-group">
                    <label class="small text-muted">Ship To Name</label>
                    <input type="text" name="shipping[name]" class="form-control form-control-sm"
                      value="{{ $ship['name'] ?? '' }}">
                  </div>
                  <div class="form-group">
                    <label class="small text-muted">Address Line</label>
                    <input type="text" name="shipping[address_line_1]" class="form-control form-control-sm"
                      value="{{ $ship['address_line_1'] ?? '' }}">
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-4">
                      <label class="small text-muted">City</label>
                      <input type="text" name="shipping[city]" class="form-control form-control-sm"
                        value="{{ $ship['city'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-4">
                      <label class="small text-muted">State</label>
                      <input type="text" name="shipping[state]" class="form-control form-control-sm"
                        value="{{ $ship['state'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-4">
                      <label class="small text-muted">Zip</label>
                      <input type="text" name="shipping[postal_code]" class="form-control form-control-sm"
                        value="{{ $ship['postal_code'] ?? '' }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary btn-ent px-5 py-2">
              Review Order <i class="fas fa-arrow-right ml-2"></i>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function copyBilling() {
      $('[name="shipping[name]"]').val($('[name="billing[name]"]').val());
      $('[name="shipping[address_line_1]"]').val($('[name="billing[address_line_1]"]').val());
      $('[name="shipping[city]"]').val($('[name="billing[city]"]').val());
      $('[name="shipping[state]"]').val($('[name="billing[state]"]').val());
      $('[name="shipping[postal_code]"]').val($('[name="billing[postal_code]"]').val());
    }
  </script>
@endpush
