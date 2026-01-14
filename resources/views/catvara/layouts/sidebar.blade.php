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

  // Flag Checks ... (Same as before)
  $hasSales = Route::has('sales-orders.index') || Route::has('invoices.index');
  $hasPos = Route::has('company.pos.orders.index');
  $hasWeb = Route::has('company.web.orders.index');
  $hasInventory = Route::has('catalog.products.index');
  $hasCustomers = Route::has('customers.index');
@endphp

<aside
  class="w-64 bg-white flex-shrink-0 flex flex-col border-r border-slate-200 transition-transform duration-300 absolute inset-y-0 left-0 z-50 lg:relative lg:translate-x-0 transform -translate-x-full"
  id="mainSidebar">

  <!-- Brand -->
  <a href="{{ route('dashboard') }}" class="h-16 flex items-center px-6 border-b border-slate-100 flex-shrink-0">
    <div
      class="h-8 w-8 bg-brand-600 rounded-lg flex items-center justify-center text-white mr-3 shadow-lg shadow-brand-500/30 flex-shrink-0">
      <i class="fas fa-layer-group"></i>
    </div>
    <span
      class="text-lg font-bold tracking-tight text-slate-800 logo-text whitespace-nowrap">{{ setting('SITE_NAME', env('APP_NAME')) }}</span>
  </a>

  <!-- Navigation -->
  <div class="flex-1 overflow-y-auto sidebar-scroll py-6 px-3 space-y-1">

    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}"
      class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all group nav-link
           {{ $isActive(['dashboard', 'company.dashboard']) ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
      <i
        class="fas fa-home w-6 {{ $isActive(['dashboard', 'company.dashboard']) ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
      <span class="sidebar-text">Dashboard</span>
    </a>

    @if ($companyReady)
      <div class="pt-6 pb-2 px-3 nav-group-header">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Business</p>
      </div>

      <!-- Sales -->
      @php $isSalesActive = $isActive(['sales-orders.*', 'invoices.*', 'credit-notes.*']); @endphp
      <div class="nav-group">
        <button
          class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all group
                                  {{ $isSalesActive ? 'bg-slate-50 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
          onclick="$(this).next('div').slideToggle(200); $(this).find('.arrow').toggleClass('rotate-180')">
          <div class="flex items-center">
            <i
              class="fas fa-shopping-bag w-6 {{ $isSalesActive ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
            <span class="sidebar-text">Sales</span>
          </div>
          <i
            class="fas fa-chevron-down text-xs text-slate-400 transition-transform arrow {{ $isSalesActive ? 'rotate-180' : '' }}"></i>
        </button>
        <div class="space-y-1 pl-9 mt-1 {{ $isSalesActive ? '' : 'hidden' }}">
          <a href="{{ company_route('sales-orders.index') }}"
            class="block py-2 text-sm {{ $isActive('sales-orders.index') ? 'text-brand-600 font-medium' : 'text-slate-500 hover:text-slate-900' }}">
            <span class="sidebar-text">All Orders</span>
          </a>
          <a href="{{ company_route('invoices.index') }}"
            class="block py-2 text-sm {{ $isActive('invoices.index') ? 'text-brand-600 font-medium' : 'text-slate-500 hover:text-slate-900' }}">
            <span class="sidebar-text">Invoices</span>
          </a>
        </div>
      </div>

      <!-- Catalog -->
      @php $isCatalogActive = $isActive(['catalog.*']); @endphp
      <div class="nav-group">
        <button
          class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all group
                                  {{ $isCatalogActive ? 'bg-slate-50 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
          onclick="$(this).next('div').slideToggle(200); $(this).find('.arrow').toggleClass('rotate-180')">
          <div class="flex items-center">
            <i
              class="fas fa-tags w-6 {{ $isCatalogActive ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
            <span class="sidebar-text">Catalog</span>
          </div>
          <i
            class="fas fa-chevron-down text-xs text-slate-400 transition-transform arrow {{ $isCatalogActive ? 'rotate-180' : '' }}"></i>
        </button>
        <div class="space-y-1 pl-9 mt-1 {{ $isCatalogActive ? '' : 'hidden' }}">
          <a href="{{ company_route('catalog.products.index') }}"
            class="block py-2 text-sm {{ $isActive('catalog.products.index') ? 'text-brand-600 font-medium' : 'text-slate-500 hover:text-slate-900' }}">
            <span class="sidebar-text">Products</span>
          </a>
          <a href="{{ company_route('catalog.categories.index') }}"
            class="block py-2 text-sm {{ $isActive(['catalog.categories.*']) ? 'text-brand-600 font-medium' : 'text-slate-500 hover:text-slate-900' }}">
            <span class="sidebar-text">Categories</span>
          </a>
          <a href="{{ company_route('catalog.attributes.index') }}"
            class="block py-2 text-sm {{ $isActive('catalog.attributes.index') ? 'text-brand-600 font-medium' : 'text-slate-500 hover:text-slate-900' }}">
            <span class="sidebar-text">Attributes</span>
          </a>
        </div>
      </div>

      <!-- Inventory -->
      @php $isInvActive = $isActive(['inventory.*']); @endphp
      <div class="nav-group">
        <button
          class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all group
                                  {{ $isInvActive ? 'bg-slate-50 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
          onclick="$(this).next('div').slideToggle(200); $(this).find('.arrow').toggleClass('rotate-180')">
          <div class="flex items-center">
            <i
              class="fas fa-box-open w-6 {{ $isInvActive ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
            <span class="sidebar-text">Inventory</span>
          </div>
          <i
            class="fas fa-chevron-down text-xs text-slate-400 transition-transform arrow {{ $isInvActive ? 'rotate-180' : '' }}"></i>
        </button>
        <div class="space-y-1 pl-9 mt-1 {{ $isInvActive ? '' : 'hidden' }}">
          <a href="{{ company_route('inventory.inventory.index') }}"
            class="block py-2 text-sm {{ $isActive('inventory.inventory.index') ? 'text-brand-600 font-medium' : 'text-slate-500 hover:text-slate-900' }}">
            <span class="sidebar-text">Stock</span>
          </a>
        </div>
      </div>

      <!-- Customers -->
      <a href="{{ company_route('customers.index') }}"
        class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all group nav-link
                         {{ $isActive(['customers.*']) ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <i
          class="fas fa-users w-6 {{ $isActive(['customers.*']) ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
        <span class="sidebar-text">Customers</span>
      </a>

      <div class="pt-6 pb-2 px-3 nav-group-header">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Settings</p>
      </div>

      <a href="{{ safe_route('tenants.index') }}"
        class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all group nav-link
                         {{ $isActive(['tenants.*']) ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <i
          class="fas fa-building w-6 {{ $isActive(['tenants.*']) ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
        <span class="sidebar-text">Companies</span>
      </a>
    @endif

  </div>

  <!-- Bottom Actions -->
  <div class="p-4 border-t border-slate-100 bg-slate-50/50">
    @if ($companyReady && can_switch_company())
      <form method="POST" action="{{ route('company.switch.reset') }}">
        @csrf
        <button type="submit"
          class="w-full flex items-center justify-center px-4 py-2 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-brand-600 transition-colors shadow-sm overflow-hidden whitespace-nowrap">
          <i class="fas fa-exchange-alt mr-2"></i> <span class="sidebar-text">Switch Company</span>
        </button>
      </form>
    @elseif(!$companyReady)
      <a href="{{ route('company.select') }}"
        class="w-full flex items-center justify-center px-4 py-2 text-xs font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">
        <span class="sidebar-text">Select Company</span>
      </a>
    @endif
  </div>
</aside>