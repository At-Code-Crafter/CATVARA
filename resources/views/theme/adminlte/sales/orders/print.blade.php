<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .invoice-container {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 10mm 15mm;
            background: #fff;
            page-break-after: avoid;
        }

        /* Header */
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            margin-bottom: 30px;
        }

        /* Bill To Section */
        .bill-to-section {
            margin-bottom: 25px;
        }

        .bill-to-label {
            font-weight: bold;
            color: #000;
            margin-bottom: 15px;
        }

        .bill-to-content {
            line-height: 1.5;
        }

        .company-name {
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
        }

        .address-line {
            color: #000;
        }

        /* Order Details Section */
        .order-details {
            display: flex;
            margin-bottom: 20px;
        }

        .order-details-left {
            width: 50%;
        }

        .order-details-right {
            width: 50%;
        }

        .detail-row {
            display: flex;
            margin-bottom: 3px;
        }

        .detail-label {
            width: 140px;
            color: #000;
        }

        .detail-value {
            color: #000;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .items-table th {
            background: #aeaaaa;
            color: #050000;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            font-size: 12px;
            border: 1px solid #000;
        }

        .items-table td {
            padding: 6px;
            border: 1px solid #000;
            font-size: 12px;
            color: #000;
            vertical-align: middle;
        }

        .items-table .col-item-no {
            width: 180px;
        }

        .items-table .col-description {
            width: 200px;
        }

        .items-table .col-currency {
            width: 25px;
            text-align: center;
        }

        .items-table .col-price {
            width: 60px;
            text-align: right;
        }

        .items-table .col-quantity {
            width: 70px;
            text-align: center;
        }

        .items-table .col-amount-currency {
            width: 25px;
            text-align: center;
        }

        .items-table .col-amount {
            width: 60px;
            text-align: right;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table .empty-row td {
            height: 25px;
        }

        .items-table .totals-label {
            text-align: right;
            font-weight: normal;
        }

        .items-table .totals-label-bold {
            text-align: right;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 14px;
            color: #b8860b;
            font-weight: bold;
            font-style: italic;
        }

        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .invoice-container {
                margin: 0;
                padding: 10mm 15mm;
                width: 100%;
                min-height: auto;
            }

            @page {
                size: A4;
                margin: 5mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-title">Invoice</div>

        <!-- Bill To Section -->
        <div class="bill-to-section">
            <div class="bill-to-label">Bill To:</div>
            <div class="bill-to-content">
                <div class="company-name">{{ $order->customer->display_name ?? 'N/A' }}</div>
                @if ($order->billing_address)
                    @if (!empty($order->billing_address['address_line_1']))
                        <div class="address-line">{{ $order->billing_address['address_line_1'] }}</div>
                    @endif
                    @if (!empty($order->billing_address['address_line_2']))
                        <div class="address-line">{{ $order->billing_address['address_line_2'] }}</div>
                    @endif
                    @if (!empty($order->billing_address['city']))
                        <div class="address-line">{{ $order->billing_address['city'] }}</div>
                    @endif
                    @if (!empty($order->billing_address['postal_code']))
                        <div class="address-line">{{ $order->billing_address['postal_code'] }}</div>
                    @endif
                    @if (!empty($order->billing_address['country']))
                        <div class="address-line">{{ $order->billing_address['country'] }}</div>
                    @endif
                @else
                    <div class="address-line">{{ $order->customer->address ?? '' }}</div>
                    <div class="address-line">{{ $order->customer->postal_code ?? '' }}</div>
                    <div class="address-line">{{ $order->customer->country->name ?? '' }}</div>
                @endif
            </div>
        </div>

        <!-- Order Details Section -->
        <div class="order-details">
            <div class="order-details-left">
                <div class="detail-row">
                    <span class="detail-label">Order Tracking No:</span>
                    <span class="detail-value">{{ $order->uuid }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value">{{ ($order->confirmed_at ?? $order->created_at)->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Delivery Method:</span>
                    <span class="detail-value">{{ $order->shipping_method ?? 'Standard' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">BOXES</span>
                    <span class="detail-value">{{ count($order->items) }}</span>
                </div>
            </div>
            <div class="order-details-right">
                <div class="detail-row">
                    <span class="detail-label">Invoice No:</span>
                    <span class="detail-value">{{ $order->order_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value">{{ $order->paymentTerm ? $order->confirmed_at?->addDays($order->paymentTerm->due_in_days)->format('d/m/Y') : 'On Receipt' }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-item-no">Item No:</th>
                    <th class="col-description">Description</th>
                    <th class="col-currency" colspan="2">Unit Price</th>
                    <th class="col-quantity text-center">Quantity</th>
                    <th class="col-amount-currency" colspan="2">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->variant_description ?? '' }}</td>
                        <td class="text-center">{{ $order->currency->symbol ?? '£' }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">{{ $order->currency->symbol ?? '£' }}</td>
                        <td class="text-right">{{ number_format($item->unit_price * $item->quantity - ($item->discount_amount ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">No items</td>
                    </tr>
                @endforelse

                <!-- Empty rows for padding -->
                <tr class="empty-row">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="empty-row">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="empty-row">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <!-- Totals rows -->
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-center">{{ $order->items->sum('quantity') }}</td>
                    <td class="text-center">{{ $order->currency->symbol ?? '£' }}</td>
                    <td class="text-right">{{ number_format($order->subtotal - $order->discount_total, 2) }}</td>
                </tr>
                @if ($order->shipping_total > 0)
                    <tr>
                        <td colspan="4" class="totals-label">DELIVERY CHARGES</td>
                        <td></td>
                        <td class="text-center">{{ $order->currency->symbol ?? '£' }}</td>
                        <td class="text-right">{{ number_format($order->shipping_total, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="4" class="totals-label-bold">AMOUNT</td>
                    <td></td>
                    <td class="text-center">{{ $order->currency->symbol ?? '£' }}</td>
                    <td class="text-right">{{ number_format($order->grand_total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            Thank You For Business!
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print Invoice</button>
    </div>
</body>

</html>
