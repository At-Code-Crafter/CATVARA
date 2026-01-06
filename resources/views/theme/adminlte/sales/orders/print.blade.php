<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      color: #333;
      font-size: 13px;
      line-height: 1.4;
      margin: 0;
      padding: 0;
    }

    .invoice-container {
      max-width: 800px;
      margin: auto;
      padding: 40px;
    }

    /* Parent Table for Printing */
    .print-layout {
      width: 100%;
      border-collapse: collapse;
    }

    /* Top Header */
    .top-table {
      width: 100%;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }

    .invoice-title {
      font-size: 32px;
      font-weight: bold;
    }

    .header-label {
      font-size: 11px;
      font-weight: bold;
      color: #666;
      display: block;
    }

    .header-value {
      font-size: 14px;
      font-weight: normal;
    }

    /* Logo and Date Section */
    .logo-section {
      width: 100%;
      border-bottom: 1px solid #ddd;
      margin-bottom: 20px;
    }

    .logo-text {
      font-size: 24px;
      font-weight: bold;
      text-transform: uppercase;
      line-height: 1;
    }

    .logo-distro {
      color: #E91E63;
      display: block;
    }

    /* Pinkish color from design */
    .additional-details {
      background-color: #f4f4f4;
      padding: 15px;
      width: 35%;
      vertical-align: top;
    }

    /* Address Grid */
    .address-table {
      width: 100%;
      margin-bottom: 40px;
    }

    .address-col {
      width: 33%;
      vertical-align: top;
    }

    .address-label {
      font-weight: bold;
      margin-bottom: 5px;
      display: block;
    }

    /* Items Table */
    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .items-table th {
      text-align: left;
      border-bottom: 1px solid #000;
      padding: 10px 0;
      font-size: 12px;
    }

    .items-table td {
      padding: 12px 0;
      border-bottom: 1px solid #eee;
      vertical-align: top;
    }

    .text-right {
      text-align: right;
    }

    .discount {
      font-style: italic;
      color: #555;
      display: block;
      font-size: 12px;
      margin-top: 2px;
    }

    /* Summary Section */
    .summary-wrapper {
      width: 100%;
      margin-top: 20px;
      page-break-inside: avoid;
    }

    .summary-table {
      width: 45%;
      float: right;
      border-collapse: collapse;
    }

    .summary-table td {
      padding: 8px 0;
      border-bottom: 1px solid #eee;
    }

    .total-row {
      font-weight: bold;
      font-size: 15px;
      border-bottom: 2px solid #000 !important;
    }

    .footer-table {
      width: 100%;
      margin-top: 50px;
      font-size: 11px;
      color: #666;
      border-top: 1px solid #eee;
      padding-top: 10px;
    }

    @media print {
      .no-print {
        display: none;
      }

      .invoice-container {
        max-width: 100%;
        padding: 0;
      }

      thead {
        display: table-header-group;
      }

      tfoot {
        display: table-footer-group;
      }

      body {
        margin: 0;
        padding: 0;
      }

      @page {
        margin: 15mm;
      }
    }
  </style>
</head>

