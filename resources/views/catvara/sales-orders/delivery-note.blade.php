@extends('catvara.layouts.print')

@section('title', 'Delivery Note ' . $dn->delivery_note_number)

@php
    $company = $dn->order->company;
    $order = $dn->order;

    // Build items list merged with box info
    // Group box items by order_item_id for quick lookup
    $boxItemMap = $dn->boxItems->groupBy('order_item_id');

    // Build a flat list: each row = one DN item, with box number from order_item_boxes
    // If an item appears in multiple boxes, show separate rows per box assignment
    $rows = collect();
    $boxTotals = []; // box_number => running total

    foreach ($dn->boxItems->sortBy('box_number') as $bi) {
        $orderItem = $bi->orderItem;
        if (!$orderItem) {
            continue;
        }

        $lineAmount = (float) $bi->quantity * (float) $orderItem->unit_price;
        $boxNum = $bi->box_number;

        if (!isset($boxTotals[$boxNum])) {
            $boxTotals[$boxNum] = 0;
        }
        $boxTotals[$boxNum] += $lineAmount;

        $rows->push([
            'name' => $orderItem->product_name,
            'variant' => $orderItem->variant_description,
            'quantity' => (float) $bi->quantity,
            'box_number' => $boxNum,
            'amount' => $lineAmount,
        ]);
    }

    // If no box items exist, fall back to DN items without box info
    if ($rows->isEmpty()) {
        foreach ($dn->items as $dnItem) {
            $orderItem = $dnItem->orderItem;
            if (!$orderItem) {
                continue;
            }
            $rows->push([
                'name' => $orderItem->product_name,
                'variant' => $orderItem->variant_description,
                'quantity' => (float) $dnItem->quantity,
                'box_number' => null,
                'amount' => (float) $dnItem->quantity * (float) $orderItem->unit_price,
            ]);
        }
    }

    // For the "Remark" and "Box Amount" columns:
    // Show box number on first occurrence of each box, and box total only on first row of each box
    $seenBoxes = [];
@endphp

