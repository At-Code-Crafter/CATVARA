@extends('catvara.layouts.print')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
  <div class="print-container">
    {{-- Document Header --}}
    <div class="flex justify-between items-start mb-10 pb-6 border-b border-slate-200">
      <div>
        {{-- Company Logo/Name --}}
        @if ($invoice->company->logo)
          <img src="{{ storage_url($invoice->company->logo) }}" class="h-14 w-auto object-contain mb-2">
        @else
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-slate-900 rounded flex items-center justify-center">
              <span class="text-white text-lg font-bold">{{ strtoupper(substr($invoice->company->name, 0, 1)) }}</span>
            </div>
            <div class="text-xl font-bold text-slate-900">{{ $invoice->company->name }}</div>
          </div>
        @endif
        <div class="text-[10px] text-slate-500 leading-relaxed">
          @if($invoice->company->address)
            {!! $invoice->company->address->render() !!}<br>
          @endif
          @if($invoice->company->email || $invoice->company->phone)
            {{ $invoice->company->email }} {{ $invoice->company->phone ? '| ' . $invoice->company->phone : '' }}
          @endif
        </div>
      </div>
      <div class="text-right">
        <div class="text-3xl font-bold text-slate-900 mb-3">INVOICE</div>
        <table class="ml-auto text-sm">
          <tr>
            <td class="text-slate-500 pr-4 py-0.5">Invoice No:</td>
            <td class="font-bold text-slate-900">{{ $invoice->invoice_number }}</td>
          </tr>
          <tr>
            <td class="text-slate-500 pr-4 py-0.5">Date:</td>
            <td class="font-medium text-slate-700">{{ $invoice->issued_at ? $invoice->issued_at->format('d M, Y') : $invoice->created_at->format('d M, Y') }}</td>
          </tr>
          @if ($invoice->due_date)
          <tr>
            <td class="text-slate-500 pr-4 py-0.5">Due Date:</td>
            <td class="font-medium text-slate-700">{{ $invoice->due_date->format('d M, Y') }}</td>
          </tr>
          @endif
        </table>
      </div>
    </div>

    {{-- Addresses --}}
    <div class="grid grid-cols-2 gap-12 mb-10">
      @php
        $billTo = $invoice->billingAddress;
        $shipTo = $invoice->shippingAddress;
      @endphp
      <div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Bill To</div>
        <div class="text-sm font-bold text-slate-900 mb-1">
          {{ $billTo->name ?? ($invoice->customer->legal_name ?? $invoice->customer->display_name) }}</div>
        <div class="text-xs text-slate-600 leading-relaxed">
          {!! $billTo?->render() !!}
        </div>
        @if ($invoice->customer->tax_number || $billTo?->tax_number)
          <div class="text-[10px] text-slate-500 mt-2">TRN: {{ $billTo?->tax_number ?? $invoice->customer->tax_number }}</div>
        @endif
      </div>
      <div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Ship To</div>
        <div class="text-sm font-bold text-slate-900 mb-1">
          {{ $shipTo->name ?? $invoice->customer->display_name }}</div>
        <div class="text-xs text-slate-600 leading-relaxed">
          {!! $shipTo?->render() ?? 'Same as Billing Address' !!}
        </div>
      </div>
    </div>

    {{-- Items Table --}}
    <table class="w-full mb-8">
      <thead>
        <tr class="border-y border-slate-300">
          <th class="py-3 text-left text-[10px] font-bold text-slate-600 uppercase w-10">#</th>
          <th class="py-3 text-left text-[10px] font-bold text-slate-600 uppercase">Description</th>
          <th class="py-3 text-right text-[10px] font-bold text-slate-600 uppercase w-24">Price</th>
          <th class="py-3 text-center text-[10px] font-bold text-slate-600 uppercase w-16">Qty</th>
          <th class="py-3 text-right text-[10px] font-bold text-slate-600 uppercase w-28">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($invoice->items as $index => $item)
          <tr class="border-b border-slate-100">
            <td class="py-3 text-xs text-slate-500">{{ $index + 1 }}</td>
            <td class="py-3">
              <div class="text-sm text-slate-800">{{ $item->product_name }}</div>
              @if ($item->variant_description)
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $item->variant_description }}</div>
              @endif
            </td>
            <td class="py-3 text-right text-sm text-slate-700">{{ money($item->unit_price, $invoice->currency->code) }}</td>
            <td class="py-3 text-center text-sm text-slate-700">{{ (int)$item->quantity }}</td>
            <td class="py-3 text-right text-sm font-medium text-slate-900">{{ money($item->line_total, $invoice->currency->code) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Summary --}}
    <div class="flex justify-end mb-10">
      <div class="w-72">
        <div class="flex justify-between py-2">
          <span class="text-sm text-slate-500">Subtotal</span>
          <span class="text-sm text-slate-700">{{ money($invoice->subtotal, $invoice->currency->code) }}</span>
        </div>
        @if ($invoice->discount_total > 0)
          <div class="flex justify-between py-2">
            <span class="text-sm text-slate-500">Discount</span>
            <span class="text-sm text-emerald-600">-{{ money($invoice->discount_total, $invoice->currency->code) }}</span>
          </div>
        @endif
        @if ($invoice->shipping_total > 0)
          <div class="flex justify-between py-2">
            <span class="text-sm text-slate-500">Shipping</span>
            <span class="text-sm text-slate-700">{{ money($invoice->shipping_total, $invoice->currency->code) }}</span>
          </div>
        @endif
        <div class="flex justify-between py-2">
          <span class="text-sm text-slate-500">Tax</span>
          <span class="text-sm text-slate-700">{{ money($invoice->tax_total, $invoice->currency->code) }}</span>
        </div>
        <div class="flex justify-between py-3 border-t-2 border-slate-900 mt-2">
          <span class="text-base font-bold text-slate-900">Total</span>
          <span class="text-base font-bold text-slate-900">{{ money($invoice->grand_total, $invoice->currency->code) }}</span>
        </div>
      </div>
    </div>

    {{-- Footer --}}
    <div class="pt-6 border-t border-slate-200">
      <div class="grid grid-cols-2 gap-12">
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Notes</div>
          <div class="text-xs text-slate-600 leading-relaxed">
            {!! nl2br(e($invoice->notes ?? 'Thank you for your business.')) !!}
          </div>
        </div>
        <div class="text-right">
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Payment Terms</div>
          <div class="text-xs text-slate-600">
            {{ $invoice->payment_term_name ?? 'Standard' }}
            @if ($invoice->payment_due_days)
              (Due in {{ $invoice->payment_due_days }} days)
            @endif
          </div>
          @if ($invoice->company->banks->count() > 0)
            <div class="mt-4 pt-3 border-t border-slate-100 text-left">
              <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Bank Details</div>
              @foreach ($invoice->company->banks->take(1) as $bank)
                <div class="text-xs text-slate-600 leading-relaxed">
                  {{ $bank->bank_name }}<br>
                  A/C: {{ $bank->account_number }}
                  @if ($bank->iban) | IBAN: {{ $bank->iban }} @endif
                  @if ($bank->swift_code) | SWIFT: {{ $bank->swift_code }} @endif
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