<body>

  <div class="invoice-container">
    <table class="print-layout">
      <thead>
        <tr>
          <td>
            <table class="top-table">
              <tr>
                <td style="width: 50%;"><span class="invoice-title">INVOICE</span></td>
                <td style="width: 25%;">
                  <span class="header-label">Invoice number</span>
                  <span class="header-value">#{{ $order->order_number }}</span>
                </td>
                <td style="width: 25%;" class="text-right">
                  <span class="header-label">Invoice total</span>
                  <span
                    class="header-value">{{ $order->currency->symbol ?? '£' }}{{ number_format($order->grand_total, 2) }}</span>
                </td>
              </tr>
            </table>

            <table class="logo-section">
              <tr>
                <td style="padding-bottom: 20px;">
                  <div class="logo-text">
                    @php $names = explode(' ', $order->company->name, 2); @endphp
                    {{ $names[0] }}<br>
                    <span class="logo-distro">{{ $names[1] ?? '' }}</span>
                  </div>
                </td>
                <td style="vertical-align: top;">
                  <span class="header-label">Date of issue</span>
                  <span
                    class="header-value">{{ ($order->confirmed_at ?? $order->created_at)->format('F d, Y') }}</span><br><br>
                  <span class="header-label">Date of supply</span>
                  <span class="header-value">{{ ($order->confirmed_at ?? $order->created_at)->format('F d, Y') }}</span>
                </td>
                <td class="additional-details">
                  <span class="header-label">Additional details</span>
                  <span class="header-value small text-muted">ID: {{ $order->uuid }}</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </thead>

      <tbody>
        <tr>
          <td>
            <table class="address-table">
              <tr>
                <td class="address-col">
                  <span class="address-label">Bill to</span>
                  @if ($order->billing_address)
                    {!! nl2br(e($order->billing_address['contact_name'] ?? ($order->customer->display_name ?? ''))) !!}<br>
                    @if (!empty($order->billing_address['address_line_1']))
                      {!! nl2br(e($order->billing_address['address_line_1'])) !!}<br>
                    @endif
                    @if (!empty($order->billing_address['address_line_2']))
                      {!! nl2br(e($order->billing_address['address_line_2'])) !!}<br>
                    @endif
                    {{ $order->billing_address['city'] ?? '' }} {{ $order->billing_address['postal_code'] ?? '' }}<br>
                    {{ $order->billing_address['country'] ?? '' }}
                  @else
                    {{ $order->customer->display_name ?? 'N/A' }}<br>
                    {{ $order->customer->email ?? '' }}
                  @endif
                </td>
                <td class="address-col">
                  <span class="address-label">Ship to</span>
                  @if ($order->shipping_address)
                    {!! nl2br(e($order->shipping_address['contact_name'] ?? ($order->customer->display_name ?? ''))) !!}<br>
                    @if (!empty($order->shipping_address['address_line_1']))
                      {!! nl2br(e($order->shipping_address['address_line_1'])) !!}<br>
                    @endif
                    @if (!empty($order->shipping_address['address_line_2']))
                      {!! nl2br(e($order->shipping_address['address_line_2'])) !!}<br>
                    @endif
                    {{ $order->shipping_address['city'] ?? '' }}
                    {{ $order->shipping_address['postal_code'] ?? '' }}<br>
                    {{ $order->shipping_address['country'] ?? '' }}
                  @else
                    <em>Same as billing</em>
                  @endif
                </td>
                <td class="address-col text-right">
                  <span class="address-label">Merchant</span>
                  <strong>{{ $order->company->name }}</strong><br>
                  {!! nl2br(e($order->company->address)) !!}<br>
                  {{ $order->company->email }}<br>
                  {{ $order->company->tax_number }}
                </td>
              </tr>
            </table>

            <table class="items-table">
              <thead>
                <tr>
                  <th style="width: 50%;">Description</th>
                  <th style="width: 10%;" class="text-right">Quantity</th>
                  <th style="width: 15%;" class="text-right">Unit price</th>
                  <th style="width: 10%;" class="text-right">VAT rate</th>
                  <th style="width: 15%;" class="text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($order->items as $item)
                  <tr>
                    <td>
                      {{ $item->product_name }}
                      @if ($item->variant_description)
                        <br><small class="text-muted">{{ $item->variant_description }}</small>
                      @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">
                      {{ $order->currency->symbol ?? '£' }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->tax_rate, 0) }}%</td>
                    <td class="text-right">
                      {{ $order->currency->symbol ?? '£' }}{{ number_format($item->unit_price * $item->quantity, 2) }}
                      @if ($item->discount_amount > 0)
                        <span
                          class="discount">-{{ $order->currency->symbol ?? '£' }}{{ number_format($item->discount_amount, 2) }}</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>

            <div class="summary-wrapper">
              <div style="float: left; width: 50%; font-size: 11px; color: #666;">
                <strong>Additional Comments:</strong><br>
                {{ $order->notes ?: 'None' }}
              </div>
              <table class="summary-table">
                <tr>
                  <td>Subtotal (Net)</td>
                  <td class="text-right">
                    {{ $order->currency->symbol ?? '£' }}{{ number_format($order->subtotal - $order->discount_total, 2) }}
                  </td>
                </tr>
                <tr>
                  <td>VAT (20%)</td>
                  <td class="text-right">
                    {{ $order->currency->symbol ?? '£' }}{{ number_format($order->tax_total - ($order->shipping_tax_total ?? 0), 2) }}
                  </td>
                </tr>
                @if ($order->shipping_total > 0)
                  <tr>
                    <td>Shipping</td>
                    <td class="text-right">
                      {{ $order->currency->symbol ?? '£' }}{{ number_format($order->shipping_total, 2) }}</td>
                  </tr>
                  <tr>
                    <td>Shipping VAT (20%)</td>
                    <td class="text-right">
                      {{ $order->currency->symbol ?? '£' }}{{ number_format($order->shipping_tax_total, 2) }}</td>
                  </tr>
                @endif
                <tr class="total-row">
                  <td>Total</td>
                  <td class="text-right">
                    {{ $order->currency->symbol ?? '£' }}{{ number_format($order->grand_total, 2) }}</td>
                </tr>
              </table>
              <div style="clear: both;"></div>
            </div>
          </td>
        </tr>
      </tbody>

      <tfoot>
        <tr>
          <td>
            <table class="footer-table">
              <tr>
                <td>
                  Provided by: {{ $order->company->name }}<br>
                  VAT ID: {{ $order->company->tax_number }}
                </td>
                <td class="text-right">
                  Issued on {{ ($order->confirmed_at ?? $order->created_at)->format('F d, Y') }}<br>
                  <span class="page-info">Invoiced by {{ $order->company->name }}</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="no-print" style="text-align: center; margin: 20px;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print
      Invoice</button>
  </div>

</body>

</html>
