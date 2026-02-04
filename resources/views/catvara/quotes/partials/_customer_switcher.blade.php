@php
  $currentCustomerId = $type === 'BILLING' ? $quote->customer_id : ($quote->shipping_customer_id ?? $quote->customer_id);
@endphp

@if ($customers->isEmpty())
  <div class="flex flex-col items-center justify-center py-10 text-slate-400">
    <i class="fas fa-users text-3xl mb-2 opacity-30"></i>
    <span class="text-sm font-medium">No customers found.</span>
  </div>
@else
  <div class="space-y-2 max-h-[400px] overflow-y-auto pr-2">
    @foreach ($customers as $customer)
      @php
        $isSelected = $customer->id === $currentCustomerId;
      @endphp
      <div
        class="customer-option p-3 rounded-xl border transition-all cursor-pointer hover:border-brand-300 hover:bg-brand-50/50
        {{ $isSelected ? 'border-brand-400 bg-brand-50 ring-2 ring-brand-200/50' : 'border-slate-200 bg-white' }}"
        data-customer-uuid="{{ $customer->uuid }}" data-customer-name="{{ $customer->display_name }}"
        onclick="selectCustomer('{{ $customer->uuid }}', '{{ addslashes($customer->display_name) }}', '{{ $type }}')">

        <div class="flex items-start gap-3">
          <div
            class="flex-shrink-0 w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-sm font-bold">
            {{ strtoupper(substr($customer->display_name ?? 'C', 0, 2)) }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-800 text-sm truncate">{{ $customer->display_name }}</span>
              @if ($isSelected)
                <i class="fas fa-check-circle text-brand-500 text-xs"></i>
              @endif
            </div>
            @if ($customer->customer_code)
              <span
                class="inline-block mt-0.5 px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold rounded">
                {{ $customer->customer_code }}
              </span>
            @endif
            @if ($customer->email)
              <p class="text-xs text-slate-500 truncate mt-0.5">{{ $customer->email }}</p>
            @endif
            @if ($customer->address)
              <p class="text-xs text-slate-400 truncate mt-0.5">
                {{ collect([$customer->address->city, $customer->address->country?->name])->filter()->implode(', ') }}
              </p>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endif
