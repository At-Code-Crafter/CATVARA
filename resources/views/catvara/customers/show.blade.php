@extends('catvara.layouts.app')

@section('title', 'Customer Profile: ' . $customer->display_name)

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div class="flex items-start gap-4">
        <a href="{{ route('customers.index', $company->uuid) }}"
          class="h-10 w-10 mt-2 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:border-brand-200 hover:bg-brand-50 transition-all shadow-sm shrink-0">
          <i class="fas fa-chevron-left"></i>
        </a>
        <div class="flex items-center gap-5">
          <div
            class="h-20 w-20 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center text-3xl font-black text-slate-300 shadow-inner">
            {{ strtoupper(substr($customer->display_name, 0, 1)) }}
          </div>
          <div>
            <div class="flex items-center gap-3 mb-2">
              <span
                class="px-2 py-0.5 bg-brand-50 text-brand-500 rounded text-[9px] font-black uppercase tracking-widest">{{ $customer->type }}
                Profile</span>
              @if ($customer->is_active)
                <span
                  class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[9px] font-black uppercase tracking-widest">Active
                  Status</span>
              @else
                <span
                  class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded text-[9px] font-black uppercase tracking-widest">Disabled</span>
              @endif
            </div>
            <h1 class="text-4xl font-black text-slate-800 tracking-tight leading-none">{{ $customer->display_name }}</h1>
            <p class="text-slate-400 font-bold text-sm mt-2 flex items-center gap-2">
              <i class="far fa-envelope text-slate-300"></i> {{ $customer->email ?: 'No email restricted' }}
              <span class="h-1 w-1 rounded-full bg-slate-300 mx-1"></span>
              <i class="fas fa-phone-alt text-slate-200 text-[10px]"></i> {{ $customer->phone ?: 'No contact' }}
            </p>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('customers.edit', [$company->uuid, $customer->id]) }}" class="btn btn-white shadow-soft">
          <i class="fas fa-edit mr-2 text-slate-400"></i> Edit Profile
        </a>
        <a href="{{ route('sales-orders.create', $company->uuid) }}?customer_id={{ $customer->id }}"
          class="btn btn-primary shadow-lg shadow-brand-500/20">
          <i class="fas fa-plus mr-2"></i> New Sales Order
        </a>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="card p-6 bg-white border-slate-100 shadow-soft">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lifetime Revenue</p>
        <p class="text-2xl font-black text-slate-800">{{ money($stats['total_spent']) }}</p>
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-50">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
          <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Fulfilled Orders Only</span>
        </div>
      </div>

      <div class="card p-6 bg-white border-slate-100 shadow-soft">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Orders</p>
        <p class="text-2xl font-black text-slate-800">{{ $stats['orders_count'] }} <span
            class="text-sm text-slate-300 ml-1">Volume</span></p>
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-50">
          <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
            <span class="text-brand-500">{{ $stats['orders_draft'] }}</span> Drafts / <span
              class="text-emerald-500">{{ $stats['orders_completed'] }}</span> Done
          </span>
        </div>
      </div>

      <div class="card p-6 bg-white border-slate-100 shadow-soft">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Outstanding Balance</p>
        <p class="text-2xl font-black text-rose-500">{{ money($stats['total_overdue']) }}</p>
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-50">
          <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></span>
          <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Overdue Invoices</span>
        </div>
      </div>

      <div class="card p-6 bg-white border-slate-100 shadow-soft">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Invoice Health</p>
        <p class="text-2xl font-black text-slate-800">{{ $stats['invoices_paid'] }} <span
            class="text-slate-300 text-sm">/ {{ $stats['invoices_paid'] + $stats['invoices_unpaid'] }}</span></p>
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-50">
          <div class="w-full bg-slate-100 h-1 rounded-full overflow-hidden">
            @php
              $totalInvoices = $stats['invoices_paid'] + $stats['invoices_unpaid'];
              $paidPercent = $totalInvoices > 0 ? ($stats['invoices_paid'] / $totalInvoices) * 100 : 0;
            @endphp
            <div class="bg-emerald-500 h-full transition-all duration-1000" style="width: {{ $paidPercent }}%"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
      <!-- Details Column -->
      <div class="space-y-6">
        <div class="card bg-white border-slate-100 shadow-soft p-8">
          <h3
            class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-8 border-b border-slate-50 pb-4">
            <i class="fas fa-info-circle text-brand-400"></i> Account Data
          </h3>

          <div class="space-y-6">
            <div>
              <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Legal Name</p>
              <p class="text-sm font-bold text-slate-700">{{ $customer->legal_name ?: 'N/A' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Tax / Registration #</p>
              <p class="text-sm font-bold text-slate-700">{{ $customer->tax_number ?: 'Not provided' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Payment Policy</p>
              <p class="text-sm font-bold text-brand-500">{{ $customer->paymentTerms?->name ?: 'Standard Terms' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Default Discount</p>
              <p class="text-sm font-bold text-emerald-600">
                {{ $customer->percentage_discount > 0 ? (float) $customer->percentage_discount . '%' : '0.00%' }}</p>
            </div>
          </div>
        </div>

        <div class="card bg-white border-slate-100 shadow-soft p-8">
          <h3
            class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-8 border-b border-slate-50 pb-4">
            <i class="fas fa-map-marked-alt text-brand-400"></i> Primary Address
          </h3>

          @if ($customer->address)
            <div class="space-y-4">
              <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-sm font-medium text-slate-600 leading-relaxed whitespace-pre-line">
                  {{ $customer->address->address_line_1 }}
                  @if ($customer->address->address_line_2)
                    <br>{{ $customer->address->address_line_2 }}
                  @endif
                  <br>{{ $customer->address->city }}, {{ $customer->address->state?->name }}
                  <br>{{ $customer->address->zip_code }}
                  <br>{{ $customer->address->country?->name }}
                </p>
              </div>
              <a href="https://maps.google.com/?q={{ urlencode($customer->address->address_line_1 . ' ' . $customer->address->city) }}"
                target="_blank"
                class="flex items-center gap-2 text-[10px] font-black text-brand-500 uppercase tracking-widest hover:text-brand-600 transition-colors pl-1">
                <i class="fas fa-external-link-alt"></i> View on Maps
              </a>
            </div>
          @else
            <div class="py-8 text-center bg-slate-50 rounded-2xl border border-slate-100">
              <p class="text-xs text-slate-400 font-bold italic">No address recorded</p>
            </div>
          @endif
        </div>

        @if ($customer->notes)
          <div class="card bg-amber-50 border-amber-100 shadow-soft p-8">
            <h3 class="text-sm font-bold text-amber-900 uppercase tracking-wider flex items-center gap-2 mb-4">
              <i class="fas fa-sticky-note text-amber-500 text-xs"></i> Internal Notes
            </h3>
            <p class="text-xs font-bold text-amber-800/70 leading-relaxed">
              {{ $customer->notes }}
            </p>
          </div>
        @endif
      </div>

      <!-- Tables Column -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Recent Orders -->
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
          <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <i class="fas fa-shopping-cart text-brand-400"></i> Transaction History
            </h3>
            <span
              class="px-2 py-0.5 bg-slate-100 text-slate-400 rounded text-[9px] font-black uppercase tracking-widest">Latest
              10</span>
          </div>
          <div class="table-responsive">
            <table class="table-premium w-full text-left">
              <thead>
                <tr>
                  <th class="pl-8!">Order #</th>
                  <th>Date</th>
                  <th class="text-center">Items</th>
                  <th class="text-right">Total</th>
                  <th class="pr-8! text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                @forelse($customer->orders->take(10) as $order)
                  <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="pl-8! py-4">
                      <a href="{{ route('sales-orders.show', [$company->uuid, $order->id]) }}"
                        class="font-bold text-brand-500 hover:text-brand-600">
                        #{{ $order->id }}
                      </a>
                    </td>
                    <td class="py-4 text-xs font-bold text-slate-600">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="py-4 text-center text-xs font-black text-slate-400">
                      {{ $order->items_count ?? $order->orderItems()->count() }}</td>
                    <td class="py-4 text-right font-black text-slate-800">
                      {{ money($order->grand_total, $order->currency?->code) }}</td>
                    <td class="pr-8! py-4 text-center">
                      @php
                        $color = match ($order->status?->code) {
                            'DRAFT' => 'indigo',
                            'CONFIRMED' => 'brand',
                            'FULFILLED' => 'emerald',
                            'CANCELLED' => 'rose',
                            default => 'slate',
                        };
                      @endphp
                      <span
                        class="px-2 py-0.5 bg-{{ $color }}-50 text-{{ $color }}-600 rounded text-[9px] font-black uppercase tracking-widest">
                        {{ $order->status?->name ?: 'N/A' }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="py-12 text-center text-slate-400 font-bold text-xs italic">
                      No sales records found for this customer.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
