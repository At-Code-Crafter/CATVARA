<div class="flex flex-col h-full">
  <div class="p-4 border-b border-slate-100 bg-slate-50/20">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">
        Select {{ ucfirst(strtolower($type)) }} Customer
      </h3>
      <button type="button" onclick="window.hideSidebar()" class="text-slate-400 hover:text-slate-600 transition">
        <i class="fas fa-times text-sm"></i>
      </button>
    </div>

    <div class="space-y-3">
      <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="switcherSearch" oninput="debounceSwitcherSearch()"
          class="w-full pl-9 h-10 rounded-xl border border-slate-200 text-sm font-bold focus:border-brand-400 focus:ring-0 placeholder:text-slate-400"
          placeholder="Search name, email, phone...">
      </div>

      <div class="flex items-center gap-2">
        <select id="switcherType" onchange="runSwitcherSearch()"
          class="flex-1 h-9 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 focus:ring-0 bg-white shadow-sm">
          <option value="">All Types</option>
          <option value="INDIVIDUAL">Individual</option>
          <option value="COMPANY">Company</option>
        </select>
      </div>
    </div>
  </div>

  <div class="flex-1 overflow-y-auto p-2 space-y-2 no-scrollbar" id="switcherResults">
    @forelse ($customers as $customer)
      <div
        class="group p-3 rounded-xl border border-slate-100 bg-white hover:border-brand-300 hover:shadow-md transition-all cursor-pointer relative overflow-hidden"
        onclick="confirmCustomerSwitch('{{ $customer->uuid }}', '{{ $customer->display_name }}')">

        <div class="flex items-start gap-3">
          <div
            class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-brand-50 group-hover:text-brand-600 transition-colors">
            <i class="fas {{ $customer->type === 'COMPANY' ? 'fa-building' : 'fa-user' }} text-base"></i>
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-xs font-black text-slate-800 truncate group-hover:text-brand-700">
              {{ $customer->display_name }}
            </div>
            @if ($customer->legal_name && $customer->legal_name !== $customer->display_name)
              <div class="text-[10px] text-slate-400 font-bold truncate">{{ $customer->legal_name }}</div>
            @endif
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
              @if ($customer->email)
                <div class="flex items-center gap-1 text-[10px] font-bold text-slate-500">
                  <i class="far fa-envelope text-[9px]"></i> {{ $customer->email }}
                </div>
              @endif
              @if ($customer->phone)
                <div class="flex items-center gap-1 text-[10px] font-bold text-slate-500">
                  <i class="fas fa-phone text-[9px]"></i> {{ $customer->phone }}
                </div>
              @endif
            </div>
          </div>
        </div>

        <div
          class="absolute right-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all translate-x-3 group-hover:translate-x-0">
          <div class="w-7 h-7 rounded-lg bg-brand-600 text-white flex items-center justify-center text-[10px]">
            <i class="fas fa-check"></i>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center py-10">
        <i class="fas fa-users text-3xl text-slate-200 mb-2"></i>
        <p class="text-xs font-bold text-slate-400">No customers found.</p>
      </div>
    @endforelse
  </div>
</div>

<script>
  // Debounce helper for the switcher search
  let switcherTimer;

  function debounceSwitcherSearch() {
    clearTimeout(switcherTimer);
    switcherTimer = setTimeout(runSwitcherSearch, 300);
  }

  function runSwitcherSearch() {
    const q = $('#switcherSearch').val();
    const customerType = $('#switcherType').val();
    const type = '{{ $type }}';

    $('#switcherResults').addClass('opacity-50 pointer-events-none');

    $.ajax({
      url: '{{ company_route('sales-orders.customer-switcher', ['sales_order' => $order->uuid]) }}',
      data: {
        q,
        customer_type: customerType,
        type: type
      },
      success: function(html) {
        window.setSidebarContent(html);
        // refocus search after reload
        $('#switcherSearch').focus().val(q);
      }
    });
  }
</script>
