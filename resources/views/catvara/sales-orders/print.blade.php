@extends('catvara.layouts.print')

@section('title', 'Order ' . $order->order_number)

@section('content')
  <div class="print-container">
    {{-- Document Header --}}
    <div class="flex justify-between items-start mb-12">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase mb-1">Sales Order</h1>
        <div class="text-sm font-bold text-slate-500 tracking-widest uppercase">No: <span
            class="text-slate-900">{{ $order->order_number }}</span></div>
        <div class="text-sm font-bold text-slate-500 tracking-widest uppercase mt-1">Date: <span
            class="text-slate-900">{{ $order->created_at->format('d M, Y') }}</span></div>
      </div>
      <div class="text-right">
        @if ($order->company->logo)
          <img src="{{ storage_url($order->company->logo) }}" class="h-12 w-auto ml-auto mb-2">
        @else
          <div class="text-xl font-black text-brand-600 uppercase">{{ $order->company->name }}</div>
        @endif
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
          {{ $order->company->address }}<br>
          {{ $order->company->email }} | {{ $order->company->phone }}
        </div>
      </div>
    </div>

    {{-- Addresses --}}
    <div class="grid grid-cols-2 gap-12 mb-12">
      <div>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 border-b border-slate-100 pb-1">
          Bill To</div>
        <div class="text-sm font-black text-brand-600 mb-1">{{ $order->customer->display_name ?? 'Cash Customer' }}</div>
        <div class="text-[11px] font-medium text-slate-600 leading-relaxed">
          @if ($order->billingAddress)
            {{ $order->billingAddress->address_line_1 }}<br>
            @if ($order->billingAddress->address_line_2)
              {{ $order->billingAddress->address_line_2 }}<br>
            @endif
            {{ $order->billingAddress->city }}, {{ $order->billingAddress->state->name ?? '' }}
            {{ $order->billingAddress->zip_code }}<br>
            {{ $order->billingAddress->country->name ?? '' }}
          @else
            No billing address provided.
          @endif
        </div>
      </div>
      <div>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 border-b border-slate-100 pb-1">
          Ship To</div>
        <div class="text-sm font-black text-indigo-600 mb-1">
          {{ $order->shippingCustomer->display_name ?? ($order->customer->display_name ?? 'N/A') }}</div>
        <div class="text-[11px] font-medium text-slate-600 leading-relaxed">
          @if ($order->shippingAddress)
            {{ $order->shippingAddress->address_line_1 }}<br>
            @if ($order->shippingAddress->address_line_2)
              {{ $order->shippingAddress->address_line_2 }}<br>
            @endif
            {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state->name ?? '' }}
            {{ $order->shippingAddress->zip_code }}<br>
            {{ $order->shippingAddress->country->name ?? '' }}
          @else
            No shipping address provided.
          @endif
        </div>
      </div>
    </div>

    {{-- Items Table --}}
    <div class="mb-12">
      <table class="w-full">
        <thead>
          <tr class="border-b-2 border-slate-900">
            <th class="text-left py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 w-[50%]">Item
              Description</th>
            <th class="text-center py-3 text-[10px] font-black uppercase tracking-widest text-slate-500">Qty</th>
            <th class="text-right py-3 text-[10px] font-black uppercase tracking-widest text-slate-500">Price</th>
            <th class="text-right py-3 text-[10px] font-black uppercase tracking-widest text-slate-500">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order->items as $item)
            <tr class="border-b border-slate-100">
              <td class="py-4">
                <div class="text-[11px] font-black text-slate-800">{{ $item->product_name }}</div>
                @if ($item->variant_description)
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">
                    {{ $item->variant_description }}</div>
                @endif
              </td>
              <td class="text-center py-4 text-[11px] font-black text-slate-600">{{ (float) $item->quantity }}</td>
              <td class="text-right py-4 text-[11px] font-black text-slate-600 font-mono">
                {{ number_format($item->unit_price, 2) }}</td>
              <td class="text-right py-4 text-[11px] font-black text-slate-900 font-mono">
                {{ number_format($item->line_total, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Footer Totals --}}
    <div class="flex justify-end pr-0">
      <div class="w-64 space-y-3">
        <div class="flex justify-between items-center text-[11px] font-bold text-slate-500 border-b border-slate-50 pb-2">
          <span>Subtotal</span>
          <span class="text-slate-900 font-black font-mono">{{ number_format($order->subtotal, 2) }}</span>
        </div>
        @if ($order->discount_total > 0)
          <div
            class="flex justify-between items-center text-[11px] font-bold text-rose-500 border-b border-slate-50 pb-2">
            <span>Discounts</span>
            <span class="font-black font-mono">-{{ number_format($order->discount_total, 2) }}</span>
          </div>
        @endif
        <div class="flex justify-between items-center text-[11px] font-bold text-slate-500 border-b border-slate-50 pb-2">
          <span>Tax & Logistics</span>
          <span
            class="text-slate-900 font-black font-mono">{{ number_format($order->tax_total + $order->shipping_total, 2) }}</span>
        </div>
        <div class="flex justify-between items-center pt-2">
          <div>
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-600 block">Grand Total</span>
            <span
              class="text-[9px] text-brand-400 font-black uppercase leading-none">{{ $order->currency->code ?? 'AED' }}</span>
          </div>
          <span
            class="text-2xl font-black text-brand-600 font-mono tracking-tighter">{{ number_format($order->grand_total, 2) }}</span>
        </div>
      </div>
    </div>

    {{-- Extra Info --}}
    <div class="mt-24 pt-8 border-t-2 border-slate-100 grid grid-cols-2 gap-12">
      <div>
        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Terms & Notes</div>
        @if ($order->notes)
          <p class="text-[10px] font-medium text-slate-600 leading-relaxed">{{ $order->notes }}</p>
        @endif
        <p class="text-[10px] font-black text-slate-800 mt-2">Payment Terms: <span
            class="text-brand-600">{{ $order->paymentTerm->name ?? 'N/A' }}</span></p>
      </div>
      <div class="text-right">
        <div class="h-12 w-32 border-b border-slate-200 ml-auto mb-2"></div>
        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Authorized Signature</div>
      </div>
    </div>
  </div>

  <style>
    @media print {
      .no-print {
        display: none;
      }

      body {
        background: white;
        margin: 0;
        padding: 0;
      }

      @page {
        margin: 2cm;
      }
    }
  </style>
@endsection
