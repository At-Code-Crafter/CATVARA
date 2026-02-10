@extends('catvara.layouts.print')

@section('title', 'Quote ' . $quote->quote_number)

@section('content')
  <div class="print-container">
    {{-- Document Header --}}
    <div class="flex justify-between items-start mb-12">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase mb-1">Quotation</h1>
        <div class="text-sm font-bold text-slate-500 tracking-widest uppercase">No: <span
            class="text-slate-900">{{ $quote->quote_number }}</span></div>
        <div class="text-sm font-bold text-slate-500 tracking-widest uppercase mt-1">Date: <span
            class="text-slate-900">{{ $quote->created_at->format('d M, Y') }}</span>
        </div>
        @if ($quote->valid_until)
          <div class="text-sm font-bold text-slate-500 tracking-widest uppercase mt-1">Valid Until: <span
              class="text-slate-900">{{ $quote->valid_until->format('d M, Y') }}</span></div>
        @endif
      </div>
      <div class="text-right">
        @if ($quote->company->logo)
          <img src="{{ storage_url($quote->company->logo) }}" class="h-12 w-auto ml-auto mb-2">
        @else
          <div class="text-xl font-black text-brand-600 uppercase">{{ $quote->company->name }}</div>
        @endif
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
          {!! $quote->company->address?->render() !!}<br>
          {{ $quote->company->email }} | {{ $quote->company->phone }}
        </div>
      </div>
    </div>

    {{-- Addresses --}}
    <div class="grid grid-cols-2 gap-12 mb-12">
      @php
        $billTo = $quote->billingAddress;
        $shipTo = $quote->shippingAddress;
      @endphp
      <div>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Billed To</div>
        <div class="text-sm font-bold text-slate-900 uppercase mb-1">
          {{ $billTo->name ?? ($quote->customer->legal_name ?? $quote->customer->display_name) }}</div>
        <div class="text-xs text-slate-500 leading-relaxed font-medium">
          {!! $billTo?->render() !!}
        </div>
        @if ($quote->customer->tax_number || ($billTo && $billTo->tax_number))
          <div class="mt-2 pt-2 border-t border-slate-100">
            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">TRN: <span
                class="text-slate-700">{{ $billTo->tax_number ?? $quote->customer->tax_number }}</span></div>
          </div>
        @endif
      </div>
      <div>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Shipped To</div>
        <div class="text-sm font-bold text-slate-900 uppercase mb-1">
          {{ $shipTo->name ?? $quote->customer->display_name }}</div>
        <div class="text-xs text-slate-500 leading-relaxed font-medium">
          {!! $shipTo?->render() ?? 'Same as Billing Address' !!}
        </div>
      </div>
    </div>

    {{-- Items Table --}}
    <table class="w-full mb-12">
      <thead>
        <tr class="border-b-2 border-slate-900">
          <th class="py-4 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest w-12">#</th>
          <th class="py-4 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">Description</th>
          <th class="py-4 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest w-24">Price</th>
          <th class="py-4 text-center text-[10px] font-black text-slate-900 uppercase tracking-widest w-20">Qty</th>
          <th class="py-4 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest w-32">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($quote->items as $index => $item)
          <tr class="border-b border-slate-100">
            <td class="py-4 text-xs font-bold text-slate-400">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
            <td class="py-4">
              <div class="text-sm font-bold text-slate-800 uppercase">{{ $item->product_name }}</div>
              @if ($item->variant_description)
                <div class="text-[10px] font-medium text-slate-400 mt-0.5 tracking-wide">{{ $item->variant_description }}
                </div>
              @endif
            </td>
            <td class="py-4 text-right text-sm font-bold text-slate-700">
              {{ money($item->unit_price, $quote->currency->code) }}</td>
            <td class="py-4 text-center text-sm font-bold text-slate-700">{{ $item->quantity }}</td>
            <td class="py-4 text-right text-sm font-black text-slate-900">
              {{ money($item->line_total, $quote->currency->code) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Summary --}}
    <div class="flex justify-end">
      <div class="w-72">
        <div class="flex justify-between py-2 border-b border-slate-50">
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Subtotal</div>
          <div class="text-sm font-bold text-slate-700">{{ money($quote->subtotal, $quote->currency->code) }}</div>
        </div>
        @if ($quote->discount_total > 0)
          <div class="flex justify-between py-2 border-b border-slate-50">
            <div class="flex flex-col">
              <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Discount</div>
            </div>
            <div class="text-sm font-bold text-emerald-600">
              -{{ money($quote->discount_total, $quote->currency->code) }}</div>
          </div>
        @endif
        @if ($quote->shipping_total > 0)
          <div class="flex justify-between py-2 border-b border-slate-50">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Shipping</div>
            <div class="text-sm font-bold text-slate-700">{{ money($quote->shipping_total, $quote->currency->code) }}
            </div>
          </div>
        @endif
        <div class="flex justify-between py-2 border-b border-slate-50">
          <div class="flex flex-col">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tax Total</div>
          </div>
          <div class="text-sm font-bold text-slate-700">{{ money($quote->tax_total, $quote->currency->code) }}</div>
        </div>
        <div class="flex justify-between py-4 mt-2">
          <div class="text-xs font-black text-slate-900 uppercase tracking-[0.2em]">Grand Total</div>
          <div class="text-xl font-black text-brand-600">{{ money($quote->grand_total, $quote->currency->code) }}
          </div>
        </div>
      </div>
    </div>

    {{-- Footer/Notes --}}
    <div class="mt-16 pt-12 border-t-2 border-slate-900">
      <div class="grid grid-cols-2 gap-12">
        <div>
          <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Notes</div>
          <div class="text-[10px] font-medium text-slate-500 leading-relaxed uppercase tracking-wider">
            {!! nl2br(e($quote->notes ?? 'Thank you for your business.')) !!}
          </div>
        </div>
        <div class="text-right">
          <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Payment Info</div>
          <div class="text-[10px] font-bold text-slate-900 uppercase tracking-widest">
            Payment Term: {{ $quote->paymentTerm->name ?? 'Standard' }}<br>
          </div>

          @if ($quote->company->banks->count() > 0)
            <div class="mt-4 pt-4 border-t border-slate-100">
              @foreach ($quote->company->banks as $bank)
                <div class="text-[9px] text-slate-500 mb-2 leading-relaxed">
                  <span class="font-bold text-slate-900">{{ $bank->bank_name }}</span><br>
                  Account: {{ $bank->account_number }}<br>
                  @if ($bank->iban)
                    IBAN: {{ $bank->iban }}<br>
                  @endif
                  @if ($bank->swift_code)
                    SWIFT: {{ $bank->swift_code }}
                  @endif
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
