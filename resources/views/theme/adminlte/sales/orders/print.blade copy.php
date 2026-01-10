<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Print Order - {{ $order->order_number }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body {
      background: #f4f6f9;
      font-size: 0.9rem;
    }

    .print-container {
      max-width: 900px;
      margin: 30px auto;
      background: white;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
    }

    .invoice-box {
      padding: 40px;
    }

    @media print {
      body {
        background: white;
        margin: 0;
      }

      .print-container {
        max-width: 100%;
        margin: 0;
        box-shadow: none;
        border: 0;
      }

      .no-print {
        display: none !important;
      }
    }

    .table thead th {
      background: #f8f9fa;
      border-top: 0;
    }

    .badge {
      font-weight: 500;
      font-size: 0.8rem;
    }
  </style>
</head>

<body>

  <div class="container py-4 no-print text-center">
    <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-print mr-2"></i> Print / Save
      as PDF</button>
    <button onclick="window.close()" class="btn btn-outline-secondary px-4 shadow-sm">Close Window</button>
  </div>

  <div class="print-container">
    <div class="invoice-box">
      <div class="row mb-5">
        <div class="col-6">
          <h3 class="font-weight-bold text-dark">{{ $order->company->name }}</h3>
          <div class="text-muted">{{ $order->company->email }}</div>
          <div class="text-muted">{{ $order->company->phone }}</div>
        </div>
        <div class="col-6 text-right">
          <h2 class="text-primary font-weight-bold mb-0">SALES ORDER</h2>
          <div class="h5 text-dark mt-1">{{ $order->order_number }}</div>
          <div class="text-muted">Date: {{ $order->created_at->format('M d, Y') }}</div>
          @if ($order->status)
            <div class="mt-2"><span class="badge badge-success px-3 py-2">{{ $order->status->name }}</span></div>
          @endif
        </div>
      </div>

      <div class="row mb-5">
        <div class="col-6">
          <h6 class="font-weight-bold text-uppercase text-muted small mb-3">Bill To</h6>
          <div class="bg-light p-3 rounded">
            <strong>{{ $order->customer->display_name }}</strong><br>
            @if ($order->billingAddress)
              {{ $order->billingAddress->address_line_1 }}<br>
              @if ($order->billingAddress->address_line_2)
                {{ $order->billingAddress->address_line_2 }}<br>
              @endif
              {{ $order->billingAddress->city }} {{ $order->billingAddress->zip_code }}<br>
              {{ $order->billingAddress->country->name ?? '' }}
            @endif
          </div>
        </div>
        <div class="col-6 text-right">
          <h6 class="font-weight-bold text-uppercase text-muted small mb-3">Ship To</h6>
          <div class="bg-light p-3 rounded text-left">
            @if ($order->shippingAddress)
              <strong>{{ $order->customer->display_name }}</strong><br>
              {{ $order->shippingAddress->address_line_1 }}<br>
              @if ($order->shippingAddress->address_line_2)
                {{ $order->shippingAddress->address_line_2 }}<br>
              @endif
              {{ $order->shippingAddress->city }} {{ $order->shippingAddress->zip_code }}<br>
              {{ $order->shippingAddress->country->name ?? '' }}
            @else
              <div class="text-muted italic">Same as Billing</div>
            @endif
          </div>
        </div>
      </div>

      <table class="table table-hover mb-5">
        <thead>
          <tr>
            <th style="width: 45%">Description</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Price</th>
            <th class="text-right">Disc</th>
            <th class="text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order->items as $item)
            <tr>
              <td>
                <div class="font-weight-bold text-dark">{{ $item->product_name }}</div>
                <div class="small text-muted">{{ $item->variant_description }}</div>
              </td>
              <td class="text-center">{{ $item->quantity }}</td>
              <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
              <td class="text-right">
                {{ $item->discount_amount > 0 ? number_format($item->discount_amount, 2) : '-' }}
              </td>
              <td class="text-right font-weight-bold text-dark">{{ number_format($item->line_total, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="row justify-content-end">
        <div class="col-5">
          <div class="card bg-light border-0">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span class="font-weight-bold">{{ number_format($order->subtotal, 2) }}</span>
              </div>
              @if ($order->shipping_total > 0)
                <div class="d-flex justify-content-between mb-2">
                  <span>Shipping:</span>
                  <span>{{ number_format($order->shipping_total, 2) }}</span>
                </div>
              @endif
              @if ($order->tax_total > 0)
                <div class="d-flex justify-content-between mb-2">
                  <span>Tax:</span>
                  <span>{{ number_format($order->tax_total, 2) }}</span>
                </div>
              @endif
              <hr>
              <div class="d-flex justify-content-between text-primary h5 font-weight-bold">
                <span>Total:</span>
                <span>{{ number_format($order->grand_total, 2) }} {{ $order->currency->code ?? '' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if ($order->notes)
        <div class="mt-5 pt-4 border-top">
          <h6 class="font-weight-bold small text-uppercase text-muted">Notes / Special Instructions</h6>
          <p class="text-muted small">{{ $order->notes }}</p>
        </div>
      @endif
    </div>
  </div>

</body>

</html>
