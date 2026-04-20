@extends('catvara.layouts.print')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
    <div class="invoice-container" id="invoice-content">
        {{-- Header (hidden in HTML, rendered by JS on every PDF page) --}}
        <div class="header-top">
            <div class="invoice-title">INVOICE</div>
            <div class="meta-item">
                <div class="label">Invoice number</div>
                <div class="value">{{ $invoice->invoice_number }}</div>
            </div>
            <div class="meta-item text-right">
                <div class="label">Invoice total</div>
                <div class="value">{{ $invoice->currency->symbol }}{{ number_format($invoice->grand_total, 2) }}</div>
            </div>
        </div>

        {{-- Brand Row: Logo, Dates, Additional Details --}}
        <div class="brand-row">
            <div class="brand-block">
                @if ($invoice->company->logo)
                    <img src="{{ storage_url($invoice->company->logo) }}" alt="{{ $invoice->company->name }}">
                @else
                    <div style="font-size: 20px; font-weight: 700; color: #333;">{{ $invoice->company->name }}</div>
                @endif
            </div>
            <div class="dates-block">
                <div class="date-item">
                    <div class="label">Date of issue</div>
                    <div class="value">
                        {{ $invoice->issued_at ? $invoice->issued_at->format('F d, Y') : $invoice->created_at->format('F d, Y') }}
                    </div>
                </div>
                <div class="date-item">
                    <div class="label">Date of supply</div>
                    <div class="value">
                        {{ $invoice->issued_at ? $invoice->issued_at->format('F d, Y') : $invoice->created_at->format('F d, Y') }}
                    </div>
                </div>
            </div>
            <div class="additional-details-block">
                <div class="label text-right">Additional details</div>
                @if ($invoice->payment_term_name)
                    <div class="text-right" style="font-size: 11px; color: #333; margin-top: 8px;">
                        <strong>Payment Terms:</strong> {{ $invoice->payment_term_name }}
                        @if ($invoice->payment_due_days)
                            (Due in {{ $invoice->payment_due_days }} days)
                        @endif
                    </div>
                @endif
                @if ($invoice->order?->order_number)
                    <div class="text-right" style="font-size: 11px; color: #333; margin-top: 8px;">
                        <strong>PO Number:</strong> {{ $invoice->order->order_number }}
                    </div>
                @endif
                @if ($invoice->notes)
                    <div class="text-right" style="font-size: 11px; color: #555; margin-top: 8px;">{{ $invoice->notes }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Addresses Row --}}
        @php
            $billTo = $invoice->billingAddress;
            $shipTo = $invoice->shippingAddress;
        @endphp
        <div class="address-grid">
            <div class="address-col">
                <div class="label">Bill to</div>
                <p>
                    {{ $billTo->name ?? ($invoice->customer->legal_name ?? $invoice->customer->display_name) }}<br>
                    {!! $billTo?->render() !!}
                    @if ($billTo?->email)
                        <br>{{ $billTo->email }}
                    @endif
                </p>
            </div>
            <div class="address-col">
                <div class="label">Ship to</div>
                <p>
                    {{ $shipTo->name ?? $invoice->customer->display_name }}<br>
                    {!! $shipTo?->render() ?? 'Same as Billing Address' !!}
                </p>
            </div>
            <div class="address-col text-right">
                <div class="label">Merchant</div>
                <p>
                    {{ $invoice->company->name }}<br>
                    @if ($invoice->company->detail?->address)
                        {{ $invoice->company->detail->address }}<br>
                    @endif
                    @if ($invoice->company->detail?->email)
                        {{ $invoice->company->detail->email }}<br>
                    @endif
                    @if ($invoice->company->detail?->tax_number)
                        <br>{{ $invoice->company->detail->tax_number }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="table-grid">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-left">Description</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit price</th>
                        <th class="text-right">VAT rate</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td>
                                {{ $item->product_name }}
                                @if ($item->variant_description && !($hideVariants ?? false))
                                    <br><span style="font-size: 11px; color: #aaa;">{{ $item->variant_description }}</span>
                                @endif
                            </td>
                            <td class="text-right">{{ (int) $item->quantity }}</td>
                            <td class="text-right">{{ money($item->unit_price, $invoice->currency->code) }}</td>
                            <td class="text-right">{{ number_format($item->tax_rate, 0) }}%</td>
                            <td class="text-right">
                                {{ money($item->line_total, $invoice->currency->code) }}
                                @if ($item->discount_amount > 0)
                                    <br><span
                                        class="discount">-{{ money($item->discount_amount, $invoice->currency->code) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="totals-container">
            <div class="totals-box">
                <div class="total-line">
                    <span>Subtotal</span><span>{{ money($invoice->subtotal, $invoice->currency->code) }}</span>
                </div>
                @if ($invoice->discount_total > 0)
                    <div class="total-line"><span>Discount</span><span
                            style="color: #db2777;">-{{ money($invoice->discount_total, $invoice->currency->code) }}</span>
                    </div>
                @endif
                <div class="total-line"><span>VAT
                        ({{ number_format($invoice->items->first()?->tax_rate ?? 20, 0) }}%)</span><span>{{ money($invoice->tax_total, $invoice->currency->code) }}</span>
                </div>
                @if ($invoice->shipping_total > 0)
                    <div class="total-line">
                        <span>Shipping</span><span>{{ money($invoice->shipping_total, $invoice->currency->code) }}</span>
                    </div>
                @endif
                <div class="total-line grand-total">
                    <span>Total</span><span>{{ money($invoice->grand_total, $invoice->currency->code) }}</span>
                </div>
            </div>
        </div>

        {{-- Bank Details --}}
        @if ($invoice->company->banks->count() > 0)
            <div class="bank-details">
                <div class="label" style="margin-bottom: 8px;">Bank Details</div>
                @foreach ($invoice->company->banks->take(1) as $bank)
                    <div style="font-size: 12px; line-height: 1.6; color: #555;">
                        <span style="font-weight: 600;">{{ $bank->bank_name }}</span><br>
                        A/C Name: {{ $bank->account_name ?? $invoice->company->name }}<br>
                        A/C No: {{ $bank->account_number }}
                        @if ($bank->iban)
                            | IBAN: {{ $bank->iban }}
                        @endif
                        @if ($bank->swift_code)
                            | SWIFT: {{ $bank->swift_code }}
                        @endif
                        @if ($bank->bic_code)
                            | BIC: {{ $bank->bic_code }}
                        @endif
                        @if ($bank->sort_code)
                            | Sort Code: {{ $bank->sort_code }}
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Footer (hidden in HTML, rendered by JS on every PDF page) --}}
        <div class="footer">
            <div class="footer-left">
                Provided by: {{ $invoice->company->name }}<br>
                @if ($invoice->company->detail?->tax_number)
                    VAT ID: {{ $invoice->company->detail->tax_number }}
                @endif
            </div>
            <div class="footer-right">
                Issued on
                {{ $invoice->issued_at ? $invoice->issued_at->format('F d, Y') : $invoice->created_at->format('F d, Y') }}<br>
                {{ $invoice->invoice_number }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function generatePDF(mode) {
            mode = mode || 'save';
            const element = document.getElementById('invoice-content');

            const options = {
                margin: [25, 0, 30, 0],
                filename: 'Invoice_{{ $invoice->invoice_number }}.pdf',
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
            };

            html2pdf().set(options).from(element).toPdf().get('pdf').then(function(pdf) {
                const totalPages = pdf.internal.getNumberOfPages();
                const pageWidth = pdf.internal.pageSize.getWidth();

                for (let i = 1; i <= totalPages; i++) {
                    pdf.setPage(i);

                    // --- HEADER (Repeats on every page) ---
                    pdf.setFontSize(22);
                    pdf.setTextColor(51, 51, 51);
                    pdf.setFont("helvetica", "bold");
                    pdf.text('INVOICE', 10, 15);

                    pdf.setFontSize(9);
                    pdf.setTextColor(102, 102, 102);
                    pdf.text('Invoice number', 77, 12);
                    pdf.text('Invoice total', pageWidth - 10, 12, {
                        align: 'right'
                    });

                    pdf.setTextColor(0, 0, 0);
                    pdf.setFontSize(10);
                    pdf.text('{{ $invoice->invoice_number }}', 77, 17);
                    pdf.text('{{ $invoice->currency->symbol }}{{ number_format($invoice->grand_total, 2) }}',
                        pageWidth - 10, 17, {
                            align: 'right'
                        });

                    // --- FOOTER (Repeats on every page) ---
                    pdf.setFontSize(9);
                    pdf.setTextColor(128, 128, 128);

                    const footerY = 285;
                    pdf.text('Provided by: {{ $invoice->company->name }}', 15, footerY);
                    @if ($invoice->company->detail?->tax_number)
                        pdf.text('VAT ID: {{ $invoice->company->detail->tax_number }}', 15, footerY + 5);
                    @endif

                    const dateStr =
                        'Issued on {{ $invoice->issued_at ? $invoice->issued_at->format('F d, Y') : $invoice->created_at->format('F d, Y') }}';
                    const pageStr = `Page ${i} of ${totalPages} for {{ $invoice->invoice_number }}`;

                    pdf.text(dateStr, pageWidth - 15, footerY, {
                        align: 'right'
                    });
                    pdf.text(pageStr, pageWidth - 15, footerY + 5, {
                        align: 'right'
                    });
                }

                if (mode === 'print') {
                    const blob = pdf.output('blob');
                    const url = URL.createObjectURL(blob);
                    window.open(url, '_self');
                } else {
                    pdf.save('Invoice_{{ $invoice->invoice_number }}.pdf');
                }
            });
        }
    </script>
@endsection
