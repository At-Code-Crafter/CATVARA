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
    <div class="flex items-center justify-between mb-8">
      <a href="{{ route('dashboard') }}" class="flex items-center">
        <div
          class="h-10 w-10 bg-brand-400 rounded-xl flex items-center justify-center text-white mr-3 shadow-lg shadow-brand-400/30 flex-shrink-0">
          <i class="fas fa-rocket text-xl"></i>
        </div>
        <span
          class="text-xl font-bold tracking-tight text-slate-800 logo-text">{{ setting('SITE_NAME', env('APP_NAME')) }}</span>
      </a>
      <button id="closeSidebar" class="lg:hidden text-slate-400 hover:text-rose-500 transition-colors">
        <i class="fas fa-times fa-lg"></i>
      </button>
    </div>

    <!-- User Profile in Sidebar -->
    <div class="flex items-center p-3 bg-slate-50 rounded-2xl sidebar-text">
      <div
        class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-brand-400 font-bold border border-slate-100 shadow-sm mr-3">
        {{ substr(auth()->user()->name, 0, 1) }}
      </div>
      <div class="overflow-hidden">
        <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
        <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">{{ active_company()->name ?? null }}</p>
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
      @if (auth()->user()->can('view', 'quotes') ||
              auth()->user()->can('view', 'orders') ||
              auth()->user()->can('view', 'invoices'))
        @php $isSalesActive = $isActive(['quotes.*', 'sales-orders.*', 'accounting.invoices.*', 'credit-notes.*']); @endphp
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
            @can('view', 'quotes')
              <a href="{{ company_route('quotes.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('quotes.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Quotations</span>
              </a>
            @endcan
            @can('view', 'orders')
              <a href="{{ company_route('sales-orders.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('sales-orders.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Orders</span>
              </a>
            @endcan
            @can('view', 'invoices')
              <a href="{{ company_route('accounting.invoices.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('accounting.invoices.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Invoices</span>
              </a>
            @endcan
          </div>
        </div>
      @endif

      <!-- Catalog -->
      @if (auth()->user()->can('view', 'products') ||
              auth()->user()->can('view', 'categories') ||
              auth()->user()->can('view', 'brands') ||
              auth()->user()->can('view', 'attributes'))
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
            @can('view', 'products')
              <a href="{{ company_route('catalog.products.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('catalog.products.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Products</span>
              </a>
            @endcan
            @can('view', 'categories')
              <a href="{{ company_route('catalog.categories.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['catalog.categories.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Categories</span>
              </a>
            @endcan
            @can('view', 'brands')
              <a href="{{ company_route('catalog.brands.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['catalog.brands.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Brands</span>
              </a>
            @endcan
            @can('view', 'attributes')
              <a href="{{ company_route('catalog.attributes.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('catalog.attributes.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Attributes</span>
              </a>
            @endcan
          </div>
        </div>
      @endif

      <!-- Inventory -->
      @if (auth()->user()->can('view', 'inventory') ||
              auth()->user()->can('view', 'stores') ||
              auth()->user()->can('view', 'warehouses'))
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
            @can('view', 'inventory')
              <a href="{{ company_route('inventory.inventory.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('inventory.inventory.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Stock</span>
              </a>
            @endcan
            @can('view', 'stores')
              <a href="{{ company_route('inventory.stores.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('inventory.stores.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Stores</span>
              </a>
            @endcan
            @can('view', 'warehouses')
              <a href="{{ company_route('inventory.warehouses.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('inventory.warehouses.index') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Warehouses</span>
              </a>
            @endcan
          </div>
        </div>
      @endif

      <!-- Customers -->
      @can('view', 'customers')
        <a href="{{ company_route('customers.index') }}"
          class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all group nav-link
            {{ $isActive(['customers.*']) ? 'bg-brand-50 text-brand-400' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
          <div class="w-8 flex justify-start items-center">
            <i
              class="fas fa-users {{ $isActive(['customers.*']) ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
          </div>
          <span class="sidebar-text">Customers</span>
        </a>
      @endcan

      @if (auth()->user()->can('view', 'company-profile') ||
              auth()->user()->can('view', 'company-banks') ||
              auth()->user()->can('view', 'users') ||
              auth()->user()->can('view', 'roles') ||
              auth()->user()->can('view', 'exchange-rates') ||
              auth()->user()->can('view', 'payment-methods') ||
              auth()->user()->can('view', 'payment-terms') ||
              auth()->user()->can('view', 'activity-logs'))
        <p class="px-3 pt-6 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest nav-group-header">
          Settings</p>
        @php $isSettingsActive = $isActive(['settings.profile.*', 'settings.company-profile.*', 'settings.company-banks.*', 'settings.tax-groups.*', 'settings.tax-rates.*', 'settings.users.*', 'settings.roles.*', 'settings.payment-methods.*', 'settings.payment-terms.*', 'settings.exchange-rates.*', 'activity-logs.*']); @endphp
        <div class="nav-group">
          <button
            class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-xl transition-all group
              {{ $isSettingsActive ? 'text-slate-900 bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
            onclick="$(this).next('div').slideToggle(200); $(this).find('.arrow').toggleClass('rotate-180')">
            <div class="flex items-center">
              <div class="w-8 flex justify-start items-center">
                <i
                  class="fas fa-cog {{ $isSettingsActive ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
              </div>
              <span class="sidebar-text">Settings</span>
            </div>
            <i
              class="fas fa-chevron-right text-[10px] text-slate-300 transition-transform arrow {{ $isSettingsActive ? 'rotate-90' : '' }}"></i>
          </button>
          <div class="space-y-1 mt-1 {{ $isSettingsActive ? '' : 'hidden' }}">
            @can('view', 'company-profile')
              <a href="{{ company_route('settings.company-profile.edit') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.company-profile.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Company Profile</span>
              </a>
            @endcan
            @can('view', 'company-banks')
              <a href="{{ company_route('settings.company-banks.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.company-banks.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Company Bank Accounts</span>
              </a>
              <a href="{{ company_route('settings.tax-groups.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.tax-groups.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Tax Groups</span>
              </a>
              <a href="{{ company_route('settings.tax-rates.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.tax-rates.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Tax Rates</span>
              </a>
            @endcan
            @can('view', 'users')
              <a href="{{ company_route('settings.users.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.users.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Users</span>
              </a>
            @endcan
            @can('view', 'roles')
              <a href="{{ company_route('settings.roles.index', ['company' => $company->uuid]) }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.roles.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Roles</span>
              </a>
            @endcan
            @can('view', 'exchange-rates')
              <a href="{{ company_route('settings.exchange-rates.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.exchange-rates.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Exchange Rates</span>
              </a>
            @endcan
            @can('view', 'payment-methods')
              <a href="{{ company_route('settings.payment-methods.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.payment-methods.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Payment Methods</span>
              </a>
            @endcan
            @can('view', 'payment-terms')
              <a href="{{ company_route('settings.payment-terms.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.payment-terms.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Payment Terms</span>
              </a>
            @endcan
            @can('view', 'orders')
              <a href="{{ company_route('settings.delivery-services.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['settings.delivery-services.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Delivery Services</span>
              </a>
            @endcan
            @can('view', 'activity-logs')
              <a href="{{ company_route('activity-logs.index') }}"
                class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive(['activity-logs.*']) ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
                <span class="sidebar-text">Activity Log</span>
              </a>
            @endcan
          </div>
        </div>
      @endif
    @endif

    <!-- Administration -->
    @if (auth()->user()->isSuperAdmin())
      <p class="px-3 pt-6 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest nav-group-header">
        Administration</p>

      @php $isConfigurationActive = $isActive(['tenants.*', 'users.*', 'price-channels.*', 'modules.*', 'permissions.*', 'settings.roles.*', 'admin.payment-methods.*', 'admin.payment-terms.*', 'countries.*', 'states.*', 'currencies.*', 'admin.exchange-rates.*', 'admin.activity-logs.*']); @endphp
      <div class="nav-group">
        <button
          class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-xl transition-all group
            {{ $isConfigurationActive ? 'text-slate-900 bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
          onclick="$(this).next('div').slideToggle(200); $(this).find('.arrow').toggleClass('rotate-180')">
          <div class="flex items-center">
            <div class="w-8 flex justify-start items-center">
              <i
                class="fas fa-cog {{ $isConfigurationActive ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
            </div>
            <span class="sidebar-text">Configuration</span>
          </div>
          <i
            class="fas fa-chevron-right text-[10px] text-slate-300 transition-transform arrow {{ $isConfigurationActive ? 'rotate-90' : '' }}"></i>
        </button>
        <div class="space-y-1 mt-1 {{ $isConfigurationActive ? '' : 'hidden' }}">
          <a href="{{ safe_route('tenants.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('tenants.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Companies</span>
          </a>
          <a href="{{ route('users.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('users.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Users</span>
          </a>

          @if ($companyReady)
            <a href="{{ company_route('settings.roles.index') }}"
              class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('settings.roles.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
              <span class="sidebar-text">Roles</span>
            </a>
          @endif

          <a href="{{ route('price-channels.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('price-channels.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Price Channels</span>
          </a>

          <a href="{{ route('admin.payment-methods.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('admin.payment-methods.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Payment Methods</span>
          </a>

          <a href="{{ route('admin.payment-terms.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('admin.payment-terms.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Payment Terms</span>
          </a>

          <a href="{{ route('countries.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('countries.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Countries</span>
          </a>

          <a href="{{ route('states.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('states.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">States / Locations</span>
          </a>

          <a href="{{ route('currencies.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('currencies.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Currencies</span>
          </a>

          <a href="{{ route('admin.exchange-rates.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('admin.exchange-rates.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Exchange Rates</span>
          </a>

          <a href="{{ route('modules.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('modules.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Modules</span>
          </a>

          <a href="{{ route('permissions.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('permissions.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Permissions</span>
          </a>

          <a href="{{ route('admin.activity-logs.index') }}"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium {{ $isActive('admin.activity-logs.*') ? 'text-brand-400' : 'text-slate-500 hover:text-brand-400' }}">
            <span class="sidebar-text">Activity Logs</span>
          </a>

          <a href="{{ route('log-viewer.index') }}" target="_blank"
            class="flex items-center py-2 pl-12 pr-4 text-xs font-medium text-slate-500 hover:text-brand-400">
            <span class="sidebar-text">Error Logs</span>
            <i class="fas fa-external-link-alt text-[8px] ml-1.5 opacity-50"></i>
          </a>

        </div>
      </div>
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
