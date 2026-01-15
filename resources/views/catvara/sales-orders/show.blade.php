@extends('catvara.layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <div class="flex items-center gap-3">
          <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Order #{{ $order->order_number }}</h2>
          <span class="badge {{ ($order->status->code ?? '') === 'CONFIRMED' ? 'badge-success' : 'badge-warning' }}">
            {{ $order->status->name ?? 'Draft' }}
          </span>
        </div>
        <p class="text-slate-400 text-sm mt-1 font-medium">Placed on {{ $order->created_at->format('M d, Y') }} at
          {{ $order->created_at->format('h:i A') }}</p>
      </div>
      <div class="flex items-center gap-3">
        @if (($order->status->code ?? '') !== 'CONFIRMED')
          <a href="{{ company_route('sales-orders.edit', $order->uuid) }}" class="btn btn-white">
            <i class="fas fa-edit mr-2"></i> Edit
          </a>
        @endif
        <a href="{{ company_route('sales-orders.print', ['sales_order' => $order->uuid]) }}" target="_blank"
          class="btn btn-white">
          <i class="fas fa-print mr-2"></i> Print
        </a>
        <a href="{{ company_route('sales-orders.index') }}" class="btn btn-white">
          <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
      </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Customer Info -->
      <div class="card p-6 border-slate-100 shadow-soft space-y-4">
        <div class="flex items-center gap-3 border-b border-slate-50 pb-4">
          <div class="h-10 w-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-400">
            <i class="fas fa-user text-sm"></i>
          </div>
          <h3 class="font-bold text-slate-800">Customer Info</h3>
        </div>
        <div class="space-y-2">
          <p class="text-lg font-bold text-brand-400">{{ $order->customer->display_name ?? 'N/A' }}</p>
          <div class="text-sm text-slate-500 space-y-1">
            @if ($order->shippingAddress)
              <p>{{ $order->shippingAddress->address_line_1 }}</p>
              @if ($order->shippingAddress->address_line_2)
                <p>{{ $order->shippingAddress->address_line_2 }}</p>
              @endif
              <p>{{ $order->shippingAddress->city ?? '' }}, {{ $order->shippingAddress->state->name ?? '' }}
                {{ $order->shippingAddress->zip_code ?? '' }}</p>
              <p>{{ $order->shippingAddress->country->name ?? '' }}</p>
              <p class="pt-2 font-bold"><i class="fas fa-phone mr-2 text-xs"></i>
                {{ $order->shippingAddress->phone ?? $order->customer->phone }}</p>
              <p class="font-bold"><i class="fas fa-envelope mr-2 text-xs"></i>
                {{ $order->shippingAddress->email ?? $order->customer->email }}</p>
            @endif
          </div>
        </div>
      </div>

      <!-- Billing Info -->
      <div class="card p-6 border-slate-100 shadow-soft space-y-4">
        <div class="flex items-center gap-3 border-b border-slate-50 pb-4">
          <div class="h-10 w-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-400">
            <i class="fas fa-file-invoice-dollar text-sm"></i>
          </div>
          <h3 class="font-bold text-slate-800">Billing Address</h3>
        </div>
        <div class="space-y-2">
          @if ($order->billingAddress)
            <p class="text-lg font-bold text-orange-400">
              {{ $order->billingAddress->first_name ?? $order->customer->display_name }}</p>
            <div class="text-sm text-slate-500 space-y-1">
              <p>{{ $order->billingAddress->address_line_1 }}</p>
              @if ($order->billingAddress->address_line_2)
                <p>{{ $order->billingAddress->address_line_2 }}</p>
              @endif
              <p>{{ $order->billingAddress->city ?? '' }}, {{ $order->billingAddress->state->name ?? '' }}
                {{ $order->billingAddress->zip_code ?? '' }}</p>
              <p>{{ $order->billingAddress->country->name ?? '' }}</p>
            </div>
          @else
            <p class="text-slate-400 italic text-sm">Same as Shipping Address</p>
          @endif
        </div>
      </div>

      <!-- Order Summary -->
      <div class="card p-6 border-slate-100 shadow-soft space-y-4">
        <div class="flex items-center gap-3 border-b border-slate-50 pb-4">
          <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-400">
            <i class="fas fa-info-circle text-sm"></i>
          </div>
          <h3 class="font-bold text-slate-800">Order Summary</h3>
        </div>
        <div class="space-y-4">
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Payment Term</span>
            <span class="font-bold text-slate-700">{{ $order->paymentTerm->name ?? 'Direct' }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Due Date</span>
            <span
              class="font-bold text-red-500">{{ $order->due_date ? $order->due_date->format('M d, Y') : 'N/A' }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Currency</span>
            <span class="font-bold text-slate-700">{{ $order->currency->code ?? 'AED' }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Payment Status</span>
            <span id="paymentStatusBadge"
              class="badge {{ $order->payment_status === 'PAID' ? 'badge-success' : 'badge-danger' }}">
              {{ $order->payment_status }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Items Table -->
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0">
        <table class="table-premium w-full text-left">
          <thead>
            <tr>
              <th class="px-8!">Item Details</th>
              <th class="text-center">Quantity</th>
              <th class="text-right">Unit Price</th>
              <th class="text-center">Discount</th>
              <th class="text-right px-8!">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            @foreach ($order->items as $item)
              <tr>
                <td class="px-8!">
                  <div class="font-bold text-slate-800">{{ $item->product_name }}</div>
                  @if ($item->variant_description)
                    <div class="text-[10px] text-slate-400 font-medium uppercase tracking-tight">
                      {{ $item->variant_description }}</div>
                  @endif
                </td>
                <td class="text-center font-bold text-slate-600">{{ (float) $item->quantity }}</td>
                <td class="text-right font-medium text-slate-600">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="text-center">
                  @if ($item->discount_amount > 0)
                    <span
                      class="text-emerald-500 font-bold text-xs">-{{ number_format((float) $item->discount_amount, 2) }}</span>
                  @else
                    <span class="text-slate-300">-</span>
                  @endif
                </td>
                <td class="text-right px-8! font-bold text-slate-900">{{ number_format((float) $item->line_total, 2) }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Totals & Notes -->
    <div class="flex flex-col lg:flex-row gap-8">
      <div class="lg:w-1/2">
        @if ($order->notes)
          <div class="card p-6 bg-slate-50/50 border-none shadow-none">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-white/50 pb-2">
              Internal Notes</h4>
            <p class="text-sm text-slate-600 font-medium line-clamp-3">{{ $order->notes }}</p>
          </div>
        @endif
      </div>
      <div class="lg:w-1/2">
        <div class="card p-6 border-slate-100 shadow-soft space-y-4">
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Subtotal</span>
            <span class="font-bold text-slate-700">{{ number_format((float) $order->subtotal, 2) }}</span>
          </div>
          @if ($order->discount_total > 0)
            <div class="flex justify-between items-center text-sm">
              <span class="text-emerald-500 font-medium">Total Discount</span>
              <span class="font-bold text-emerald-500">-{{ number_format((float) $order->discount_total, 2) }}</span>
            </div>
          @endif
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Shipping & Additional</span>
            <span class="font-bold text-slate-700">{{ number_format((float) $order->shipping_total, 2) }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Estimated Tax</span>
            <span class="font-bold text-slate-700">{{ number_format((float) $order->tax_total, 2) }}</span>
          </div>
          <div class="pt-4 border-t border-slate-50 flex justify-between items-center">
            <span class="text-lg font-bold text-slate-800">Grand Total</span>
            <span class="text-2xl font-black text-brand-400">{{ number_format((float) $order->grand_total, 2) }}
              {{ $order->currency->code ?? '' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
