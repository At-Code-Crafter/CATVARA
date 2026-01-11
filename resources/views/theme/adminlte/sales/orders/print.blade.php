@extends('theme.adminlte.layouts.print_layout')

@section('title', 'Order ' . $order->order_number)

@section('content')
    @php
        // --- Configuration & Helpers ---
        $maxLinesPerPage = 42;
        $headerHeightPg1 = 18;
        $headerHeightPgN = 4;
        $footerHeight = 3;
        $totalsBoxHeight = 8;

        $pages = [];
        $currentPageIndex = 0;

        // Initialize Page 1
        $pages[$currentPageIndex] = [
            'is_first' => true,
            'items' => [],
            'lines_used' => $headerHeightPg1 + $footerHeight,
            'show_totals' => false
        ];

        foreach ($order->items as $item) {
            $itemCost = 1;
            if ($item->discount_amount > 0)
                $itemCost += 0.5;
            if (strlen($item->product_name) > 60)
                $itemCost += 0.5;

            if (($pages[$currentPageIndex]['lines_used'] + $itemCost) > $maxLinesPerPage) {
                $currentPageIndex++;
                $pages[$currentPageIndex] = [
                    'is_first' => false,
                    'items' => [],
                    'lines_used' => $headerHeightPgN + $footerHeight,
                    'show_totals' => false
                ];
            }

            $pages[$currentPageIndex]['items'][] = $item;
            $pages[$currentPageIndex]['lines_used'] += $itemCost;
        }

        if (($pages[$currentPageIndex]['lines_used'] + $totalsBoxHeight) <= $maxLinesPerPage) {
            $pages[$currentPageIndex]['show_totals'] = true;
        } else {
            $currentPageIndex++;
            $pages[$currentPageIndex] = [
                'is_first' => false,
                'items' => [],
                'lines_used' => $headerHeightPgN + $footerHeight + $totalsBoxHeight,
                'show_totals' => true
            ];
        }

        $totalPages = count($pages);
        $logo = $order->company->logo ? asset('storage/' . $order->company->logo) : asset('assets/images/logo.png'); 
    @endphp

    <div id="invoicePages">
        @foreach($pages as $index => $page)
            @php $pageNo = $index + 1; @endphp

            <div class="invoice-page">
                <!-- Header Top -->
                <div class="inv-top {{ $page['is_first'] ? 'has-divider' : '' }}">
                    <div class="inv-title">SALES ORDER</div> <!-- Changed Title -->

                    <div class="inv-meta-block">
                        <div class="label">Order number</div>
                        <div class="value">{{ $order->order_number }}</div>
                    </div>

                    <div class="inv-meta-block inv-top-right">
                        <div class="label">Order total</div>
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
                                <div class="value">{{ $order->created_at->format('F d, Y') }}</div>
                            </div>
                            <div class="row">
                                <div class="label">Valid until</div>
                                <!-- Example field for Order, fallback to Due Date -->
                                <div class="value">{{ $order->due_date ? $order->due_date->format('F d, Y') : '-' }}</div>
                            </div>
                        </div>

                        <div class="inv-additional">
                            <div class="label">
                                Additional details
                            </div>
                            {{-- @if($order->payment_term_id)
                            <p>
                                {{ $order->paymentTerm->name ?? '' }}
                            </p>
                            @endif --}}
                            @if($order->notes)
                                <p>
                                    {{ $order->notes ?? '' }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="inv-addresses">
                        <div class="addr">
                            <h4>Bill to</h4>
                            <div class="lines">
                                <div class="label">
                                    {{ $order->billingAddress->name ?? $order->customer->display_name }}
                                </div>
                                @if($order->billingAddress)
                                    <div class="value">
                                        {{ $order->billingAddress->address_line_1 }}
                                        {{ $order->billingAddress->address_line_2 }}
                                        {{ $order->billingAddress->city }} {{ $order->billingAddress->zip_code }}
                                        {{ $order->billingAddress->country->name ?? '' }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="addr">
                            <h4>Ship to</h4>
                            <div class="lines">
                                <div class="label">
                                    {{ $order->shippingAddress->name ?? $order->customer->display_name }}
                                </div>
                                @if($order->shippingAddress)
                                    <div class="value">
                                        {{ $order->shippingAddress->address_line_1 }}
                                        {{ $order->shippingAddress->address_line_2 }}
                                        {{ $order->shippingAddress->city }} {{ $order->shippingAddress->zip_code }}
                                        {{ $order->shippingAddress->country->name ?? '' }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="addr right">
                            <h4>Merchant</h4>
                            <!-- Dynamic Merchant Info (Company) -->
                            <div class="lines">{{ $order->company->name }}
                                <p>
                                    {{ $order->company->legal_name }}
                                    {{ $order->company->detail->address }}
                                    {{ $order->company->email }}
                                    {{ $order->company->phone }}
                                </p>
                                @if($order->company->detail->tax_number)
                                    <p>
                                        {{ $order->company->detail->tax_number }}
                                    </p>
                                @endif
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
                                    <td class="qty">{{ (float) $item->quantity }}</td>
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
                                <div class="val">{{ number_format($order->grand_total, 2) }} {{ $order->currency->code ?? '' }}
                                </div>
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
                    <div class="left">Provided by: {{ $order->company->name }}<br>VAT ID:
                        {{ $order->company->detail->tax_number ?? 'N/A' }}</div>
                    <div class="right">Issued on {{ $order->created_at->format('F d, Y') }}<br>Page {{ $pageNo }} of
                        {{ $totalPages }} for {{ $order->order_number }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endsection