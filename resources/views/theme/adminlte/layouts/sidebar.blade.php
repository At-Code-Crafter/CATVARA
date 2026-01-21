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

  // Module visibility flags (only show if at least one route exists)
  $hasSales =
      Route::has('sales-orders.index') ||
      // Route::has('quotes.index') || // DISABLED
      Route::has('invoices.index') ||
      Route::has('credit-notes.index');
  $hasPos = Route::has('company.pos.orders.index') || Route::has('company.pos.returns.index');
  $hasWeb = Route::has('company.web.orders.index');
  $hasAccounting =
      Route::has('accounting.payments.index') ||
      Route::has('allocations.index') ||
      Route::has('refunds.index') ||
      Route::has('payment-methods.index');
  $hasInventory =
      Route::has('catalog.categories.index') ||
      Route::has('catalog.products.index') ||
      Route::has('stock-movements.index');

  $hasCustomers = Route::has('customers.index') || Route::has('customer-balances.index');
  $hasReports =
      Route::has('company.reports.sales') ||
      Route::has('company.reports.payments') ||
      Route::has('company.reports.allocations') ||
      Route::has('company.reports.outstanding');
@endphp

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

  <!-- Brand Logo -->
  <a href="{{ route('dashboard') }}" class="brand-link text-center">
    <span class="brand-text font-weight-light text-center">{{ setting('SITE_NAME', env('APP_NAME')) }}</span>
  </a>

  <div class="sidebar">

    <!-- Sidebar user panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('theme/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2"
          alt="User Image">
      </div>

      <div class="info">
        <a href="#" class="d-block">{{ auth()->user()->name }}</a>

        <div class="text-muted small" style="line-height: 1.2;">
          @if ($company)
            <i class="fas fa-building mr-1"></i> {{ $company->name }}
            @if (!empty($company->code))
              <span class="text-muted">({{ $company->code }})</span>
            @endif
          @else
            <i class="fas fa-exclamation-triangle mr-1"></i> Company not selected
          @endif
        </div>

        <div class="mt-2">
          @if ($companyReady && can_switch_company())
            <form method="POST" action="{{ route('company.switch.reset') }}" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-xs btn-outline-light">
                <i class="fas fa-random mr-1"></i> Change
              </button>
            </form>
          @elseif(!$companyReady)
            <a href="{{ route('company.select') }}" class="btn btn-xs btn-outline-light">
              <i class="fas fa-building mr-1"></i> Select Company
            </a>
          @endif
        </div>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <li class="nav-header">MAIN</li>

        {{-- Dashboard --}}
        <li class="nav-item">
          <a href="{{ route('dashboard') }}"
            class="nav-link {{ $isActive(['dashboard', 'company.dashboard']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        {{-- Company scoped modules - only show if company selected --}}
        @if ($companyReady)

          @if ($hasSales || $hasPos || $hasWeb || $hasAccounting || $hasInventory || $hasCustomers || $hasReports)
            <li class="nav-header">ADMIN RESOURCE</li>
          @endif

          {{-- Sales --}}
          @can('view', 'orders')
            @if ($hasSales)
              <li
                class="nav-item has-treeview {{ $isActive(['quotes.*', 'sales-orders.*', 'invoices.*', 'credit-notes.*']) ? 'menu-open' : '' }}">
                <a href="#"
                  class="nav-link {{ $isActive(['quotes.*', 'sales-orders.*', 'invoices.*', 'credit-notes.*']) ? 'active' : '' }}">
                  <i class="nav-icon fas fa-shopping-cart"></i>
                  <p>Sales <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">

                  {{-- Quotes Navigation --}}
                  @if (Route::has('quotes.index'))
                    <li class="nav-item">
                      <a href="{{ company_route('quotes.index') }}"
                        class="nav-link {{ $isActive('quotes.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Quotations</p>
                      </a>
                    </li>
                  @endif

                  @if (Route::has('sales-orders.index'))
                    <li class="nav-item">
                      <a href="{{ company_route('sales-orders.index') }}"
                        class="nav-link {{ $isActive('sales-orders.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Orders</p>
                      </a>
                    </li>

                    @can('create', 'orders')
                      @if (Route::has('sales.orders.create'))
                        <li class="nav-item">
                          <a href="{{ company_route('sales.orders.create') }}"
                            class="nav-link {{ $isActive('sales.orders.create') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-magic text-warning"></i>
                            <p>New Order (Wizard)</p>
                          </a>
                        </li>
                      @endif
                    @endcan
                  @endif

                  @can('view', 'invoices')
                    @if (Route::has('invoices.index'))
                      <li class="nav-item">
                        <a href="{{ company_route('invoices.index') }}"
                          class="nav-link {{ $isActive('invoices.*') ? 'active' : '' }}">
                          <i class="far fa-circle nav-icon"></i>
                          <p>Invoices</p>
                        </a>
                      </li>
                    @endif
                  @endcan

                  @can('view', 'credit-notes')
                    @if (Route::has('credit-notes.index'))
                      <li class="nav-item">
                        <a href="{{ company_route('credit-notes.index') }}"
                          class="nav-link {{ $isActive('credit-notes.*') ? 'active' : '' }}">
                          <i class="far fa-circle nav-icon"></i>
                          <p>Credit Notes</p>
                        </a>
                      </li>
                    @endif
                  @endcan

                </ul>
              </li>
            @endif
          @endcan

          {{-- POS --}}
          @can('access', 'pos')
            @if ($hasPos)
              <li class="nav-item has-treeview {{ $isActive(['pos.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isActive(['pos.*']) ? 'active' : '' }}">
                  <i class="nav-icon fas fa-cash-register"></i>
                  <p>POS <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">

                  @if (Route::has('pos.orders.index'))
                    <li class="nav-item">
                      <a href="{{ company_route('pos.orders.index') }}"
                        class="nav-link {{ $isActive('pos.orders.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>POS Orders</p>
                      </a>
                    </li>
                  @endif

                  @if (Route::has('pos.returns.index'))
                    <li class="nav-item">
                      <a href="{{ company_route('pos.returns.index') }}"
                        class="nav-link {{ $isActive('pos.returns.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>POS Returns</p>
                      </a>
                    </li>
                  @endif

                </ul>
              </li>
            @endif
          @endcan

          {{-- Web --}}
          @if ($hasWeb)
            <li class="nav-item has-treeview {{ $isActive(['web.*']) ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ $isActive(['web.*']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-globe"></i>
                <p>Web <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                @if (Route::has('web.orders.index'))
                  <li class="nav-item">
                    <a href="{{ company_route('web.orders.index') }}"
                      class="nav-link {{ $isActive('web.orders.*') ? 'active' : '' }}">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Web Orders</p>
                    </a>
                  </li>
                @endif
              </ul>
            </li>
          @endif

          {{-- Accounting --}}
          @can('view', 'payments')
            @if ($hasAccounting)
              <li
                class="nav-item has-treeview {{ $isActive(['accounting.payments.*', 'allocations.*', 'refunds.*', 'payment-methods.*']) ? 'menu-open' : '' }}">
                <a href="#"
                  class="nav-link {{ $isActive(['accounting.payments.*', 'allocations.*', 'refunds.*', 'payment-methods.*']) ? 'active' : '' }}">
                  <i class="nav-icon fas fa-file-invoice-dollar"></i>
                  <p>Accounting <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  @if (Route::has('accounting.payments.index'))
                    <li class="nav-item">
                      <a href="{{ company_route('accounting.payments.index') }}"
                        class="nav-link {{ $isActive('accounting.payments.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Payments</p>
                      </a>
                    </li>
                  @endif
                  @can('view', 'allocations')
                    @if (Route::has('allocations.index'))
                      <li class="nav-item"><a href="{{ company_route('allocations.index') }}" class="nav-link"><i
                            class="far fa-circle nav-icon"></i>
                          <p>Allocations</p>
                        </a></li>
                    @endif
                  @endcan
                  @can('view', 'refunds')
                    @if (Route::has('refunds.index'))
                      <li class="nav-item"><a href="{{ company_route('refunds.index') }}" class="nav-link"><i
                            class="far fa-circle nav-icon"></i>
                          <p>Refunds</p>
                        </a></li>
                    @endif
                  @endcan
                  @if (Route::has('payment-methods.index'))
                    <li class="nav-item"><a href="{{ company_route('payment-methods.index') }}" class="nav-link"><i
                          class="far fa-circle nav-icon"></i>
                        <p>Payment Methods</p>
                      </a></li>
                  @endif
                </ul>
              </li>
            @endif
          @endcan

          {{-- Catalog --}}
          @can('view', 'categories')
            @if ($hasInventory)
              <li
                class="nav-item has-treeview {{ $isActive(['catalog.categories.*', 'catalog.products.*', 'catalog.attributes.*']) ? 'menu-open' : '' }}">
                <a href="#"
                  class="nav-link {{ $isActive(['catalog.categories.*', 'catalog.products.*', 'catalog.attributes.*']) ? 'active' : '' }}">
                  <i class="nav-icon fas fa-boxes"></i>
                  <p>Catalog <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  @if (Route::has('catalog.categories.index'))
                    <li class="nav-item"><a href="{{ company_route('catalog.categories.index') }}"
                        class="nav-link {{ $isActive('catalog.categories.*') ? 'active' : '' }}"><i
                          class="far fa-circle nav-icon"></i>
                        <p>Categories</p>
                      </a></li>
                  @endif
                  @can('view', 'attributes')
                    @if (Route::has('catalog.attributes.index'))
                      <li class="nav-item"><a href="{{ company_route('catalog.attributes.index') }}"
                          class="nav-link {{ $isActive('catalog.attributes.*') ? 'active' : '' }}"><i
                            class="far fa-circle nav-icon"></i>
                          <p>Attributes</p>
                        </a></li>
                    @endif
                  @endcan
                  @can('view', 'products')
                    @if (Route::has('catalog.products.index'))
                      <li class="nav-item"><a href="{{ company_route('catalog.products.index') }}"
                          class="nav-link {{ $isActive('catalog.products.*') ? 'active' : '' }}"><i
                            class="far fa-circle nav-icon"></i>
                          <p>Products</p>
                        </a></li>
                    @endif
                  @endcan
                </ul>
              </li>
            @endif
          @endcan

          {{-- Inventory Management --}}
          @can('view', 'inventory')
            @if ($hasInventory)
              <li class="nav-item has-treeview {{ $isActive(['inventory.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isActive(['inventory.*']) ? 'active' : '' }}">
                  <i class="nav-icon fas fa-warehouse"></i>
                  <p>Inventory <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item"><a href="{{ company_route('inventory.inventory.index') }}"
                      class="nav-link {{ $isActive('inventory.inventory.index') ? 'active' : '' }}">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Dashboard</p>
                    </a></li>
                  @can('view', 'transfers')
                    <li class="nav-item"><a href="{{ company_route('inventory.transfers.index') }}"
                        class="nav-link {{ $isActive('inventory.transfers.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Transfers</p>
                      </a></li>
                  @endcan
                  <li class="nav-item"><a href="{{ company_route('inventory.movements') }}"
                      class="nav-link {{ $isActive('inventory.movements') ? 'active' : '' }}">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Movement History</p>
                    </a></li>
                  @can('adjust', 'inventory')
                    <li class="nav-item"><a href="{{ company_route('inventory.inventory.create') }}"
                        class="nav-link {{ $isActive('inventory.inventory.create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Adjust Stock</p>
                      </a></li>
                  @endcan
                  @can('view', 'warehouses')
                    <li class="nav-item"><a href="{{ company_route('inventory.warehouses.index') }}"
                        class="nav-link {{ $isActive('inventory.warehouses.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Warehouses</p>
                      </a></li>
                  @endcan
                  @can('view', 'stores')
                    <li class="nav-item"><a href="{{ company_route('inventory.stores.index') }}"
                        class="nav-link {{ $isActive('inventory.stores.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Stores</p>
                      </a></li>
                  @endcan
                  @can('view', 'inventory-reasons')
                    <li class="nav-item"><a href="{{ company_route('inventory.reasons.index') }}"
                        class="nav-link {{ $isActive('inventory.reasons.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Inv. Reasons</p>
                      </a></li>
                  @endcan
                </ul>
              </li>
            @endif
          @endcan

          {{-- Customers --}}
          @can('view', 'customers')
            @if ($hasCustomers)
              <li
                class="nav-item has-treeview {{ $isActive(['customers.*', 'customer-balances.*']) ? 'menu-open' : '' }}">
                <a href="#"
                  class="nav-link {{ $isActive(['customers.*', 'customer-balances.*']) ? 'active' : '' }}">
                  <i class="nav-icon fas fa-user-friends"></i>
                  <p>Customers <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  @if (Route::has('customers.index'))
                    <li class="nav-item"><a href="{{ company_route('customers.index') }}"
                        class="nav-link {{ $isActive('customers.*') ? 'active' : '' }}"><i
                          class="far fa-circle nav-icon"></i>
                        <p>Customers</p>
                      </a></li>
                  @endif
                  @if (Route::has('customer-balances.index'))
                    <li class="nav-item"><a href="{{ company_route('customer-balances.index') }}" class="nav-link"><i
                          class="far fa-circle nav-icon"></i>
                        <p>Customer Balances</p>
                      </a></li>
                  @endif
                </ul>
              </li>
            @endif
          @endcan

          {{-- Reports --}}
          @can('view', 'reports')
            @if ($hasReports)
              <li class="nav-item has-treeview {{ $isActive(['reports.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isActive(['reports.*']) ? 'active' : '' }}">
                  <i class="nav-icon fas fa-chart-line"></i>
                  <p>Reports <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  @if (Route::has('reports.sales'))
                    <li class="nav-item"><a href="{{ company_route('reports.sales') }}" class="nav-link"><i
                          class="far fa-circle nav-icon"></i>
                        <p>Sales Report</p>
                      </a></li>
                  @endif
                  @if (Route::has('reports.payments'))
                    <li class="nav-item"><a href="{{ company_route('reports.payments') }}" class="nav-link"><i
                          class="far fa-circle nav-icon"></i>
                        <p>Payment Report</p>
                      </a></li>
                  @endif
                  @if (Route::has('reports.allocations'))
                    <li class="nav-item"><a href="{{ company_route('reports.allocations') }}" class="nav-link"><i
                          class="far fa-circle nav-icon"></i>
                        <p>Allocation Report</p>
                      </a></li>
                  @endif
                  @if (Route::has('reports.outstanding'))
                    <li class="nav-item"><a href="{{ company_route('reports.outstanding') }}" class="nav-link"><i
                          class="far fa-circle nav-icon"></i>
                        <p>Outstanding Balances</p>
                      </a></li>
                  @endif
                </ul>
              </li>
            @endif
          @endcan

        @endif

        <li class="nav-header">ACCESS CONTROL</li>

        {{-- Users --}}
        @can('view', 'users')
          <li class="nav-item">
            <a href="{{ safe_route('users.index') }}" class="nav-link {{ $isActive('users.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-users"></i>
              <p>Users</p>
            </a>
          </li>
        @endcan

        @can('view', 'roles')
          @if (company_selected())
            <li class="nav-item">
              <a href="{{ company_route('settings.roles.index') }}" class="nav-link">
                <i class="nav-icon fas fa-user-shield"></i>
                <p>Roles</p>
              </a>
            </li>
          @endif
        @endcan

        {{-- Permissions --}}
        @can('view', 'permissions')
          <li class="nav-item">
            <a href="{{ safe_route('permissions.index') }}"
              class="nav-link {{ $isActive('permissions.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-key"></i>
              <p>Permissions</p>
            </a>
          </li>
        @endcan

        @can('view', 'company')
          @if (company_selected())
            <li class="nav-item">
              <a href="{{ company_route('settings.general') }}"
                class="nav-link {{ $isActive('company.settings.general') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cogs"></i>
                <p>Company Profile</p>
              </a>
            </li>
          @endif
        @endcan

        {{-- Modules --}}
        @can('view', 'modules')
          <li class="nav-item">
            <a href="{{ safe_route('modules.index') }}" class="nav-link {{ $isActive('modules.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-layer-group"></i>
              <p>Modules</p>
            </a>
          </li>
        @endcan

        <li class="nav-header">SETTINGS</li>

        {{-- Companies --}}
        @can('view', 'companies')
          <li class="nav-item">
            <a href="{{ safe_route('tenants.index') }}" class="nav-link {{ $isActive('tenants.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-building"></i>
              <p>Companies</p>
            </a>
          </li>
        @endcan

        {{-- Currencies --}}
        @can('view', 'currencies')
          <li class="nav-item">
            <a href="{{ safe_route('currencies.index') }}"
              class="nav-link {{ $isActive('currencies.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-money-bill-wave"></i>
              <p>Currencies</p>
            </a>
          </li>
        @endcan

        {{-- Payment Terms --}}
        @can('view', 'payment-terms')
          <li class="nav-item">
            <a href="{{ safe_route('payment-terms.index') }}"
              class="nav-link {{ $isActive('payment-terms.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-file-invoice"></i>
              <p>Payment Terms</p>
            </a>
          </li>
        @endcan

        {{-- Price Channels --}}
        @can('view', 'price-channels')
          <li class="nav-item">
            <a href="{{ safe_route('price-channels.index') }}"
              class="nav-link {{ $isActive('price-channels.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tags"></i>
              <p>Price Channels</p>
            </a>
          </li>
        @endcan

        {{-- Countries & States --}}
        @can('view', 'countries')
          <li class="nav-item has-treeview {{ $isActive(['countries.*', 'states.*']) ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $isActive(['countries.*', 'states.*']) ? 'active' : '' }}">
              <i class="nav-icon fas fa-globe-americas"></i>
              <p>Locations <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ safe_route('countries.index') }}"
                  class="nav-link {{ $isActive('countries.*') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Countries</p>
                </a>
              </li>
              @can('view', 'states')
                <li class="nav-item">
                  <a href="{{ safe_route('states.index') }}"
                    class="nav-link {{ $isActive('states.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>States / Provinces</p>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcan

      </ul>
    </nav>

  </div>
</aside>
