<aside id="sidebar"
  class="flex flex-col z-40 left-0 top-0 lg:static lg:left-auto lg:top-auto lg:translate-x-0 h-screen overflow-y-scroll lg:overflow-y-auto no-scrollbar w-64 lg:w-72 shrink-0 bg-primary p-4 transition-all duration-200 ease-in-out">
  <!-- Sidebar header -->
  <div class="flex justify-between mb-10 pr-3 sm:px-2">
    <a class="block" href="#">
      <div class="flex items-center">
        <div
          class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center text-white mr-3 shadow-lg shadow-accent/20">
          <i data-lucide="zap" class="w-6 h-6"></i>
        </div>
        <span class="text-xl font-bold text-white tracking-tight">CATVARA</span>
      </div>
    </a>
  </div>

  <!-- Links -->
  <div class="space-y-8">
    <!-- Pages group -->
    <div>
      <h3 class="text-xs uppercase text-slate-500 font-semibold pl-3 mb-4 tracking-wider">Main Menu</h3>
      <ul class="space-y-1">
        <!-- Dashboard -->
        <li
          class="px-3 py-2 rounded-xl {{ request()->routeIs('company.dashboard') ? 'bg-accent/10 text-accent' : 'bg-transparent text-slate-300' }} mb-0.5 last:mb-0">
          <a class="flex items-center transition duration-150 truncate"
            href="{{ route('company.dashboard', ['company' => active_company()->uuid]) }}">
            <i data-lucide="layout-grid"
              class="shrink-0 h-5 w-5 mr-3 {{ request()->routeIs('company.dashboard') ? 'text-accent' : 'text-slate-500' }}"></i>
            <span class="text-sm font-medium">Dashboard</span>
          </a>
        </li>

        <!-- Sales (Dropdown) -->
        <li class="px-3 py-2 rounded-xl hover:bg-slate-800/30 mb-0.5 last:mb-0 group transition-all"
          x-data="{ open: false }">
          <a
            class="flex items-center justify-between text-slate-300 group-hover:text-white transition duration-150 truncate cursor-pointer sidebar-dropdown-toggle">
            <div class="flex items-center">
              <i data-lucide="shopping-cart"
                class="shrink-0 h-5 w-5 mr-3 text-slate-500 group-hover:text-accent transition-colors"></i>
              <span class="text-sm font-medium">Sales</span>
            </div>
            <i data-lucide="chevron-down"
              class="shrink-0 h-4 w-4 text-slate-500 ml-2 transition-transform duration-200 sidebar-chevron"></i>
          </a>
          <ul class="pl-9 mt-2 hidden sidebar-submenu space-y-2">
            <li>
              <a href="{{ route('company.sales-orders.index', ['company' => active_company()->uuid]) }}"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Sales Orders</a>
            </li>
            <li>
              <a href="#"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Invoices</a>
            </li>
          </ul>
        </li>

        <!-- Catalog (Dropdown) -->
        <li class="px-3 py-2 rounded-xl hover:bg-slate-800/30 mb-0.5 last:mb-0 group transition-all">
          <a
            class="flex items-center justify-between text-slate-300 group-hover:text-white transition duration-150 truncate cursor-pointer sidebar-dropdown-toggle">
            <div class="flex items-center">
              <i data-lucide="package"
                class="shrink-0 h-5 w-5 mr-3 text-slate-500 group-hover:text-accent transition-colors"></i>
              <span class="text-sm font-medium">Catalog</span>
            </div>
            <i data-lucide="chevron-down"
              class="shrink-0 h-4 w-4 text-slate-500 ml-2 transition-transform duration-200 sidebar-chevron"></i>
          </a>
          <ul class="pl-9 mt-2 hidden sidebar-submenu space-y-2">
            <li>
              <a href="{{ route('company.catalog.products.index', ['company' => active_company()->uuid]) }}"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Products</a>
            </li>
            <li>
              <a href="{{ route('company.catalog.categories.index', ['company' => active_company()->uuid]) }}"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Categories</a>
            </li>
            <li>
              <a href="{{ route('company.catalog.attributes.index', ['company' => active_company()->uuid]) }}"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Attributes</a>
            </li>
          </ul>
        </li>

        <!-- Customers -->
        <li class="px-3 py-2 rounded-xl hover:bg-slate-800/30 mb-0.5 last:mb-0 group transition-all">
          <a class="flex items-center text-slate-300 group-hover:text-white transition duration-150 truncate"
            href="{{ route('company.customers.index', ['company' => active_company()->uuid]) }}">
            <i data-lucide="users"
              class="shrink-0 h-5 w-5 mr-3 text-slate-500 group-hover:text-accent transition-colors"></i>
            <span class="text-sm font-medium">Customers</span>
          </a>
        </li>

        <!-- Inventory (Dropdown) -->
        <li class="px-3 py-2 rounded-xl hover:bg-slate-800/30 mb-0.5 last:mb-0 group transition-all">
          <a
            class="flex items-center justify-between text-slate-300 group-hover:text-white transition duration-150 truncate cursor-pointer sidebar-dropdown-toggle">
            <div class="flex items-center">
              <i data-lucide="database"
                class="shrink-0 h-5 w-5 mr-3 text-slate-500 group-hover:text-accent transition-colors"></i>
              <span class="text-sm font-medium">Inventory</span>
            </div>
            <i data-lucide="chevron-down"
              class="shrink-0 h-4 w-4 text-slate-500 ml-2 transition-transform duration-200 sidebar-chevron"></i>
          </a>
          <ul class="pl-9 mt-2 hidden sidebar-submenu space-y-2">
            <li>
              <a href="{{ route('company.inventory.inventory.index', ['company' => active_company()->uuid]) }}"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Stock</a>
            </li>
            <li>
              <a href="{{ route('company.inventory.transfers.index', ['company' => active_company()->uuid]) }}"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Transfers</a>
            </li>
            <li>
              <a href="{{ route('company.inventory.warehouses.index', ['company' => active_company()->uuid]) }}"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Warehouses</a>
            </li>
            <li>
              <a href="{{ route('company.inventory.stores.index', ['company' => active_company()->uuid]) }}"
                class="text-sm text-slate-400 hover:text-accent transition-colors block py-1">Stores</a>
            </li>
          </ul>
        </li>
      </ul>
    </div>

    <!-- Management -->
    <div>
      <h3 class="text-xs uppercase text-slate-500 font-semibold pl-3 mb-4 tracking-wider">Management</h3>
      <ul class="space-y-1">
        <!-- Settings -->
        <li class="px-3 py-2 rounded-xl hover:bg-slate-800/30 mb-0.5 last:mb-0 group transition-all">
          <a class="flex items-center text-slate-300 group-hover:text-white transition duration-150 truncate"
            href="#">
            <i data-lucide="settings"
              class="shrink-0 h-5 w-5 mr-3 text-slate-500 group-hover:text-accent transition-colors"></i>
            <span class="text-sm font-medium">Settings</span>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Sidebar footer -->
  <div class="mt-auto pt-6 px-3">
    <div class="bg-slate-800/40 rounded-2xl p-4 border border-slate-700/50">
      <div class="flex items-center mb-1">
        <div
          class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-white mr-2">
          AP</div>
        <div>
          <div class="text-xs font-semibold text-white truncate">Admin Panel</div>
          <div class="text-[10px] text-slate-500">v2.1.0</div>
        </div>
      </div>
    </div>
  </div>
</aside>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Simple sidebar dropdown toggle using jQuery since it's available
    $('.sidebar-dropdown-toggle').on('click', function(e) {
      e.preventDefault();
      const $parent = $(this).parent();
      const $submenu = $parent.find('.sidebar-submenu');
      const $chevron = $parent.find('.sidebar-chevron');

      $submenu.slideToggle(200);

      if ($chevron.css('transform') === 'none') {
        $chevron.css('transform', 'rotate(-180deg)');
      } else {
        $chevron.css('transform', '');
      }
    });
  });
</script>