@section('content')
    <div class="invoice-container" id="dn-content">
        <style>
            .dn-container {
                padding: 5mm;
                font-family: Arial, sans-serif;
                font-size: 14px;
                color: #333;
            }

            .dn-header {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .dn-logo {
                text-align: center;
            }

            .dn-logo-text {
                font-size: 18px;
                font-weight: bold;
                letter-spacing: 1px;
            }

            .dn-company-info {
                font-size: 12px;
                line-height: 1.4;
                text-align: center;
                margin-bottom: 10px;
                color: #000;
            }

            .dn-meta {
                width: 100%;
            }

            .dn-meta-item {
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                font-size: 14px;
            }

            .dn-meta-item .label {
                font-weight: bold;
                font-size: 14px;
                margin-right: 5px;
            }

            .dn-deliver-to {
                margin-bottom: 20px;
                margin-top: 10px;
            }

            .dn-deliver-to p {
                font-size: 14px;
                line-height: 1.5;
                margin: 0;
            }

            .dn-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
                margin: 20px 0;
            }

            .dn-table th {
                border: 1px solid #999;
                padding: 6px;
                font-weight: bold;
                text-align: center;
                font-size: 14px;
            }

            .dn-table th:first-child {
                text-align: left;
            }

            .dn-table td {
                border: 1px solid #999;
                padding: 6px;
                vertical-align: top;
                color: #000;
            }

            .dn-table .col-desc {
                width: 40%;
            }

            .dn-table .col-qty {
                width: 8%;
                text-align: center;
            }

            .dn-table .col-remark {
                width: 12%;
            }

            .dn-table .col-weight {
                width: 10%;
                text-align: center;
            }

            .dn-table .col-amount {
                width: 13%;
                text-align: right;
            }

            .dn-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .dn-footer {
                margin-top: 30px;
                padding: 20px 0;
                font-size: 12px;
                line-height: 1.8;
                page-break-inside: avoid;
                color: #000;
            }

            .dn-footer-spacing {
                height: 25px;
            }

            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            @media print {
                @page {
                    margin: 10mm;
                    size: A4;
                }

                body {
                    background: none;
                    margin: 0;
                    padding: 0;
                }

                .dn-container {
                    margin: 0;
                    padding: 10mm;
                    width: 100%;
                }
            }
        </style>

        <div class="dn-container">
            {{-- Header --}}
            <div class="dn-header">
                <div class="dn-logo">
                    @if ($company->logo)
                        <img src="{{ storage_url($company->logo) }}" style="max-height: 60px; margin-bottom: 5px;">
                    @else
                        <div class="dn-logo-text">{{ $company->name }}</div>
                    @endif
                </div>

                @if ($company->legal_name)
                    <div style="font-weight: bold; margin-bottom: 3px;">{{ $company->legal_name }}</div>
                @endif

                <div class="dn-company-info">
                    {{ $company->detail?->address ?? '' }}
                </div>

                <div class="dn-meta">
                    <div class="dn-meta-item">
                        <div class="label">DELIVERY NOTE:</div>
                        <div>{{ $dn->delivery_note_number }}</div>
                    </div>
                    <div class="dn-meta-item">
                        <div class="label">DATE:</div>
                        <div>{{ $dn->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Deliver To --}}
            <div class="dn-deliver-to">
                <div style="font-weight: bold; margin-bottom: 5px;">Deliver To</div>
                <p>
                    {{ $order->customer->display_name ?? 'N/A' }}<br>
                    @if ($order->shippingAddress)
                        {!! $order->shippingAddress->render() !!}
                    @elseif ($order->billingAddress)
                        {!! $order->billingAddress->render() !!}
                    @endif
                </p>
            </div>

            {{-- Items Table --}}
            <table class="dn-table">
                <thead>
                    <tr>
                        <th class="col-desc">Description</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-remark">Remark</th>
                        <th class="col-weight">Weight</th>
                        <th class="col-amount">Box Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php
                            $isFirstOfBox = false;
                            $boxAmountDisplay = '';
                            $remarkDisplay = '';

                            if ($row['box_number']) {
                                if (!in_array($row['box_number'], $seenBoxes)) {
                                    $isFirstOfBox = true;
                                    $seenBoxes[] = $row['box_number'];
                                    $remarkDisplay = 'BOX NO.' . $row['box_number'];
                                    $boxAmountDisplay = number_format($boxTotals[$row['box_number']] ?? 0, 2);
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                {{ $row['name'] }}
                                @if ($row['variant'])
                                    [{{ $row['variant'] }}]
                                @endif
                            </td>
                            <td class="text-center">{{ (int) $row['quantity'] }}</td>
                            <td>{{ $remarkDisplay }}</td>
                            <td class="text-center">-</td>
                            <td class="text-right">{{ $boxAmountDisplay }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Footer --}}
            <div class="dn-footer">
                <div>
                    <strong>DELIVERY SERVICE:</strong> {{ $company->name }} [SELF]
                </div>
                <div>
                    <strong>VEHICLE NO.:</strong> {{ $dn->vehicle_number ?? '___________________' }}
                </div>
                <div>
                    <strong>DRIVER NAME:</strong> {{ $dn->reference_number ?? '___________________' }}
                </div>
                <div class="dn-footer-spacing"></div>
                <div>
                    <strong>RECEIVER NAME:</strong> ___________________________________
                </div>
                <div class="dn-footer-spacing"></div>
                <div>
                    <strong>RECEIVER SIGN:</strong> ___________________________________
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function generatePDF(mode) {
            mode = mode || 'save';
            const el = document.getElementById('dn-content');
            if (!el) {
                alert('Nothing to export');
                return;
            }

            const filename = 'DeliveryNote_{{ $dn->delivery_note_number }}.pdf';

            const worker = html2pdf().set({
                margin: [5, 0],
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1.0
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    scrollY: 0
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            }).from(el);

            if (mode === 'print') {
                worker.toPdf().get('pdf').then(function(pdf) {
                    const blob = pdf.output('blob');
                    const url = URL.createObjectURL(blob);
                    window.open(url, '_self');
                });
            } else {
                worker.save();
            }
        }
    </script>
@endsection
