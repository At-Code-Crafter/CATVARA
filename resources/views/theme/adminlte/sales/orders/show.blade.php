@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h1 class="m-0 font-weight-bold text-dark">
        <i class="fas fa-file-invoice mr-2 text-primary"></i>Order #{{ $order->order_number }}
      </h1>
    </div>
    <div class="col-sm-6 text-right">
      <a href="{{ company_route('sales-orders.index') }}" class="btn btn-default shadow-sm border">
        <i class="fas fa-arrow-left mr-1"></i> Back to List
      </a>
      <a href="{{ company_route('sales-orders.print', ['order' => $order->uuid]) }}"
        class="btn btn-outline-primary shadow-sm border px-4 ml-2" target="_blank">
        <i class="fas fa-print mr-1"></i> Print Invoice
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row">
    <div class="col-md-8">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
          <h3 class="card-title font-weight-bold"><i class="fas fa-list mr-2"></i>Line Items</h3>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped mb-0">
            <thead class="bg-light">
              <tr>
                <th class="pl-4">Product/Variant</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Tax</th>
                <th class="text-right pr-4">Line Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($order->items as $item)
                <tr>
                  <td class="pl-4 font-weight-bold text-dark">{{ $item->product_name }}</td>
                  <td class="text-center">{{ (float) $item->quantity }}</td>
                  <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                  <td class="text-right text-danger">-{{ number_format($item->discount_amount, 2) }}</td>
                  <td class="text-right text-muted">{{ number_format($item->tax_amount, 2) }}
                    ({{ (float) $item->tax_rate }}%)</td>
                  <td class="text-right pr-4 font-weight-bold text-dark">{{ number_format($item->line_total, 2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
        <div class="card-body">
          <h5 class="font-weight-bold text-uppercase small opacity-75 mb-3">Order Status</h5>
          <h2 class="font-weight-bold mb-0">
            <i class="fas fa-check-circle mr-2"></i>{{ $order->status->name }}
          </h2>
          <div class="mt-2 small opacity-75">Confirmed on
            {{ $order->confirmed_at ? $order->confirmed_at->format('M d, Y H:i') : 'N/A' }}</div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
          <h3 class="card-title font-weight-bold"><i class="fas fa-user mr-2"></i>Customer Info</h3>
        </div>
        <div class="card-body">
          <h5 class="font-weight-bold text-dark mb-1">{{ $order->customer->display_name ?? 'N/A' }}</h5>
          <p class="text-muted small mb-3"><i
              class="fas fa-envelope mr-1"></i>{{ $order->customer->email ?? 'No email' }}</p>

          <label class="text-uppercase small font-weight-bold text-muted mb-1 d-block">Shipping Address</label>
          <div class="p-2 bg-light rounded small">
            {!! nl2br(e($order->shipping_address['address'] ?? 'No address provided')) !!}
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal</span>
            <span class="font-weight-bold">{{ number_format($order->subtotal, 2) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-2 text-danger">
            <span>Discount</span>
            <span>-{{ number_format($order->discount_total, 2) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Tax</span>
            <span>{{ number_format($order->tax_total, 2) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Shipping</span>
            <span>{{ number_format($order->shipping_total, 2) }}</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="font-weight-bold text-dark mb-0">Grand Total</h5>
            <h3 class="font-weight-bold text-primary mb-0">{{ number_format($order->grand_total, 2) }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
