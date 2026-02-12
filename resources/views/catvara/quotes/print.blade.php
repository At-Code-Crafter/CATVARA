@extends('catvara.layouts.print')

@section('title', 'Quote ' . $quote->quote_number)

@section('content')
  <div class="invoice-container" id="quote-content">
    {{-- Header (hidden in HTML, rendered by JS on every PDF page) --}}
    <div class="header-top">
      <div class="invoice-title">QUOTATION</div>
      <div class="meta-item">
        <div class="label">Quote number</div>
        <div class="value">{{ $quote->quote_number }}</div>
      </div>
      <div class="meta-item text-right">
        <div class="label">Quote total</div>
        <div class="value">{{ $quote->currency->symbol }}{{ number_format($quote->grand_total, 2) }}</div>
      </div>
    </div>

    {{-- Brand Row: Logo, Dates, Additional Details --}}
    <div class="brand-row">
      <div class="brand-block">
        @if ($quote->company->logo)
          <img src="{{ storage_url($quote->company->logo) }}" alt="{{ $quote->company->name }}">
        @else
          <div style="font-size: 20px; font-weight: 700; color: #333;">{{ $quote->company->name }}</div>
        @endif
      </div>
      <div class="dates-block">
        <div class="date-item">
          <div class="label">Date of issue</div>
          <div class="value">{{ $quote->created_at->format('F d, Y') }}</div>
        </div>
        @if ($quote->valid_until)
          <div class="date-item">
            <div class="label">Valid until</div>
            <div class="value">{{ $quote->valid_until->format('F d, Y') }}</div>
          </div>
        @endif
      </div>
      <div class="additional-details-block">
        <div class="label text-right">Additional details</div>
        @if ($quote->paymentTerm)
          <div class="text-right" style="font-size: 11px; color: #333; margin-top: 8px;">
            <strong>Payment Terms:</strong> {{ $quote->paymentTerm->name }}
          </div>
        @endif
        @if ($quote->notes)
          <div class="text-right" style="font-size: 11px; color: #555; margin-top: 8px;">{{ $quote->notes }}</div>
        @endif
      </div>
    </div>

    {{-- Addresses Row --}}
    @php
      $billTo = $quote->billingAddress;
      $shipTo = $quote->shippingAddress;
    @endphp
    <div class="address-grid">
      <div class="address-col">
        <div class="label">Bill to</div>
        <p>
          {{ $billTo->name ?? ($quote->customer->legal_name ?? $quote->customer->display_name) }}<br>
          {!! $billTo?->render() !!}
        </p>
      </div>
      <div class="address-col">
        <div class="label">Ship to</div>
        <p>
          {{ $shipTo->name ?? $quote->customer->display_name }}<br>
          {!! $shipTo?->render() ?? 'Same as Billing Address' !!}
        </p>
      </div>
      <div class="address-col text-right">
        <div class="label">Merchant</div>
        <p>
          {{ $quote->company->name }}<br>
          @if ($quote->company->detail?->address)
            {{ $quote->company->detail->address }}<br>
          @endif
          @if ($quote->company->detail?->email)
            {{ $quote->company->detail->email }}<br>
          @endif
          @if ($quote->company->detail?->tax_number)
            <br>{{ $quote->company->detail->tax_number }}
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
          @foreach ($quote->items as $item)
            <tr>
              <td>
                {{ $item->product_name }}
                @if ($item->variant_description)
                  <br><span style="font-size: 11px; color: #aaa;">{{ $item->variant_description }}</span>
                @endif
              </td>
              <td class="text-right">{{ (int) $item->quantity }}</td>
              <td class="text-right">{{ money($item->unit_price, $quote->currency->code) }}</td>
              <td class="text-right">{{ number_format($item->tax_rate, 0) }}%</td>
              <td class="text-right">
                {{ money($item->line_total, $quote->currency->code) }}
                @if ($item->discount_amount > 0)
                  <br><span class="discount">-{{ money($item->discount_amount, $quote->currency->code) }}</span>
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
        <div class="total-line"><span>Subtotal</span><span>{{ money($quote->subtotal, $quote->currency->code) }}</span></div>
        @if ($quote->discount_total > 0)
          <div class="total-line"><span>Discount</span><span style="color: #db2777;">-{{ money($quote->discount_total, $quote->currency->code) }}</span></div>
        @endif
        <div class="total-line"><span>VAT ({{ number_format($quote->items->first()?->tax_rate ?? 20, 0) }}%)</span><span>{{ money($quote->tax_total, $quote->currency->code) }}</span></div>
        @if ($quote->shipping_total > 0)
          <div class="total-line"><span>Shipping</span><span>{{ money($quote->shipping_total, $quote->currency->code) }}</span></div>
        @endif
        <div class="total-line grand-total"><span>Total</span><span>{{ money($quote->grand_total, $quote->currency->code) }}</span></div>
      </div>
    </div>

    {{-- Bank Details --}}
    @if ($quote->company->banks->count() > 0)
      <div class="bank-details">
        <div class="label" style="margin-bottom: 8px;">Bank Details</div>
        @foreach ($quote->company->banks->take(1) as $bank)
          <div style="font-size: 12px; line-height: 1.6; color: #555;">
            <span style="font-weight: 600;">{{ $bank->bank_name }}</span><br>
            A/C Name: {{ $bank->account_name ?? $quote->company->name }}<br>
            A/C No: {{ $bank->account_number }}
            @if ($bank->iban) | IBAN: {{ $bank->iban }} @endif
            @if ($bank->swift_code) | SWIFT: {{ $bank->swift_code }} @endif
          </div>
        @endforeach
      </div>
    @endif

    {{-- Footer (hidden in HTML, rendered by JS on every PDF page) --}}
    <div class="footer">
      <div class="footer-left">
        Provided by: {{ $quote->company->name }}<br>
        @if ($quote->company->detail?->tax_number)
          VAT ID: {{ $quote->company->detail->tax_number }}
        @endif
      </div>
      <div class="footer-right">
        {{ $quote->created_at->format('F d, Y') }}<br>
        {{ $quote->quote_number }}
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  function generatePDF(mode) {
    mode = mode || 'save';
    const element = document.getElementById('quote-content');

    const options = {
      margin: [25, 0, 30, 0],
      filename: 'Quote_{{ $quote->quote_number }}.pdf',
      image: { type: 'jpeg', quality: 1.0 },
      html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(options).from(element).toPdf().get('pdf').then(function (pdf) {
      const totalPages = pdf.internal.getNumberOfPages();
      const pageWidth = pdf.internal.pageSize.getWidth();

      for (let i = 1; i <= totalPages; i++) {
        pdf.setPage(i);

        // --- HEADER (Repeats on every page) ---
        pdf.setFontSize(22);
        pdf.setTextColor(51, 51, 51);
        pdf.setFont("helvetica", "bold");
        pdf.text('QUOTATION', 10, 15);

        pdf.setFontSize(9);
        pdf.setTextColor(102, 102, 102);
        pdf.text('Quote number', 85, 12);
        pdf.text('Quote total', pageWidth - 10, 12, { align: 'right' });

        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(10);
        pdf.text('{{ $quote->quote_number }}', 85, 17);
        pdf.text('{{ $quote->currency->symbol }}{{ number_format($quote->grand_total, 2) }}', pageWidth - 10, 17, { align: 'right' });

        // --- FOOTER (Repeats on every page) ---
        pdf.setFontSize(9);
        pdf.setTextColor(128, 128, 128);

        const footerY = 285;
        pdf.text('Provided by: {{ $quote->company->name }}', 15, footerY);
        @if ($quote->company->detail?->tax_number)
          pdf.text('VAT ID: {{ $quote->company->detail->tax_number }}', 15, footerY + 5);
        @endif

        const dateStr = '{{ $quote->created_at->format("F d, Y") }}';
        const pageStr = `Page ${i} of ${totalPages} for {{ $quote->quote_number }}`;

        pdf.text(dateStr, pageWidth - 15, footerY, { align: 'right' });
        pdf.text(pageStr, pageWidth - 15, footerY + 5, { align: 'right' });
      }

      if (mode === 'print') {
        const blob = pdf.output('blob');
        const url = URL.createObjectURL(blob);
        window.open(url, '_self');
      } else {
        pdf.save('Quote_{{ $quote->quote_number }}.pdf');
      }
    });
  }
</script>
@endsection
