@extends('catvara.layouts.print')

@section('title', 'Delivery Note ' . $dn->delivery_note_number)

@section('content')
  <div class="print-container">
    {{-- Document Header --}}
    <div class="flex justify-between items-start mb-12">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase mb-1">Delivery Note</h1>
        <div class="text-sm font-bold text-slate-500 tracking-widest uppercase">No: <span
            class="text-slate-900">{{ $dn->delivery_note_number }}</span></div>
        <div class="text-sm font-bold text-slate-500 tracking-widest uppercase mt-1">Order Ref: <span
            class="text-slate-900">{{ $dn->order->order_number }}</span></div>
        <div class="text-sm font-bold text-slate-500 tracking-widest uppercase mt-1">Date: <span
            class="text-slate-900">{{ $dn->created_at->format('d M, Y') }}</span></div>
        <div class="text-sm font-bold text-slate-500 tracking-widest uppercase mt-1">Dispatch From: <span
            class="text-slate-900">{{ $dn->inventoryLocation ? $dn->inventoryLocation->name : 'N/A' }}</span></div>
        @if ($dn->reference_number)
          <div class="text-sm font-bold text-slate-500 tracking-widest uppercase mt-1">Ref No: <span
              class="text-slate-900">{{ $dn->reference_number }}</span></div>
        @endif
        @if ($dn->vehicle_number)
          <div class="text-sm font-bold text-slate-500 tracking-widest uppercase mt-1">Vehicle: <span
              class="text-slate-900">{{ $dn->vehicle_number }}</span></div>
        @endif
      </div>
      <div class="text-right">
        @if ($dn->order->company->logo)
          <img src="{{ storage_url($dn->order->company->logo) }}" class="h-12 w-auto ml-auto mb-2">
        @else
          <div class="text-xl font-black text-brand-600 uppercase">{{ $dn->order->company->name }}</div>
        @endif
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
          {{ $dn->order->company->address }}<br>
          {{ $dn->order->company->email }} | {{ $dn->order->company->phone }}
        </div>
      </div>
    </div>

    {{-- Addresses --}}
    <div class="grid grid-cols-2 gap-12 mb-12">
      <div>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 border-b border-slate-100 pb-1">
          Customer</div>
        <div class="text-sm font-black text-brand-600 mb-1">{{ $dn->order->customer->display_name ?? 'Cash Customer' }}
        </div>
        <div class="text-[11px] font-medium text-slate-600 leading-relaxed">
          @if ($dn->order->billingAddress)
            {{ $dn->order->billingAddress->address_line_1 }}<br>
            @if ($dn->order->billingAddress->address_line_2)
              {{ $dn->order->billingAddress->address_line_2 }}<br>
            @endif
            {{ $dn->order->billingAddress->city }}, {{ $dn->order->billingAddress->state->name ?? '' }}
            {{ $dn->order->billingAddress->zip_code }}<br>
            {{ $dn->order->billingAddress->country->name ?? '' }}
          @endif
        </div>
      </div>
      <div>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 border-b border-slate-100 pb-1">
          Delivery Address</div>
        <div class="text-sm font-black text-indigo-600 mb-1">
          {{ $dn->order->shippingCustomer->display_name ?? ($dn->order->customer->display_name ?? 'N/A') }}</div>
        <div class="text-[11px] font-medium text-slate-600 leading-relaxed">
          @if ($dn->order->shippingAddress)
            {{ $dn->order->shippingAddress->address_line_1 }}<br>
            @if ($dn->order->shippingAddress->address_line_2)
              {{ $dn->order->shippingAddress->address_line_2 }}<br>
            @endif
            {{ $dn->order->shippingAddress->city }}, {{ $dn->order->shippingAddress->state->name ?? '' }}
            {{ $dn->order->shippingAddress->zip_code }}<br>
            {{ $dn->order->shippingAddress->country->name ?? '' }}
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
            <th class="text-left py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 w-[70%]">Item
              Description</th>
            <th class="text-right py-3 text-[10px] font-black uppercase tracking-widest text-slate-500">Qty Delivered</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($dn->items as $item)
            <tr class="border-b border-slate-100">
              <td class="py-4">
                <div class="text-[11px] font-black text-slate-800">{{ $item->orderItem->product_name }}</div>
                @if ($item->orderItem->variant_description)
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">
                    {{ $item->orderItem->variant_description }}</div>
                @endif
              </td>
              <td class="text-right py-4 text-[11px] font-black text-slate-900">{{ (float) $item->quantity }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Extra Info --}}
    <div class="mt-24 pt-8 border-t-2 border-slate-100 grid grid-cols-2 gap-12">
      <div>
        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Instructions & Notes</div>
        @if ($dn->notes)
          <p class="text-[10px] font-medium text-slate-600 leading-relaxed">{{ $dn->notes }}</p>
        @else
          <p class="text-[10px] font-medium text-slate-400 italic">No special instructions.</p>
        @endif
      </div>
      <div>
        <div class="flex flex-col gap-8">
          <div class="text-right">
            <div class="h-10 w-48 border-b border-slate-200 ml-auto mb-2"></div>
            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Receiver Signature</div>
          </div>
          <div class="text-right">
            <div class="h-10 w-48 border-b border-slate-200 ml-auto mb-2"></div>
            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Delivered By</div>
          </div>
        </div>
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
