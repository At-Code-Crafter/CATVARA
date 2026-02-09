@extends('catvara.layouts.print')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
  <div class="print-container">
    {{-- Top Header Row --}}
    <div class="flex justify-between items-start mb-0 pb-4 border-b border-slate-200">
      <div>
        <div class="text-4xl font-bold text-slate-900">INVOICE</div>
      </div>
      <div>
        <div class="text-xs text-slate-500">Invoice number</div>
        <div class="text-base font-bold text-slate-900">{{ $invoice->invoice_number }}</div>
      </div>
      <div class="text-right">
        <div class="text-xs text-slate-500">Invoice total</div>
        <div class="text-xl font-bold text-slate-900">{{ $invoice->currency->symbol }}{{ number_format($invoice->grand_total, 2) }}</div>
      </div>
    </div>

    {{-- Second Row: Logo, Dates, Additional Details --}}
    <div class="flex mb-0 border-b border-slate-200">
      {{-- Company Logo --}}
      <div class="flex-1 py-4">
        @if ($invoice->company->logo)
          <img src="{{ storage_url($invoice->company->logo) }}" class="h-16 w-auto object-contain">
        @else
          <div class="text-2xl font-bold text-slate-900">{{ $invoice->company->name }}</div>
        @endif
      </div>
      {{-- Dates --}}
      <div class="flex-1 py-4">
        <div class="mb-3">
          <div class="text-xs font-semibold text-slate-700">Date of issue</div>
          <div class="text-sm text-slate-900">
            {{ $invoice->issued_at ? $invoice->issued_at->format('F d, Y') : $invoice->created_at->format('F d, Y') }}
          </div>
        </div>
        <div>
          <div class="text-xs font-semibold text-slate-700">Date of supply</div>
          <div class="text-sm text-slate-900">
            {{ $invoice->issued_at ? $invoice->issued_at->format('F d, Y') : $invoice->created_at->format('F d, Y') }}
          </div>
        </div>
      </div>
      {{-- Additional Details / Payment Terms --}}
      <div class="flex-1 bg-slate-100 py-4 px-6">
        <div class="text-sm text-slate-600 mb-2">Additional details</div>
        @if ($invoice->payment_term_name)
          <div class="text-xs text-slate-700">
            <strong>Payment Terms:</strong> {{ $invoice->payment_term_name }}
            @if ($invoice->payment_due_days)
              (Due in {{ $invoice->payment_due_days }} days)
            @endif
          </div>
        @endif
        @if ($invoice->notes)
          <div class="text-xs text-slate-600 mt-2">{{ $invoice->notes }}</div>
        @endif
      </div>
    </div>

    {{-- Addresses Row: Bill To, Ship To, Merchant --}}
    <div class="flex mb-6 py-4 border-b border-slate-200">
      @php
        $billTo = $invoice->billingAddress;
        $shipTo = $invoice->shippingAddress;
      @endphp
      {{-- Bill To --}}
      <div class="flex-1">
        <div class="text-xs font-bold text-slate-700 mb-1">Bill to</div>
        <div class="text-sm font-semibold text-slate-900">
          {{ $billTo->name ?? ($invoice->customer->legal_name ?? $invoice->customer->display_name) }}
        </div>
        <div class="text-xs text-slate-600 leading-relaxed mt-1">
          {!! $billTo?->render() !!}
        </div>
      </div>
      {{-- Ship To --}}
      <div class="flex-1">
        <div class="text-xs font-bold text-slate-700 mb-1">Ship to</div>
        <div class="text-sm font-semibold text-slate-900">
          {{ $shipTo->name ?? $invoice->customer->display_name }}
        </div>
        <div class="text-xs text-slate-600 leading-relaxed mt-1">
          {!! $shipTo?->render() ?? 'Same as Billing Address' !!}
        </div>
      </div>
      {{-- Merchant --}}
      <div class="flex-1 text-right">
        <div class="text-xs font-bold text-slate-700 mb-1">Merchant</div>
        <div class="text-sm font-semibold text-slate-900">{{ $invoice->company->name }}</div>
        <div class="text-xs text-slate-600 leading-relaxed mt-1">
          @if ($invoice->company->detail?->address)
            {{ $invoice->company->detail->address }}<br>
          @endif
          @if ($invoice->company->detail?->email)
            {{ $invoice->company->detail->email }}<br>
          @endif
          @if ($invoice->company->detail?->tax_number)
            {{ $invoice->company->detail->tax_number }}
          @endif
        </div>
      </div>
    </div>

    {{-- Items Table --}}
    <table class="w-full mb-6">
      <thead>
        <tr class="border-b border-slate-300">
          <th class="py-3 text-left text-xs font-semibold text-slate-700">Description</th>
          <th class="py-3 text-center text-xs font-semibold text-slate-700 w-20">Quantity</th>
          <th class="py-3 text-right text-xs font-semibold text-slate-700 w-24">Unit price</th>
          <th class="py-3 text-center text-xs font-semibold text-slate-700 w-20">VAT rate</th>
          <th class="py-3 text-right text-xs font-semibold text-slate-700 w-24">Amount</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($invoice->items as $item)
          <tr class="border-b border-slate-100">
            <td class="py-3">
              <div class="text-sm text-slate-800">{{ $item->product_name }}</div>
              @if ($item->variant_description && !($hideVariants ?? false))
                <div class="text-xs text-slate-400 mt-0.5">{{ $item->variant_description }}</div>
              @endif
            </td>
            <td class="py-3 text-center text-sm text-slate-700">{{ (int) $item->quantity }}</td>
            <td class="py-3 text-right text-sm text-slate-700">{{ money($item->unit_price, $invoice->currency->code) }}</td>
            <td class="py-3 text-center text-sm text-slate-700">{{ $item->tax_percentage ?? 20 }}%</td>
            <td class="py-3 text-right text-sm text-slate-900">
              {{ money($item->line_total, $invoice->currency->code) }}
              @if ($item->discount_amount > 0)
                <div class="text-xs text-pink-600">-{{ money($item->discount_amount, $invoice->currency->code) }}</div>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Summary --}}
    <div class="flex justify-end mb-8">
      <div class="w-80">
        <div class="flex justify-between py-2 border-b border-slate-100">
          <span class="text-sm text-slate-700 font-semibold">Subtotal</span>
          <span class="text-sm text-slate-900">{{ money($invoice->subtotal, $invoice->currency->code) }}</span>
        </div>
        @if ($invoice->discount_total > 0)
          <div class="flex justify-between py-2 border-b border-slate-100">
            <span class="text-sm text-slate-700">Discount</span>
            <span class="text-sm text-pink-600">-{{ money($invoice->discount_total, $invoice->currency->code) }}</span>
          </div>
        @endif
        <div class="flex justify-between py-2 border-b border-slate-100">
          <span class="text-sm text-slate-700 font-semibold">VAT <span class="font-normal">(20%)</span></span>
          <span class="text-sm text-slate-900">{{ money($invoice->tax_total, $invoice->currency->code) }}</span>
        </div>
        @if ($invoice->shipping_total > 0)
          <div class="flex justify-between py-2 border-b border-slate-100">
            <span class="text-sm text-slate-700 font-semibold">Shipping</span>
            <span class="text-sm text-slate-900">{{ money($invoice->shipping_total, $invoice->currency->code) }}</span>
          </div>
        @endif
        <div class="flex justify-between py-3 border-b-2 border-slate-900">
          <span class="text-sm font-bold text-slate-900">Total</span>
          <span class="text-sm font-bold text-slate-900">{{ money($invoice->grand_total, $invoice->currency->code) }}</span>
        </div>
      </div>
    </div>

    {{-- Additional Notes with Payment Terms --}}
    <div class="mb-6 pb-4 border-b border-slate-200">
      {{-- <div class="text-xs font-bold text-slate-700 mb-2">Additional Notes</div>
      <div class="text-xs text-slate-600 leading-relaxed">
        {!! nl2br(e($invoice->notes ?? 'Thank you for your business.')) !!}
        @if ($invoice->payment_term_name)
          <br><br><strong>Payment Terms:</strong> {{ $invoice->payment_term_name }}
          @if ($invoice->payment_due_days)
            (Due in {{ $invoice->payment_due_days }} days)
          @endif
        @endif
      </div> --}}
    </div>

    {{-- Bank Details --}}
    @if ($invoice->company->banks->count() > 0)
      <div class="mb-6">
        <div class="text-xs font-bold text-slate-700 mb-2">Bank Details</div>
        @foreach ($invoice->company->banks->take(1) as $bank)
          <div class="text-xs text-slate-600 leading-relaxed">
            <span class="font-semibold">{{ $bank->bank_name }}</span><br>
            A/C Name: {{ $bank->account_name ?? $invoice->company->name }}<br>
            A/C No: {{ $bank->account_number }}
            @if ($bank->iban) | IBAN: {{ $bank->iban }} @endif
            @if ($bank->swift_code) | SWIFT: {{ $bank->swift_code }} @endif
          </div>
        @endforeach
      </div>
    @endif

  </div>

  {{-- Page Footer - Fixed at bottom --}}
  <div class="print-footer pt-4 border-t border-slate-300 flex justify-between text-xs text-slate-500">
    <div>
      Provided by: {{ $invoice->company->name }}<br>
      @if ($invoice->company->detail?->tax_number)
        VAT ID: {{ $invoice->company->detail->tax_number }}
      @endif
    </div>
    <div class="text-right">
      Issued on {{ $invoice->issued_at ? $invoice->issued_at->format('F d, Y') : $invoice->created_at->format('F d, Y') }}<br>
      Page 1 of 1 for {{ $invoice->invoice_number }}
    </div>
  </div>
@endsection
