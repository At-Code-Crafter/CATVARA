@extends('theme.adminlte.layouts.print_layout')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
@php
    // --- Configuration & Helpers ---
    $maxLinesPerPage = 42; 
    $headerHeightPg1 = 18; 
    $headerHeightPgN = 4;  
    $footerHeight    = 3;  
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

    foreach($invoice->items as $item) {
        $itemCost = 1;
        if($item->discount_amount > 0) $itemCost += 0.5; 
        // using description from invoice item which might be long
        if(strlen($item->description) > 60) $itemCost += 0.5; 

        if ( ($pages[$currentPageIndex]['lines_used'] + $itemCost) > $maxLinesPerPage ) {
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

    if ( ($pages[$currentPageIndex]['lines_used'] + $totalsBoxHeight) <= $maxLinesPerPage ) {
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
    $logo = $invoice->company->logo ? asset('storage/' . $invoice->company->logo) : asset('assets/images/logo.png'); 
@endphp

    <div id="invoicePages">
        @foreach($pages as $index => $page)
        @php $pageNo = $index + 1; @endphp
        
        <div class="invoice-page">
            <!-- Header Top -->
            <div class="inv-top {{ $page['is_first'] ? 'has-divider' : '' }}">
                <div class="inv-title">TAX INVOICE</div>

                <div class="inv-meta-block">
                    <div class="label">Invoice number</div>
                    <div class="value">{{ $invoice->invoice_number }}</div>
                </div>

                <div class="inv-meta-block inv-top-right">
                    <div class="label">Invoice total</div>
                    <div class="value">{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency->code ?? '' }}</div>
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
                        <div class="value">{{ $invoice->issued_at->format('F d, Y') }}</div>
                    </div>
                    <div class="row">
                        <div class="label">Due Date</div>
                        <div class="value">{{ $invoice->due_date ? $invoice->due_date->format('F d, Y') : '-' }}</div>
                    </div>
                </div>

                <div class="inv-additional">
                    <div class="label">
                        Additional details
                    </div>
                    @if($invoice->notes)
                    <p>
                        {{ $invoice->notes ?? '' }}
                    </p>
                    @endif
                </div>
            </div>

            <div class="inv-addresses">
                <div class="addr">
                    <h4>Bill to</h4>
                     @php
                        // Assuming invoice has addresses rel or logic. Using simplified access for now
                        $billTo = $invoice->addresses->where('type', 'BILLING')->first();
                        $shipTo = $invoice->addresses->where('type', 'SHIPPING')->first();
                    @endphp
                    <div class="lines">
                        <div class="label">
                            {{ $billTo->name ?? $invoice->customer->display_name }}
                        </div>
                        @if($billTo)
                        <div class="value">
                            {{ $billTo->address_line_1 }}
                            {{ $billTo->address_line_2 }}
                            {{ $billTo->city }} {{ $billTo->zip_code }}
                            {{ $billTo->country->name ?? '' }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="addr">
                    <h4>Ship to</h4>
                    <div class="lines">
                        <div class="label">
                                {{ $shipTo->name ?? $invoice->customer->display_name }}
                            </div>
                            @if($shipTo)
                            <div class="value">
                                {{ $shipTo->address_line_1 }}
                            {{ $shipTo->address_line_2 }}
                            {{ $shipTo->city }} {{ $shipTo->zip_code }}
                            {{ $shipTo->country->name ?? '' }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="addr right">
                    <h4>Merchant</h4>
                    <!-- Dynamic Merchant Info (Company) -->
                    <div class="lines">{{ $invoice->company->name }}
                        <p>
                            {{ $invoice->company->legal_name }}
                            {{ $invoice->company->detail->address }}
                            {{ $invoice->company->email }}
                            {{ $invoice->company->phone }}
                        </p>
                        @if($invoice->company->detail->tax_number)
                        <p>
                            TRN: {{ $invoice->company->detail->tax_number }}
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
                            {{ $item->description }}
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
                        <div class="val">{{ number_format($invoice->subtotal, 2) }}</div>
                    </div>
                    @if($invoice->discount_total > 0)
                    <div class="trow">
                        <div class="lbl">Discount</div>
                        <div class="val text-danger">-{{ number_format($invoice->discount_total, 2) }}</div>
                    </div>
                    @endif
                    <div class="trow">
                        <div class="lbl">VAT</div>
                        <div class="val">{{ number_format($invoice->tax_total, 2) }}</div>
                    </div>
                    @if($invoice->shipping_amount > 0)
                    <div class="trow">
                        <div class="lbl">Shipping</div>
                        <div class="val">{{ number_format($invoice->shipping_amount, 2) }}</div>
                    </div>
                    @endif
                    <div class="trow total">
                        <div class="lbl">Total</div>
                        <div class="val">{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency->code ?? '' }}</div>
                    </div>
                </div>
            </div>
            
            @if($invoice->notes)
            <div class="mt-4 pt-3 border-top">
                <strong>Notes:</strong> <span class="text-muted small">{{ $invoice->notes }}</span>
            </div>
            @endif
            @endif

            <!-- Footer -->
            <div class="inv-footer">
                <div class="left">Provided by: {{ $invoice->company->name }}<br>VAT ID: {{ $invoice->company->detail->tax_number ?? 'N/A' }}</div>
                <div class="right">Issued on {{ $invoice->issued_at->format('F d, Y') }}<br>Page {{ $pageNo }} of {{ $totalPages }} for {{ $invoice->invoice_number }}</div>
            </div>
        </div>
        @endforeach
    </div>
@endsection
