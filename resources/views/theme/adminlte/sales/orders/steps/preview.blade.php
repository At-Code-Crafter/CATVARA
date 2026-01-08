@extends('theme.adminlte.layouts.app')

@section('title', 'New Sales Order - Preview')

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8">
      <h1 class="m-0">
        <i class="fas fa-file-invoice mr-2 text-primary"></i> New Sales Order
      </h1>
      <div class="text-muted">Step 4: Preview & Confirm</div>
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
            <a class="nav-link" href="{{ company_route('sales.orders.billing', ['order' => $order->uuid]) }}">
              <i class="fas fa-file-invoice-dollar mr-2"></i> 3. Terms
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="#">
              <i class="fas fa-check-circle mr-2"></i> 4. Preview
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body p-4">

        <div class="row">
          {{-- Order Summary --}}
          <div class="col-md-8">
            <div class="card shadow-sm mb-4">
              <div class="card-header bg-light font-weight-bold">Order Details</div>
              <div class="card-body p-0">
                <table class="table table-striped mb-0">
                  <thead>
                    <tr>
                      <th>Item</th>
                      <th class="text-center">Qty</th>
                      <th class="text-right">Price</th>
                      <th class="text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($order->items as $item)
                      <tr>
                        <td>{{ $item->name }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($item->total, 2) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="card shadow-sm">
                  <div class="card-header bg-light font-weight-bold small text-uppercase">Bill To</div>
                  <div class="card-body">
                    @php $b = $order->billing_address ?? []; @endphp
                    <strong>{{ $b['name'] ?? '-' }}</strong><br>
                    {{ $b['address_line_1'] ?? '-' }}<br>
                    {{ $b['city'] ?? '' }}, {{ $b['state'] ?? '' }} {{ $b['postal_code'] ?? '' }}
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card shadow-sm">
                  <div class="card-header bg-light font-weight-bold small text-uppercase">Ship To</div>
                  <div class="card-body">
                    @php $s = $order->shipping_address ?? []; @endphp
                    <strong>{{ $s['name'] ?? '-' }}</strong><br>
                    {{ $s['address_line_1'] ?? '-' }}<br>
                    {{ $s['city'] ?? '' }}, {{ $s['state'] ?? '' }} {{ $s['postal_code'] ?? '' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Totals & Actions --}}
          <div class="col-md-4">
            <div class="card shadow-sm">
              <div class="card-header bg-light font-weight-bold">Totals</div>
              <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Subtotal</span>
                  <span class="font-weight-bold">{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Shipping</span>
                  <span class="font-weight-bold">{{ number_format($order->shipping_total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                  <span class="text-muted">Additional</span>
                  <span class="font-weight-bold">{{ number_format($order->additional_total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-4 border-top pt-2 h4">
                  <span class="font-weight-bold">Grand Total</span>
                  <span class="font-weight-bolder text-primary">{{ number_format($order->grand_total, 2) }}</span>
                </div>

                <form action="{{ company_route('sales.orders.store', ['order' => $order->uuid]) }}" method="POST">
                  @csrf

                  <button type="submit" name="action" value="save"
                    class="btn btn-outline-primary btn-block btn-lg mb-2">
                    <i class="fas fa-save mr-2"></i> Save Order
                  </button>

                  <button type="submit" name="action" value="invoice" class="btn btn-success btn-block btn-lg">
                    <i class="fas fa-file-invoice mr-2"></i> Confirm & Invoice
                  </button>
                </form>

              </div>
            </div>

            <div class="card shadow-sm mt-3">
              <div class="card-body small text-muted">
                <strong>Payment Terms:</strong> {{ $order->payment_term_id ? 'Selected' : 'None' }}<br>
                <strong>Notes:</strong> {{ Str::limit($order->notes, 50) }}
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
@endsection
