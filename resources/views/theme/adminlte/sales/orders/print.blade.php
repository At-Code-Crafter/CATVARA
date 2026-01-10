<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Invoice</title>
  <link rel="stylesheet" href="{{ asset('pos/assets/css/invoice.css') }}" />
@php
    // --- Configuration & Helpers ---
    $maxLinesPerPage = 42; // Total adjustable lines of "height" per page
    $headerHeightPg1 = 18; // Logo, Addresses, Dates area cost
    $headerHeightPgN = 4;  // Simple header cost
    $footerHeight    = 3;  // Footer cost
    $totalsBoxHeight = 8;  // Totals box cost

    $pages = [];
    $currentPageIndex = 0;
    
    // Initialize Page 1
    $pages[$currentPageIndex] = [
        'is_first' => true,
        'items' => [],
        'lines_used' => $headerHeightPg1 + $footerHeight,
        'show_totals' => false
    ];

    // --- Item Distribution Logic ---
    foreach($order->items as $item) {
        // Calculate item "cost" (1 line base + 1 if discount + 1 if description wraps? simplified)
        // If you want robust text wrapping calculation, you need char counting. 
        // For now, assuming standard 1 line = 1 unit.
        $itemCost = 1;
        if($item->discount_amount > 0) $itemCost += 0.5; // Discount sub-row
        if(strlen($item->product_name) > 60) $itemCost += 0.5; // Wrap guess

        // Check capacity
        if ( ($pages[$currentPageIndex]['lines_used'] + $itemCost) > $maxLinesPerPage ) {
            // Page full, start new page
            $currentPageIndex++;
            $pages[$currentPageIndex] = [
                'is_first' => false,
                'items' => [],
                'lines_used' => $headerHeightPgN + $footerHeight,
                'show_totals' => false
            ];
        }

        // Add item
        $pages[$currentPageIndex]['items'][] = $item;
        $pages[$currentPageIndex]['lines_used'] += $itemCost;
    }

    // --- Totals Section Logic ---
    // Check if totals fit on the current last page
    if ( ($pages[$currentPageIndex]['lines_used'] + $totalsBoxHeight) <= $maxLinesPerPage ) {
        $pages[$currentPageIndex]['show_totals'] = true;
    } else {
        // Create a final page just for totals (and maybe overflow items if logic was tighter, but here just totals)
        $currentPageIndex++;
        $pages[$currentPageIndex] = [
            'is_first' => false,
            'items' => [], // Empty items
            'lines_used' => $headerHeightPgN + $footerHeight + $totalsBoxHeight,
            'show_totals' => true
        ];
    }
    
    $totalPages = count($pages);
    $logo = $order->company->logo ? asset('storage/' . $order->company->logo) : asset('assets/images/logo.png'); // Replace with dynamic company logo if available
@endphp

<body>
  <div class="inv-screen">
    <div id="invoicePages">
        @foreach($pages as $index => $page)
        @php $pageNo = $index + 1; @endphp
        
        <div class="invoice-page">
            <!-- Header Top -->
            <div class="inv-top {{ $page['is_first'] ? 'has-divider' : '' }}">
                <div class="inv-title">INVOICE</div>

                <div class="inv-meta-block">
                    <div class="label">Invoice number</div>
                    <div class="value">{{ $order->order_number }}</div>
                </div>

                <div class="inv-meta-block inv-top-right">
                    <div class="label">Invoice total</div>
                    <div class="value">{{ number_format($order->grand_total, 2) }} {{ $order->currency->code ?? '' }}</div>
                </div>
            </div>

            <!-- Page 1 Specific Content -->
            @if($page['is_first'])
            <div class="inv-mid">
                <div class="inv-logo-wrap">
                    <img class="inv-logo" src="{{ $logo }}" alt="Logo" />
                </div>

                <div class="inv-dates">
                    <div class="row">
                        <div class="label">Date of issue</div>
                        <div class="value">{{ $order->created_at->format('d F Y') }}</div>
                    </div>
                    <div class="row">
                        <div class="label">Date of supply</div>
                         <!-- Assuming supply date is same or exists, falling back to created -->
                        <div class="value">{{ $order->created_at->format('d F Y') }}</div>
                    </div>
                </div>

                <div class="inv-additional">
                    <!-- Optional: Payment Terms or other info -->
                    {{ $order->paymentTerm->name ?? '' }}
                </div>
            </div>

            <div class="inv-addresses">
                <div class="addr">
                    <h4>Bill to</h4>
                    <div class="lines">{{ $order->billingAddress->name ?? $order->customer->display_name }}
