<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quote->quote_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        .company-info h1 {
            font-size: 24px;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .company-info p {
            color: #64748b;
            font-size: 11px;
        }
        .quote-info {
            text-align: right;
        }
        .quote-info h2 {
            font-size: 28px;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .quote-info .quote-number {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 5px;
        }
        .quote-info .quote-date {
            font-size: 11px;
            color: #64748b;
        }
        .addresses {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .address-block {
            width: 48%;
        }
        .address-block h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .address-block .name {
            font-weight: bold;
            font-size: 14px;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .address-block p {
            color: #475569;
            font-size: 11px;
        }
        .validity-info {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .validity-info.expired {
            background: #fef2f2;
            border-color: #fecaca;
        }
        .validity-info span {
            font-size: 11px;
        }
        .validity-info .label {
            color: #64748b;
        }
        .validity-info .value {
            font-weight: bold;
            color: #166534;
        }
        .validity-info.expired .value {
            color: #dc2626;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        thead th:last-child {
            text-align: right;
        }
        tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        tbody td:last-child {
            text-align: right;
        }
        .item-name {
            font-weight: 600;
            color: #1e293b;
        }
        .item-variant {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-left: auto;
            width: 280px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .totals-row.grand-total {
            border-top: 2px solid #e5e7eb;
            border-bottom: none;
            padding-top: 12px;
            margin-top: 5px;
        }
        .totals-row .label {
            color: #64748b;
        }
        .totals-row .value {
            font-weight: 600;
            color: #1e293b;
        }
        .totals-row.grand-total .label {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }
        .totals-row.grand-total .value {
            font-size: 18px;
            font-weight: bold;
            color: #3b82f6;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 6px;
        }
        .notes h4 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 8px;
        }
        .notes p {
            color: #475569;
            font-size: 11px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-info">
                <h1>{{ $quote->company->name ?? 'Company Name' }}</h1>
                <p>{{ $quote->company->address ?? '' }}</p>
                <p>{{ $quote->company->phone ?? '' }} | {{ $quote->company->email ?? '' }}</p>
            </div>
            <div class="quote-info">
                <h2>Quotation</h2>
                <div class="quote-number">{{ $quote->quote_number }}</div>
                <div class="quote-date">Date: {{ $quote->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="addresses">
            <div class="address-block">
                <h3>Bill To</h3>
                <div class="name">{{ $quote->customer->display_name ?? 'N/A' }}</div>
                @if ($quote->billingAddress)
                    <p>{{ $quote->billingAddress->address_line_1 }}</p>
                    @if ($quote->billingAddress->address_line_2)
                        <p>{{ $quote->billingAddress->address_line_2 }}</p>
                    @endif
                    <p>{{ $quote->billingAddress->city ?? '' }}, {{ $quote->billingAddress->state->name ?? '' }} {{ $quote->billingAddress->zip_code ?? '' }}</p>
                    <p>{{ $quote->billingAddress->country->name ?? '' }}</p>
                    <p style="margin-top: 8px;">{{ $quote->billingAddress->email ?? $quote->customer->email }}</p>
                    <p>{{ $quote->billingAddress->phone ?? $quote->customer->phone }}</p>
                @endif
            </div>
            <div class="address-block">
                <h3>Ship To</h3>
                <div class="name">{{ $quote->customer->display_name ?? 'N/A' }}</div>
                @if ($quote->shippingAddress)
                    <p>{{ $quote->shippingAddress->address_line_1 }}</p>
                    @if ($quote->shippingAddress->address_line_2)
                        <p>{{ $quote->shippingAddress->address_line_2 }}</p>
                    @endif
                    <p>{{ $quote->shippingAddress->city ?? '' }}, {{ $quote->shippingAddress->state->name ?? '' }} {{ $quote->shippingAddress->zip_code ?? '' }}</p>
                    <p>{{ $quote->shippingAddress->country->name ?? '' }}</p>
                @endif
            </div>
        </div>

        @if ($quote->valid_until)
            @php
                $isExpired = $quote->valid_until->isPast();
            @endphp
            <div class="validity-info {{ $isExpired ? 'expired' : '' }}">
                <span>
                    <span class="label">Payment Terms:</span>
                    <span class="value">{{ $quote->paymentTerm->name ?? 'Direct' }}</span>
                </span>
                <span>
                    <span class="label">Valid Until:</span>
                    <span class="value">{{ $quote->valid_until->format('M d, Y') }}</span>
                </span>
                <span>
                    <span class="label">Currency:</span>
                    <span class="value">{{ $quote->currency->code ?? 'AED' }}</span>
                </span>
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Description</th>
                    <th class="text-center" style="width: 12%;">Qty</th>
                    <th class="text-right" style="width: 16%;">Unit Price</th>
                    <th class="text-center" style="width: 12%;">Discount</th>
                    <th class="text-right" style="width: 20%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quote->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->product_name }}</div>
                            @if ($item->variant_description)
                                <div class="item-variant">{{ $item->variant_description }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ (float) $item->quantity }}</td>
                        <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-center">
                            @if ($item->discount_amount > 0)
                                -{{ number_format((float) $item->discount_amount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right" style="font-weight: 600;">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span class="label">Subtotal</span>
                <span class="value">{{ number_format((float) $quote->subtotal, 2) }}</span>
            </div>
            @if ($quote->discount_total > 0)
                <div class="totals-row">
                    <span class="label">Discount</span>
                    <span class="value" style="color: #10b981;">-{{ number_format((float) $quote->discount_total, 2) }}</span>
                </div>
            @endif
            @if ($quote->shipping_total > 0)
                <div class="totals-row">
                    <span class="label">Shipping</span>
                    <span class="value">{{ number_format((float) $quote->shipping_total, 2) }}</span>
                </div>
            @endif
            <div class="totals-row">
                <span class="label">Tax</span>
                <span class="value">{{ number_format((float) $quote->tax_total, 2) }}</span>
            </div>
            <div class="totals-row grand-total">
                <span class="label">Grand Total</span>
                <span class="value">{{ number_format((float) $quote->grand_total, 2) }} {{ $quote->currency->code ?? '' }}</span>
            </div>
        </div>

        @if ($quote->notes)
            <div class="notes">
                <h4>Notes</h4>
                <p>{{ $quote->notes }}</p>
            </div>
        @endif

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Generated on {{ now()->format('M d, Y h:i A') }}</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
