@extends('catvara.layouts.app')

@section('title', 'Quote ' . $quote->quote_number)

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <div class="flex items-center gap-3">
          <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Quote #{{ $quote->quote_number }}</h2>
          @php
            $statusCode = $quote->status->code ?? 'DRAFT';
            $statusColor = match ($statusCode) {
                'DRAFT' => 'badge-warning',
                'SENT' => 'badge-info',
                'ACCEPTED' => 'badge-success',
                'REJECTED', 'EXPIRED' => 'badge-danger',
                'CONVERTED' => 'badge-primary',
                default => 'badge-secondary',
            };
          @endphp
          <span class="badge {{ $statusColor }}">
            {{ $quote->status->name ?? 'Draft' }}
          </span>
        </div>
        <p class="text-slate-400 text-sm mt-1 font-medium">Created on {{ $quote->created_at->format('M d, Y') }} at
          {{ $quote->created_at->format('h:i A') }}</p>
      </div>
      <div class="flex items-center gap-3 flex-wrap">
        @if (!$quote->order_id && in_array($statusCode, ['DRAFT', 'SENT', 'ACCEPTED']))
          @if ($statusCode !== 'SENT')
            <button type="button" id="sendQuoteBtn" class="btn btn-white">
              <i class="fas fa-paper-plane mr-2 text-blue-500"></i> Send Quote
            </button>
          @endif

          <button type="button" id="generateOrderBtn"
            class="btn btn-primary bg-linear-to-r from-emerald-500 to-emerald-600 border-none shadow-lg shadow-emerald-500/25">
            <i class="fas fa-file-invoice mr-2"></i> Generate Order
          </button>
        @endif

        @if ($quote->order_id)
          <a href="{{ company_route('sales-orders.show', ['sales_order' => $quote->order_id]) }}" class="btn btn-primary">
            <i class="fas fa-external-link-alt mr-2"></i> View Order
          </a>
        @endif

        @if ($statusCode === 'DRAFT')
          <a href="{{ company_route('quotes.edit', ['quote' => $quote->uuid]) }}" class="btn btn-white">
            <i class="fas fa-edit mr-2 text-amber-500"></i> Edit
          </a>
        @endif
        <a href="{{ company_route('quotes.print', ['quote' => $quote->uuid]) }}" target="_blank" class="btn btn-white">
          <i class="fas fa-print mr-2 text-slate-500"></i> Print
        </a>
        <a href="{{ company_route('quotes.index') }}" class="btn btn-white">
          <i class="fas fa-arrow-left mr-2 text-slate-500"></i> Back
        </a>
      </div>
    </div>

    <!-- Validity Alert -->
    @if ($quote->valid_until)
      @php
        $isExpired = $quote->valid_until->isPast();
        $daysLeft = now()->diffInDays($quote->valid_until, false);
      @endphp
      @if ($isExpired)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-500">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div>
            <p class="text-red-800 font-bold">Quote Expired</p>
            <p class="text-red-600 text-sm">This quote expired on {{ $quote->valid_until->format('M d, Y') }}.</p>
          </div>
        </div>
      @elseif ($daysLeft <= 7)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-500">
            <i class="fas fa-clock"></i>
          </div>
          <div>
            <p class="text-amber-800 font-bold">Quote Expiring Soon</p>
            <p class="text-amber-600 text-sm">This quote expires in {{ $daysLeft }} day(s) on
              {{ $quote->valid_until->format('M d, Y') }}.</p>
          </div>
        </div>
      @endif
    @endif

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
          <p class="text-lg font-bold text-brand-400">{{ $quote->customer->display_name ?? 'N/A' }}</p>
          <div class="text-sm text-slate-500 space-y-1">
            @if ($quote->shippingAddress)
              <p>{{ $quote->shippingAddress->address_line_1 }}</p>
              @if ($quote->shippingAddress->address_line_2)
                <p>{{ $quote->shippingAddress->address_line_2 }}</p>
              @endif
              <p>{{ $quote->shippingAddress->city ?? '' }}, {{ $quote->shippingAddress->state->name ?? '' }}
                {{ $quote->shippingAddress->zip_code ?? '' }}</p>
              <p>{{ $quote->shippingAddress->country->name ?? '' }}</p>
              <p class="pt-2 font-bold"><i class="fas fa-phone mr-2 text-xs"></i>
                {{ $quote->shippingAddress->phone ?? $quote->customer->phone }}</p>
              <p class="font-bold"><i class="fas fa-envelope mr-2 text-xs"></i>
                {{ $quote->shippingAddress->email ?? $quote->customer->email }}</p>
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
          @if ($quote->billingAddress)
            <p class="text-lg font-bold text-orange-400">
              {{ $quote->billingAddress->first_name ?? $quote->customer->display_name }}</p>
            <div class="text-sm text-slate-500 space-y-1">
              <p>{{ $quote->billingAddress->address_line_1 }}</p>
              @if ($quote->billingAddress->address_line_2)
                <p>{{ $quote->billingAddress->address_line_2 }}</p>
              @endif
              <p>{{ $quote->billingAddress->city ?? '' }}, {{ $quote->billingAddress->state->name ?? '' }}
                {{ $quote->billingAddress->zip_code ?? '' }}</p>
              <p>{{ $quote->billingAddress->country->name ?? '' }}</p>
            </div>
          @else
            <p class="text-slate-400 italic text-sm">Same as Shipping Address</p>
          @endif
        </div>
      </div>

      <!-- Quote Summary -->
      <div class="card p-6 border-slate-100 shadow-soft space-y-4">
        <div class="flex items-center gap-3 border-b border-slate-50 pb-4">
          <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-400">
            <i class="fas fa-info-circle text-sm"></i>
          </div>
          <h3 class="font-bold text-slate-800">Quote Summary</h3>
        </div>
        <div class="space-y-4">
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Payment Term</span>
            <span class="font-bold text-slate-700">{{ $quote->paymentTerm->name ?? 'Direct' }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Valid Until</span>
            <span
              class="font-bold {{ $quote->valid_until && $quote->valid_until->isPast() ? 'text-red-500' : 'text-green-500' }}">
              {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'N/A' }}
            </span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Currency</span>
            <span class="font-bold text-slate-700">{{ $quote->currency->code ?? 'AED' }}</span>
          </div>
          @if ($quote->order_id)
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-400 font-medium">Converted to Order</span>
              <a href="{{ company_route('sales-orders.show', ['sales_order' => $quote->order_id]) }}"
                class="font-bold text-brand-500 hover:text-brand-600">
                {{ $quote->order->order_number ?? 'View Order' }}
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Bank Details -->
    @php
      $companyBank = \App\Models\Company\CompanyBank::where('company_id', $quote->company_id)
          ->where('is_active', true)
          ->first();
    @endphp
    @if ($companyBank)
      <div class="card p-6 border-slate-100 shadow-soft relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-indigo-400"></div>
        <div class="flex items-center gap-3 mb-6">
          <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 shadow-sm">
            <i class="fas fa-university"></i>
          </div>
          <div>
            <h3 class="font-bold text-slate-800">Bank Details</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Payment Information</p>
          </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="bg-slate-50 rounded-xl p-4">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Bank Name</p>
            <p class="font-bold text-slate-800">{{ $companyBank->bank_name }}</p>
          </div>
          <div class="bg-slate-50 rounded-xl p-4">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Account Name</p>
            <p class="font-bold text-slate-800">{{ $companyBank->account_name }}</p>
          </div>
          <div class="bg-slate-50 rounded-xl p-4">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Account Number</p>
            <p class="font-bold text-slate-800 font-mono">{{ $companyBank->account_number }}</p>
          </div>
          @if ($companyBank->iban)
            <div class="bg-indigo-50 rounded-xl p-4 md:col-span-2">
              <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">IBAN</p>
              <p class="font-bold text-indigo-700 font-mono text-sm tracking-wide">{{ $companyBank->iban }}</p>
            </div>
          @endif
          @if ($companyBank->swift_code)
            <div class="bg-amber-50 rounded-xl p-4">
              <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest mb-1">SWIFT / BIC</p>
              <p class="font-bold text-amber-700 font-mono">{{ $companyBank->swift_code }}</p>
            </div>
          @endif
          @if ($companyBank->branch)
            <div class="bg-slate-50 rounded-xl p-4">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Branch</p>
              <p class="font-bold text-slate-800">{{ $companyBank->branch }}</p>
            </div>
          @endif
        </div>
      </div>
    @endif

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
            @foreach ($quote->items as $item)
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
        @if ($quote->notes)
          <div class="card p-6 bg-slate-50/50 border-none shadow-none">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-white/50 pb-2">
              Internal Notes</h4>
            <p class="text-sm text-slate-600 font-medium line-clamp-3">{{ $quote->notes }}</p>
          </div>
        @endif
      </div>
      <div class="lg:w-1/2">
        <div class="card p-6 border-slate-100 shadow-soft space-y-4">
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Subtotal</span>
            <span class="font-bold text-slate-700">{{ number_format((float) $quote->subtotal, 2) }}</span>
          </div>
          @if ($quote->discount_total > 0)
            <div class="flex justify-between items-center text-sm">
              <span class="text-emerald-500 font-medium">Total Discount</span>
              <span class="font-bold text-emerald-500">-{{ number_format((float) $quote->discount_total, 2) }}</span>
            </div>
          @endif
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Shipping & Additional</span>
            <span class="font-bold text-slate-700">{{ number_format((float) $quote->shipping_total, 2) }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-400 font-medium">Estimated Tax</span>
            <span class="font-bold text-slate-700">{{ number_format((float) $quote->tax_total, 2) }}</span>
          </div>
          <div class="pt-4 border-t border-slate-50 flex justify-between items-center">
            <span class="text-lg font-bold text-slate-800">Grand Total</span>
            <span class="text-2xl font-black text-brand-400">{{ number_format((float) $quote->grand_total, 2) }}
              {{ $quote->currency->code ?? '' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {
      // Send Quote
      $('#sendQuoteBtn').on('click', function() {
        Swal.fire({
          title: 'Send Quote?',
          text: 'This will mark the quote as sent to the customer.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, Send It',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#3b82f6'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: "{{ company_route('quotes.update', ['quote' => $quote->uuid]) }}",
              method: 'POST',
              data: {
                _method: 'PUT',
                _token: $('meta[name="csrf-token"]').attr('content'),
                action: 'send'
              },
              success: function(response) {
                Swal.fire({
                  icon: 'success',
                  title: 'Quote Sent!',
                  text: 'The quote status has been updated.',
                  timer: 2000,
                  showConfirmButton: false
                }).then(() => {
                  window.location.reload();
                });
              },
              error: function(xhr) {
                Swal.fire('Error', 'Failed to update quote status.', 'error');
              }
            });
          }
        });
      });

      // Generate Order
      $('#generateOrderBtn').on('click', function() {
        const btn = $(this);

        Swal.fire({
          title: 'Generate Order from Quote?',
          html: `
          <p class="text-sm text-slate-600 mb-3">This will create a new sales order with all the items and details from this quote.</p>
          <p class="text-sm text-slate-500">The quote will be marked as <strong>Converted</strong>.</p>
        `,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: '<i class="fas fa-file-invoice mr-2"></i> Generate Order',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#10b981'
        }).then((result) => {
          if (result.isConfirmed) {
            btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Generating...');

            $.ajax({
              url: "{{ company_route('quotes.generate-order', ['quote' => $quote->id]) }}",
              method: 'POST',
              data: {
                _token: $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                if (response.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Order Created!',
                    text: 'The sales order has been generated successfully.',
                    confirmButtonText: 'View Order',
                    showCancelButton: true,
                    cancelButtonText: 'Stay Here'
                  }).then((result) => {
                    if (result.isConfirmed && response.redirect_url) {
                      window.location.href = response.redirect_url;
                    } else {
                      window.location.reload();
                    }
                  });
                }
              },
              error: function(xhr) {
                btn.prop('disabled', false).html(
                  '<i class="fas fa-file-invoice mr-2"></i> Generate Order');
                const message = xhr.responseJSON?.message || 'Failed to generate order.';
                Swal.fire('Error', message, 'error');
              }
            });
          }
        });
      });
    });
  </script>
@endpush
