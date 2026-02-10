<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Label {{ $dn->delivery_note_number }}</title>
  <style>
    @page {
      size: 100mm 150mm;
      margin: 0;
    }

    body {
      margin: 0;
      padding: 5mm;
      font-family: 'Arial', sans-serif;
      width: 90mm;
      /* Fallback for screen */
      height: 140mm;
    }

    .label-container {
      display: flex;
      flex-direction: column;
      height: 100%;
      border: 2px solid #000;
      box-sizing: border-box;
      padding: 5mm;
    }

    .header {
      border-bottom: 2px solid #000;
      padding-bottom: 3mm;
      margin-bottom: 5mm;
    }

    .sender {
      font-size: 10px;
      text-transform: uppercase;
    }

    .recipient {
      flex-grow: 1;
    }

    .recipient-label {
      font-size: 10px;
      color: #666;
      text-transform: uppercase;
      margin-bottom: 2mm;
    }

    .recipient-name {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 2mm;
      text-transform: uppercase;
    }

    .recipient-address {
      font-size: 14px;
      line-height: 1.4;
    }

    .details {
      border-top: 2px solid #000;
      padding-top: 3mm;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }

    .order-ref {
      font-size: 12px;
      font-weight: bold;
    }

    .dn-number {
      font-size: 16px;
      font-weight: 900;
      margin-bottom: 2mm;
    }

    .meta {
      font-size: 10px;
      text-align: right;
    }
  </style>
</head>

<body onload="window.print()">
  <div class="label-container">
    <div class="header">
      <div class="sender">
        <strong>FROM:</strong><br>
        {{ $dn->order->company->name }}<br>
        {{ $dn->order->company->phone }}
      </div>
    </div>

    <div class="recipient">
      <div class="recipient-label">SHIP TO:</div>
      <div class="recipient-name">
        @if ($dn->order->shippingAddress)
          {{ $dn->order->shippingAddress->name ?? $dn->order->customer->display_name }}
        @else
          {{ $dn->order->customer->display_name }}
        @endif
      </div>
      <div class="recipient-address">
        @if ($dn->order->shippingAddress)
          {{ $dn->order->shippingAddress->address_line_1 }}<br>
          @if ($dn->order->shippingAddress->address_line_2)
            {{ $dn->order->shippingAddress->address_line_2 }}<br>
          @endif
          {{ $dn->order->shippingAddress->city }}
          @if ($dn->order->shippingAddress->state)
            , {{ $dn->order->shippingAddress->state->code ?? $dn->order->shippingAddress->state->name }}
          @endif
          <br>
          <strong>{{ $dn->order->shippingAddress->zip_code }}</strong><br>
          {{ $dn->order->shippingAddress->country->name ?? '' }}<br>
          Phone: {{ $dn->order->shippingAddress->phone }}
        @else
          (No shipping address provided)
        @endif
      </div>
    </div>

    <div class="details">
      <div>
        <div class="dn-number">{{ $dn->delivery_note_number }}</div>
        @if ($dn->reference_number)
          <div class="order-ref">REF: {{ $dn->reference_number }}</div>
        @endif
        <div class="order-ref">ORD: {{ $dn->order->order_number }}</div>
      </div>
      <div class="meta">
        {{ $dn->created_at->format('d/m/Y') }}<br>
        Items: {{ $dn->items->sum('quantity') }}
      </div>
    </div>
  </div>
</body>

</html>
