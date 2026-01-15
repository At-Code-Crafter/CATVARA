@php
  use Illuminate\Support\Facades\Route;

  $company = active_company();
  $companyReady = company_selected();

  $isActive = function ($names) {
      foreach ((array) $names as $n) {
          if (request()->routeIs($n)) {
              return true;
          }
      }
      return false;
  };
@endphp

<aside
  class="w-[260px] bg-white flex-shrink-0 flex flex-col border-r border-slate-100 transition-all duration-300 fixed lg:relative inset-y-0 left-0 z-50 lg:translate-x-0 transform -translate-x-full shadow-2xl lg:shadow-none"
  id="mainSidebar">

  <!-- Brand & Profile -->
  <div class="p-6 border-b border-slate-50">
    <a href="{{ route('dashboard') }}" class="flex items-center mb-8">
      <div
        class="h-10 w-10 bg-brand-400 rounded-xl flex items-center justify-center text-white mr-3 shadow-lg shadow-brand-400/30 flex-shrink-0">
        <i class="fas fa-rocket text-xl"></i>
      </div>
      <span
        class="text-xl font-bold tracking-tight text-slate-800 logo-text">{{ setting('SITE_NAME', env('APP_NAME')) }}</span>
    </a>

    <!-- User Profile in Sidebar -->
    <div class="flex items-center p-3 bg-slate-50 rounded-2xl sidebar-text">
      <div
        class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-brand-400 font-bold border border-slate-100 shadow-sm mr-3">
        {{ substr(auth()->user()->name, 0, 1) }}
      </div>
      <div class="overflow-hidden">
        <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
        <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">System Admin</p>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1 custom-scrollbar">

    <p class="px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest nav-group-header">Main Menu</p>

    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}"
      class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all group nav-link
           {{ $isActive(['dashboard', 'company.dashboard']) ? 'bg-brand-50 text-brand-400' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
      <div class="w-8 flex justify-start items-center">
        <i
          class="fas fa-th-large {{ $isActive(['dashboard', 'company.dashboard']) ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
      </div>
      <span class="sidebar-text">Dashboard</span>
    </a>

    @if ($companyReady)
      <p class="px-3 pt-6 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest nav-group-header">Business
      </p>

      <!-- Sales -->
      @php $isSalesActive = $isActive(['sales-orders.*', 'invoices.*', 'credit-notes.*']); @endphp
      <div class="nav-group">
        <button
          class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-xl transition-all group
          {{ $isSalesActive ? 'text-slate-900 bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
          onclick="$(this).next('div').slideToggle(200); $(this).find('.arrow').toggleClass('rotate-180')">
          <div class="flex items-center">
            <div class="w-8 flex justify-start items-center">
              <i
                class="fas fa-shopping-cart {{ $isSalesActive ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
            </div>
            <span class="sidebar-text">Sales</span>
          </div>
          <i
            class="fas fa-chevron-right text-[10px] text-slate-300 transition-transform arrow {{ $isSalesActive ? 'rotate-90' : '' }}"></i>
        </button>
        <div class="space-y-1 mt-1 {{ $isSalesActive ? '' : 'hidden' }}">
          <a href="{{ company_route('sales-orders.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('sales-orders.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">All Orders</span>
          </a>
          <a href="{{ company_route('invoices.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('invoices.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Invoices</span>
          </a>
        </div>
      </div>

      <!-- Catalog -->
      @php $isCatalogActive = $isActive(['catalog.*']); @endphp
      <div class="nav-group">
        <button
          class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-xl transition-all group
          {{ $isCatalogActive ? 'text-slate-900 bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
          onclick="$(this).next('div').slideToggle(200); $(this).find('.arrow').toggleClass('rotate-180')">
          <div class="flex items-center">
            <div class="w-8 flex justify-start items-center">
              <i
                class="fas fa-box {{ $isCatalogActive ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
            </div>
            <span class="sidebar-text">Catalog</span>
          </div>
          <i
            class="fas fa-chevron-right text-[10px] text-slate-300 transition-transform arrow {{ $isCatalogActive ? 'rotate-90' : '' }}"></i>
        </button>
        <div class="space-y-1 mt-1 {{ $isCatalogActive ? '' : 'hidden' }}">
          <a href="{{ company_route('catalog.products.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('catalog.products.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Products</span>
          </a>
          <a href="{{ company_route('catalog.categories.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['catalog.categories.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Categories</span>
          </a>
          <a href="{{ company_route('catalog.attributes.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('catalog.attributes.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Attributes</span>
          </a>
        </div>
      </div>

      <!-- Inventory -->
      @php $isInvActive = $isActive(['inventory.*']); @endphp
      <div class="nav-group">
        <button
          class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-xl transition-all group
          {{ $isInvActive ? 'text-slate-900 bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
          onclick="$(this).next('div').slideToggle(200); $(this).find('.arrow').toggleClass('rotate-180')">
          <div class="flex items-center">
            <div class="w-8 flex justify-start items-center">
              <i
                class="fas fa-warehouse {{ $isInvActive ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
            </div>
            <span class="sidebar-text">Inventory</span>
          </div>
          <i
            class="fas fa-chevron-right text-[10px] text-slate-300 transition-transform arrow {{ $isInvActive ? 'rotate-90' : '' }}"></i>
        </button>
        <div class="space-y-1 mt-1 {{ $isInvActive ? '' : 'hidden' }}">
          <a href="{{ company_route('inventory.inventory.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('inventory.inventory.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Stock</span>
          </a>
        </div>
      </div>

      <!-- Customers -->
      <a href="{{ company_route('customers.index') }}"
        class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all group nav-link
        {{ $isActive(['customers.*']) ? 'bg-brand-50 text-brand-400' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
        <div class="w-8 flex justify-start items-center">
          <i
            class="fas fa-users {{ $isActive(['customers.*']) ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
        </div>
        <span class="sidebar-text">Customers</span>
      </a>

      <p class="px-3 pt-6 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest nav-group-header">Settings
      </p>

      <a href="{{ safe_route('tenants.index') }}"
        class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all group nav-link
        {{ $isActive(['tenants.*']) ? 'bg-brand-50 text-brand-400' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
        <div class="w-8 flex justify-start items-center">
          <i
            class="fas fa-building {{ $isActive(['tenants.*']) ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
        </div>
        <span class="sidebar-text">Companies</span>
      </a>
    @endif

  </div>

  <!-- Bottom Actions -->
  <div class="p-6 border-t border-slate-50">
    @if ($companyReady && can_switch_company())
      <form method="POST" action="{{ route('company.switch.reset') }}">
        @csrf
        <button type="submit"
          class="w-full flex items-center justify-center px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-50 rounded-xl hover:bg-brand-50 hover:text-brand-400 transition-all active:scale-95">
          <i class="fas fa-exchange-alt mr-2"></i> <span class="sidebar-text">Switch Company</span>
        </button>
      </form>
    @elseif(!$companyReady)
      <a href="{{ route('company.select') }}"
        class="w-full flex items-center justify-center px-4 py-2.5 text-xs font-bold text-white bg-brand-400 rounded-xl hover:bg-brand-500 transition-all shadow-lg shadow-brand-400/20 active:scale-95">
        <span class="sidebar-text">Select Company</span>
      </a>
    @endif
  </div>
</aside>