@if($order->billingAddress)
{{ $order->billingAddress->address_line_1 }}
{{ $order->billingAddress->address_line_2 }}
{{ $order->billingAddress->city }} {{ $order->billingAddress->zip_code }}
{{ $order->billingAddress->country->name ?? '' }}
@endif</div>
                </div>

                <div class="addr">
                    <h4>Ship to</h4>
                    <div class="lines">{{ $order->shippingAddress->name ?? $order->customer->display_name }}
@if($order->shippingAddress)
{{ $order->shippingAddress->address_line_1 }}
{{ $order->shippingAddress->address_line_2 }}
{{ $order->shippingAddress->city }} {{ $order->shippingAddress->zip_code }}
{{ $order->shippingAddress->country->name ?? '' }}
@endif</div>
                </div>

                <div class="addr right">
                    <h4>Merchant</h4>
                    <!-- Dynamic Merchant Info (Company) -->
                    <div class="lines">{{ $order->company->name }}
{{ $order->company->email }}
{{ $order->company->phone }}
{{ $order->company->vat_number ?? '' }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Items Table -->
            @if(count($page['items']) > 0)
            <table class="inv-table">
                <colgroup>
                    <col style="width:70mm" />
                    <col style="width:10mm" />
                    <col style="width:23mm" />
                    <col style="width:22mm" />
                    <col style="width:26mm" />
                </colgroup>
                <thead>
                    <tr>
                        <th class="desc">Description</th>
                        <th class="qty">Quantity</th>
                        <th class="unit">Unit price</th>
                        <th class="vat">VAT rate</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($page['items'] as $item)
                    <tr>
                        <td class="desc">
                            {{ $item->product_name }}
                            @if($item->variant_description)
                            <div style="color:#6b6b6b;font-size:13px;margin-top:4px;">{{ $item->variant_description }}</div>
                            @endif
                        </td>
                        <td class="qty">{{ (float)$item->quantity }}</td>
                        <td class="unit">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="vat">{{ $item->tax_rate ?? '0' }}%</td>
                        <td class="amount">
                            {{ number_format($item->line_total, 2) }}
                            @if($item->discount_amount > 0)
                            <br><span style="color:#444">-{{ number_format($item->discount_amount, 2) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <!-- Totals Section -->
            @if($page['show_totals'])
            <div class="totals-area">
                <div class="totals">
                    <div class="trow">
                        <div class="lbl">Subtotal</div>
                        <div class="val">{{ number_format($order->subtotal, 2) }}</div>
                    </div>
                    @if($order->discount_total > 0)
                    <div class="trow">
                        <div class="lbl">Discount</div>
                        <div class="val text-danger">-{{ number_format($order->discount_total, 2) }}</div>
                    </div>
                    @endif
                    <div class="trow">
                        <div class="lbl">VAT</div>
                        <div class="val">{{ number_format($order->tax_total, 2) }}</div>
                    </div>
                    @if($order->shipping_total > 0)
                    <div class="trow">
                        <div class="lbl">Shipping</div>
                        <div class="val">{{ number_format($order->shipping_total, 2) }}</div>
                    </div>
                    @endif
                    <div class="trow total">
                        <div class="lbl">Total</div>
                        <div class="val">{{ number_format($order->grand_total, 2) }} {{ $order->currency->code ?? '' }}</div>
                    </div>
                </div>
            </div>
            
            @if($order->notes)
            <div class="mt-4 pt-3 border-top">
                <strong>Notes:</strong> <span class="text-muted small">{{ $order->notes }}</span>
            </div>
            @endif
            @endif

            <!-- Footer -->
            <div class="inv-footer">
                <div class="left">Provided by: {{ $order->company->name }}<br>VAT ID: {{ $order->company->detail->tax_number ?? 'N/A' }}</div>
                <div class="right">Issued on {{ $order->created_at->format('d F Y') }}<br>Page {{ $pageNo }} of {{ $totalPages }} for {{ $order->order_number }}</div>
            </div>
        </div>
        @endforeach
    </div>
  </div>

  <script>
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('autoprint') === '1') {
          setTimeout(() => window.print(), 300);
      }
  </script>
</body>
</html>
