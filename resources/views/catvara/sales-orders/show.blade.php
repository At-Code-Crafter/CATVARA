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
        @php
          $statusCode = $order->status->code ?? '';
          $canEdit = $statusCode === 'DRAFT';
          $canDeliver = in_array($statusCode, ['CONFIRMED', 'PARTIALLY_FULFILLED']);
          $isFulfilled = $order->is_fulfilled ?? false;
        @endphp

        @if ($canEdit)
          <a href="{{ company_route('sales-orders.edit', ['sales_order' => $order->uuid]) }}" class="btn btn-white">
            <i class="fas fa-edit mr-2"></i> Edit
          </a>
        @endif

        @if ($statusCode === 'CONFIRMED' || $statusCode === 'PARTIALLY_FULFILLED')
          <button type="button" id="generateInvoiceBtn"
            class="btn btn-primary bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-sm">
            <i class="fas fa-file-invoice mr-2"></i> Generate Invoice
          </button>

          {{-- Mark as Fulfillment Button --}}
          @if ($isFulfilled)
            <button type="button" disabled
              class="btn bg-gray-400 text-white border-none shadow-sm cursor-not-allowed">
              <i class="fas fa-check-circle mr-2"></i> Already Marked as Fulfillment
            </button>
          @else
            <button type="button" id="markFulfillmentBtn"
              class="btn btn-primary bg-amber-500 hover:bg-amber-600 text-white border-none shadow-sm">
              <i class="fas fa-box-check mr-2"></i> Mark as Fulfillment
            </button>
          @endif
        @endif

        @if ($canDeliver)
          <button onclick="openDeliveryModal()" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 text-white">
            <i class="fas fa-truck mr-2"></i> Delivery Note
          </button>
        @endif
        <a href="{{ company_route('sales-orders.print', ['sales_order' => $order->uuid]) }}" target="_blank"
          class="btn btn-white">
          <i class="fas fa-print mr-2"></i> Print Order
        </a>
        <a href="{{ company_route('sales-orders.print-proforma', ['sales_order' => $order->uuid]) }}" target="_blank"
          class="btn btn-white">
          <i class="fas fa-file-contract mr-2"></i> Proforma
        </a>
        <a href="{{ company_route('sales-orders.index') }}" class="btn btn-white">
          <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
      </div>
    </div>

    {{-- Overall Fulfillment Progress --}}
    <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-soft overflow-hidden relative">
      <div class="flex justify-between items-end mb-4">
        <div>
          <h2 class="text-xl font-black text-slate-800 tracking-tight">Fulfillment Performance</h2>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">
            Total Ordered: <span class="text-slate-800">{{ $stats['total_ordered'] }}</span>
            &nbsp;•&nbsp; Shipped: <span class="text-indigo-600">{{ $stats['total_fulfilled'] }}</span>
            &nbsp;•&nbsp; Completion: <span class="text-indigo-600 font-black">{{ $stats['percentage'] }}%</span>
          </p>
        </div>
        <div class="flex gap-4">
          <div class="text-right">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Fully Shipped</span>
            <span class="text-sm font-black text-slate-800">{{ $stats['fully'] }} / {{ $stats['total_items'] }}</span>
          </div>
          <div class="text-right border-l border-slate-100 pl-4">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Partially
              Shipped</span>
            <span class="text-sm font-black text-orange-500">{{ $stats['partial'] }}</span>
          </div>
        </div>
      </div>
      <div class="h-3 bg-slate-50 rounded-full overflow-hidden flex p-0.5 border border-slate-100 shadow-inner">
        <div
          class="h-full bg-linear-to-r from-indigo-500 to-brand-500 rounded-full shadow-lg shadow-indigo-200/50 transition-all duration-[1500ms]"
          style="width: {{ $stats['percentage'] }}%"></div>
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
              <th class="text-center">Fulfillment</th>
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
                <td class="text-center min-w-[120px]">
                  @php
                    $percent = $item->quantity > 0 ? ($item->fulfilled_quantity / $item->quantity) * 100 : 0;
                    $barColor = $percent >= 100 ? 'bg-emerald-500' : ($percent > 0 ? 'bg-indigo-500' : 'bg-slate-200');
                  @endphp
                  <div class="flex flex-col items-center gap-1">
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden max-w-[100px] mx-auto">
                      <div class="h-full {{ $barColor }} transition-all duration-500"
                        style="width: {{ $percent }}%"></div>
                    </div>
                    <span
                      class="text-[9px] font-black {{ $percent >= 100 ? 'text-emerald-500' : ($percent > 0 ? 'text-indigo-500' : 'text-slate-400') }}">
                      {{ (float) $item->fulfilled_quantity }} / {{ (float) $item->quantity }}
                    </span>
                  </div>
                </td>
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

    <!-- Delivery Notes Section -->
    @if ($order->deliveryNotes->count() > 0)
      <div class="card p-6 border-slate-100 shadow-soft">
        <div class="flex items-center gap-3 border-b border-slate-50 pb-4 mb-4">
          <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-400">
            <i class="fas fa-truck-loading text-sm"></i>
          </div>
          <h3 class="font-bold text-slate-800">Generated Delivery Notes</h3>
        </div>

        {{-- Fulfillment Summary Widget --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-4">
            <div class="h-10 w-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-indigo-500">
              <i class="fas fa-boxes text-sm"></i>
            </div>
            <div>
              <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Total Line
                Items</span>
              <span class="text-xl font-black text-slate-800">{{ $stats['total_items'] }} Items</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 flex items-center gap-4">
            <div class="h-10 w-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-emerald-500">
              <i class="fas fa-check-double text-sm"></i>
            </div>
            <div>
              <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest block mb-0.5">Fully
                Shipped</span>
              <span class="text-xl font-black text-slate-800">{{ $stats['fully'] }} Lines</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl bg-orange-50/50 border border-orange-100 flex items-center gap-4">
            <div class="h-10 w-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-orange-400">
              <i class="fas fa-hourglass-half text-sm"></i>
            </div>
            <div>
              <span class="text-[9px] font-black text-orange-400 uppercase tracking-widest block mb-0.5">Pending
                Shipment</span>
              <span class="text-xl font-black text-slate-800">{{ $stats['none'] }} Lines</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100 flex items-center gap-4">
            <div class="h-10 w-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-brand-500">
              <i class="fas fa-weight-hanging text-sm"></i>
            </div>
            <div>
              <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest block mb-0.5">Weight / Value
                Ratio</span>
              <span class="text-xl font-black text-slate-800">{{ $stats['percentage'] }}% Done</span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach ($order->deliveryNotes as $dn)
            <div
              class="flex flex-col rounded-2xl border border-slate-100 overflow-hidden hover:border-brand-200 transition-all hover:shadow-lg bg-white">
              <div class="p-4 flex items-center justify-between border-b border-slate-50 bg-slate-50/50">
                <div>
                  <div class="flex items-center gap-2">
                    <p class="text-xs font-black text-slate-800 tracking-tight">{{ $dn->delivery_note_number }}</p>
                    <span
                      class="badge {{ $dn->status === 'DELIVERED' ? 'badge-success' : 'badge-info' }} text-[8px] px-1 py-0.5 scale-90">
                      {{ $dn->status }}
                    </span>
                  </div>
                  <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                    {{ $dn->created_at->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <a href="{{ company_route('sales-orders.delivery-note.print', ['delivery_note' => $dn->uuid]) }}"
                    target="_blank"
                    class="h-8 w-8 rounded-lg bg-white shadow-xs border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-brand-50 hover:text-brand-500 transition-all"
                    title="Print Note">
                    <i class="fas fa-print text-xs"></i>
                  </a>
                  <a href="{{ company_route('sales-orders.delivery-note.print-label', ['delivery_note' => $dn->uuid]) }}"
                    target="_blank"
                    class="h-8 w-8 rounded-lg bg-white shadow-xs border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-indigo-50 hover:text-indigo-500 transition-all"
                    title="Print Label">
                    <i class="fas fa-tag text-xs"></i>
                  </a>
                  @if ($dn->status !== 'DELIVERED')
                    <button onclick="markAsDelivered('{{ $dn->uuid }}')"
                      class="h-8 w-8 rounded-lg bg-white shadow-xs border border-slate-200 flex items-center justify-center text-emerald-500 hover:bg-emerald-50 hover:border-emerald-200 transition-all font-bold"
                      title="Mark as Delivered">
                      <i class="fas fa-check text-xs"></i>
                    </button>
                  @endif
                  <button onclick="confirmDeleteNote('{{ $dn->uuid }}')"
                    class="h-8 w-8 rounded-lg bg-white shadow-xs border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all"
                    title="Delete Note">
                    <i class="fas fa-trash text-xs"></i>
                  </button>
                </div>
              </div>
              <div class="p-4">
                @if ($dn->reference_number || $dn->vehicle_number)
                  <div class="flex flex-wrap gap-2 mb-3 pb-3 border-b border-slate-50">
                    @if ($dn->reference_number)
                      <span
                        class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-tighter">REF:
                        {{ $dn->reference_number }}</span>
                    @endif
                    @if ($dn->vehicle_number)
                      <span
                        class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 text-[9px] font-black uppercase tracking-tighter">VEH:
                        {{ $dn->vehicle_number }}</span>
                    @endif
                  </div>
                @endif
                <div class="space-y-1.5">
                  @foreach ($dn->items as $dnItem)
                    <div class="flex justify-between items-center text-[10px]">
                      <span
                        class="text-slate-500 font-medium truncate pr-4">{{ $dnItem->orderItem->product_name }}</span>
                      <span class="font-black text-slate-700 whitespace-nowrap">x {{ (float) $dnItem->quantity }}</span>
                    </div>
                  @endforeach
                </div>
              </div>
              @if ($dn->notes)
                <div class="px-4 py-2 border-t border-slate-50 bg-slate-50/20">
                  <p class="text-[9px] text-slate-400 italic line-clamp-1">{{ $dn->notes }}</p>
                </div>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <script>
    function openDeliveryModal() {
      const locations = {!! json_encode($locations) !!};
      const items = {!! json_encode(
          $order->items->map(function ($item) {
              return [
                  'id' => $item->id,
                  'name' => $item->product_name,
                  'variant' => $item->variant_description,
                  'ordered' => (float) $item->quantity,
                  'fulfilled' => (float) $item->fulfilled_quantity,
                  'remaining' => (float) $item->quantity - (float) $item->fulfilled_quantity,
                  'stock' => (float) ($item->productVariant ? $item->productVariant->inventory->sum('quantity') : 0),
              ];
          }),
      ) !!};

      let html = `
            <div class="text-left">
                <p class="text-sm text-slate-500 mb-4">Specify quantities and details for this delivery session.</p>

                <div class="mb-6 space-y-1.5">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Dispatch From (Warehouse/Store)</label>
                    <select id="dn-location" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden">
                        ${locations.map(loc => `<option value="${loc.id}">${loc.name}</option>`).join('')}
                    </select>
                </div>

                <div class="max-h-[300px] overflow-y-auto rounded-xl border border-slate-100 mb-4 bg-white">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 font-bold text-slate-400 uppercase tracking-widest">Item</th>
                                <th class="p-3 font-bold text-slate-400 uppercase tracking-widest text-center">Remaining</th>
                                <th class="p-3 font-bold text-slate-400 uppercase tracking-widest text-center">Stock</th>
                                <th class="p-3 font-bold text-slate-400 uppercase tracking-widest text-right">Deliver Now</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            ${items.map(item => `
                                                      <tr class="${item.remaining <= 0 ? 'bg-slate-50/50 grayscale opacity-50' : ''}">
                                                          <td class="p-3">
                                                              <div class="font-bold text-slate-700 truncate max-w-[150px]">${item.name}</div>
                                                              <div class="text-[9px] text-slate-400 uppercase mt-0.5">${item.variant || '-'}</div>
                                                          </td>
                                                          <td class="p-3 text-center font-bold text-slate-600">
                                                              ${item.remaining}
                                                          </td>
                                                          <td class="p-3 text-center">
                                                              <span class="badge ${item.stock >= item.remaining ? 'badge-success' : (item.stock > 0 ? 'badge-warning' : 'badge-danger')} text-[9px] scale-90">
                                                                  ${item.stock}
                                                              </span>
                                                          </td>
                                                          <td class="p-3 text-right">
                                                              <input type="number"
                                                                     class="delivery-qty w-16 px-2 py-1 rounded-lg border border-slate-200 text-right font-bold text-indigo-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                                                                     data-item-id="${item.id}"
                                                                     value="${item.remaining}"
                                                                     min="0"
                                                                     max="${item.remaining}"
                                                                     ${item.remaining <= 0 ? 'disabled' : ''} />
                                                          </td>
                                                      </tr>
                                                  `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-between items-center mb-6 px-1">
                    <button type="button" onclick="autoFillRemaining()" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition-colors">
                        <i class="fas fa-magic mr-1"></i> Ship Remaining
                    </button>
                    <button type="button" onclick="clearAllQuantities()" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-times mr-1"></i> Clear
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Reference No.</label>
                        <input type="text" id="dn-reference" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden" placeholder="LPO / Job No.">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Vehicle No.</label>
                        <input type="text" id="dn-vehicle" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden" placeholder="AE-12345">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Internal Notes</label>
                    <textarea id="dn-notes" class="w-full p-4 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden" rows="2" placeholder="Driver instructions etc..."></textarea>
                </div>
            </div>
        `;

      Swal.fire({
        title: 'Generate Delivery Note',
        html: html,
        width: '650px',
        showCancelButton: true,
        confirmButtonText: 'Confirm & Print',
        confirmButtonColor: '#4f46e5',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          const quantities = [];
          $('.delivery-qty').each(function() {
            const val = parseFloat($(this).val()) || 0;
            if (val > 0) {
              quantities.push({
                order_item_id: $(this).data('item-id'),
                quantity: val
              });
            }
          });

          if (quantities.length === 0) {
            Swal.showValidationMessage('Please specify at least one item to deliver.');
            return false;
          }

          return $.ajax({
            url: "{{ company_route('sales-orders.delivery-note.generate', ['sales_order' => $order->uuid]) }}",
            method: 'POST',
            data: {
              _token: "{{ csrf_token() }}",
              items: quantities,
              inventory_location_id: $('#dn-location').val(),
              reference_number: $('#dn-reference').val(),
              vehicle_number: $('#dn-vehicle').val(),
              notes: $('#dn-notes').val()
            }
          }).catch(xhr => {
            Swal.showValidationMessage(`Error: ${xhr.responseJSON?.message || 'Generation failed'}`);
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Fulfillment Success!',
            text: result.value.message,
            confirmButtonColor: '#4f46e5'
          }).then(() => {
            if (result.value.redirect) {
              window.open(result.value.redirect, '_blank');
            }
            window.location.reload();
          });
        }
      });
    }

    function autoFillRemaining() {
      $('.delivery-qty').each(function() {
        if (!$(this).prop('disabled')) {
          $(this).val($(this).attr('max'));
        }
      });
    }

    function clearAllQuantities() {
      $('.delivery-qty').each(function() {
        if (!$(this).prop('disabled')) {
          $(this).val(0);
        }
      });
    }

    function confirmDeleteNote(dnUuid) {
      Swal.fire({
        title: 'Delete Delivery Note?',
        text: "This will rollback fulfilled quantities and update order status.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        confirmButtonColor: '#ef4444',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          let url = "{{ company_route('sales-orders.delivery-note.delete', ['delivery_note' => ':uuid']) }}";
          url = url.replace(':uuid', dnUuid);

          return $.ajax({
            url: url,
            method: 'DELETE',
            data: {
              _token: "{{ csrf_token() }}"
            }
          }).catch(xhr => {
            Swal.showValidationMessage(`Error: ${xhr.responseJSON?.message || 'Deletion failed'}`);
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value.ok) {
          Swal.fire('Deleted!', result.value.message, 'success').then(() => {
            window.location.reload();
          });
        }
      });
    }

    function markAsDelivered(dnUuid) {
      Swal.fire({
        title: 'Mark as Delivered?',
        text: "This will update the status to DELIVERED and record the timestamp.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Confirm Delivery',
        confirmButtonColor: '#10b981',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          let url = "{{ company_route('sales-orders.delivery-note.delivered', ['delivery_note' => ':uuid']) }}";
          url = url.replace(':uuid', dnUuid);

          return $.ajax({
            url: url,
            method: 'PATCH',
            data: {
              _token: "{{ csrf_token() }}"
            }
          }).catch(xhr => {
            Swal.showValidationMessage(`Error: ${xhr.responseJSON?.message || 'Update failed'}`);
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Delivered!',
            text: result.value.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.reload();
          });
        }
      });
    }

    // Mark as Fulfillment Button
    document.getElementById('markFulfillmentBtn')?.addEventListener('click', function() {
      Swal.fire({
        title: 'Mark as Fulfillment?',
        text: "Are you sure? If you click yes, the stock quantity will be updated according to the ordered items.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, mark as fulfilled',
        confirmButtonColor: '#f59e0b',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          return $.post(
            "{{ company_route('sales-orders.mark-as-fulfillment', ['sales_order' => $order->uuid]) }}", {
              _token: "{{ csrf_token() }}"
            }).catch(xhr => {
            Swal.showValidationMessage(`Error: ${xhr.responseJSON?.message || 'Fulfillment failed'}`);
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value.success) {
          Swal.fire({
            icon: 'success',
            title: 'Fulfilled!',
            text: result.value.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.reload();
          });
        }
      });
    });

    // Generate Invoice Button
    document.getElementById('generateInvoiceBtn')?.addEventListener('click', function() {
      const isFulfilled = {{ $order->is_fulfilled ? 'true' : 'false' }};

      if (!isFulfilled) {
        Swal.fire({
          title: 'Fulfillment Required',
          text: "Please click 'Mark as Fulfillment' button first before generating an invoice.",
          icon: 'warning',
          confirmButtonText: 'OK',
          confirmButtonColor: '#f59e0b'
        });
        return;
      }

      const btn = this;
      Swal.fire({
        title: 'Generate Invoice?',
        text: "This will create a draft invoice from this sales order.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, generate it',
        confirmButtonColor: '#10b981',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          return $.post(
            "{{ company_route('sales-orders.generate-invoice', ['sales_order' => $order->uuid]) }}", {
              _token: "{{ csrf_token() }}"
            }).catch(xhr => {
            Swal.showValidationMessage(`Error: ${xhr.responseJSON?.message || 'Generation failed'}`);
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value.success) {
          Swal.fire({
            icon: 'success',
            title: 'Invoice Generated!',
            text: result.value.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            if (result.value.redirect_url) {
              window.location.href = result.value.redirect_url;
            } else {
              window.location.reload();
            }
          });
        }
      });
    });
  </script>
@endsection
