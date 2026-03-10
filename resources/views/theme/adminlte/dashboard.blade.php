@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0">Dashboard</h1>
    </div>
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </div>
  </div>
@endsection

@section('content')

  @if (user_brand_ids()->isNotEmpty())
    <div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
      <h1 class="display-3 text-secondary font-weight-bold">{{ active_company()->name ?? 'Dashboard' }}</h1>
    </div>
  @else
    {{-- Welcome Section with Date Filter --}}
    <div class="row mb-4">
      <div class="col-12">
        <div class="card welcome-card border-0">
          <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
              <div class="d-flex align-items-center mb-2 mb-lg-0">
                <div class="welcome-icon mr-3">
                  <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                  <h4 class="text-white mb-0">{{ active_company()->name ?? 'Dashboard' }}</h4>
                  <small class="text-white-50">Business Analytics Overview</small>
                </div>
              </div>

              {{-- Date Filter --}}
              <div class="date-filter-wrapper">
                <form id="dateFilterForm" method="GET" action="{{ company_route('company.dashboard') }}"
                  class="d-flex flex-wrap align-items-center gap-2">
                  {{-- Quick Presets --}}
                  <div class="btn-group mr-2" role="group">
                    <button type="button" class="btn btn-filter-preset {{ !request('date_from') ? 'active' : '' }}"
                      data-preset="all">
                      All Time
                    </button>
                    <button type="button" class="btn btn-filter-preset" data-preset="today">
                      Today
                    </button>
                    <button type="button" class="btn btn-filter-preset" data-preset="week">
                      This Week
                    </button>
                    <button type="button" class="btn btn-filter-preset" data-preset="month">
                      This Month
                    </button>
                  </div>

                  {{-- Custom Date Range --}}
                  <div class="d-flex align-items-center">
                    <div class="input-group input-group-sm date-input-group mr-2">
                      <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                      <input type="date" name="date_from" id="date_from" class="form-control"
                        value="{{ $stats['date_from'] ?? '' }}" placeholder="From">
                    </div>
                    <span class="text-white-50 mx-1">to</span>
                    <div class="input-group input-group-sm date-input-group ml-2">
                      <input type="date" name="date_to" id="date_to" class="form-control"
                        value="{{ $stats['date_to'] ?? '' }}" placeholder="To">
                    </div>
                    <button type="submit" class="btn btn-filter-apply ml-2">
                      <i class="fas fa-filter"></i> Apply
                    </button>
                    @if (request('date_from') || request('date_to'))
                      <a href="{{ company_route('company.dashboard') }}" class="btn btn-filter-clear ml-1">
                        <i class="fas fa-times"></i>
                      </a>
                    @endif
                  </div>
                </form>
              </div>
            </div>

            @if (request('date_from') || request('date_to'))
              <div class="mt-2">
                <span class="badge badge-filter-active">
                  <i class="fas fa-filter mr-1"></i>
                  Filtered:
                  {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'Start' }}
                  → {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Now' }}
                </span>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Key Metrics Row --}}
    <div class="row">
      {{-- Total Orders --}}
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
        <a href="{{ company_route('sales-orders.index') }}" class="text-decoration-none">
          <div class="metric-card metric-blue">
            <div class="metric-icon">
              <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="metric-content">
              <div class="metric-value">{{ number_format($stats['total_orders'] ?? 0) }}</div>
              <div class="metric-label">Total Orders</div>
            </div>
            <div class="metric-footer">
              <span>View All <i class="fas fa-arrow-right ml-1"></i></span>
            </div>
          </div>
        </a>
      </div>

      {{-- New Orders Today --}}
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
        <a href="{{ company_route('sales-orders.index') }}?date_from={{ now()->format('Y-m-d') }}&date_to={{ now()->format('Y-m-d') }}"
          class="text-decoration-none">
          <div class="metric-card metric-green">
            <div class="metric-icon">
              <i class="fas fa-cart-plus"></i>
            </div>
            <div class="metric-content">
              <div class="metric-value">{{ number_format($stats['new_orders_today'] ?? 0) }}</div>
              <div class="metric-label">New Today</div>
            </div>
            <div class="metric-footer">
              <span>View Today's <i class="fas fa-arrow-right ml-1"></i></span>
            </div>
          </div>
        </a>
      </div>

      {{-- Total Customers --}}
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
        <a href="{{ company_route('customers.index') }}" class="text-decoration-none">
          <div class="metric-card metric-orange">
            <div class="metric-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="metric-content">
              <div class="metric-value">{{ number_format($stats['total_customers'] ?? 0) }}</div>
              <div class="metric-label">Total Customers</div>
            </div>
            <div class="metric-footer">
              <span>View All <i class="fas fa-arrow-right ml-1"></i></span>
            </div>
          </div>
        </a>
      </div>

      {{-- Total Revenue --}}
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
        <div class="metric-card metric-purple">
          <div class="metric-icon">
            <i class="fas fa-coins"></i>
          </div>
          <div class="metric-content">
            <div class="metric-value">{{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
            <div class="metric-label">Total Revenue</div>
          </div>
          <div class="metric-footer">
            <span><i class="fas fa-check-circle mr-1"></i> Confirmed Orders</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Charts Row 1: Revenue Trend & Order Status --}}
    <div class="row">
      {{-- Revenue Trend - Line Chart --}}
      <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card chart-card">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="card-title mb-0">
                <i class="fas fa-chart-line text-primary mr-2"></i>Revenue Trend
              </h5>
              <span class="badge badge-soft-primary">Last 6 Months</span>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 280px;">
              <canvas id="revenueChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Order Status - Donut Chart --}}
      <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card chart-card">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="card-title mb-0">
                <i class="fas fa-chart-pie text-success mr-2"></i>Order Status
              </h5>
              <span class="badge badge-soft-success">Distribution</span>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 280px;">
              <canvas id="orderStatusChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Charts Row 2: Monthly Orders & Customer Types --}}
    <div class="row">
      {{-- Monthly Orders - Bar Chart --}}
      <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card chart-card">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="card-title mb-0">
                <i class="fas fa-chart-bar text-info mr-2"></i>Monthly Orders
              </h5>
              <span class="badge badge-soft-info">Last 6 Months</span>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 250px;">
              <canvas id="monthlyOrdersChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Customer Types - Pie Chart --}}
      <div class="col-xl-3 col-lg-3 mb-4">
        <div class="card chart-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-user-tag text-warning mr-2"></i>Customer Types
            </h5>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 220px;">
              <canvas id="customerTypesChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Top Products - Horizontal Bar --}}
      <div class="col-xl-3 col-lg-3 mb-4">
        <div class="card chart-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-trophy text-danger mr-2"></i>Top Products
            </h5>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 220px;">
              <canvas id="topProductsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Status Cards Row --}}
    <div class="row">
      {{-- Pending Orders --}}
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
        <a href="{{ company_route('sales-orders.index') }}?status_id={{ $stats['draft_status_id'] ?? '' }}"
          class="text-decoration-none">
          <div class="status-card status-warning">
            <div class="status-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="status-value">{{ number_format($stats['pending_orders'] ?? 0) }}</div>
            <div class="status-label">Pending</div>
          </div>
        </a>
      </div>

      {{-- Confirmed Orders --}}
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
        <a href="{{ company_route('sales-orders.index') }}?status_id={{ $stats['confirmed_status_id'] ?? '' }}"
          class="text-decoration-none">
          <div class="status-card status-success">
            <div class="status-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="status-value">{{ number_format($stats['confirmed_orders'] ?? 0) }}</div>
            <div class="status-label">Confirmed</div>
          </div>
        </a>
      </div>

      {{-- New Customers --}}
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
        <a href="{{ company_route('customers.index') }}?new_this_month=1" class="text-decoration-none">
          <div class="status-card status-info">
            <div class="status-icon">
              <i class="fas fa-user-plus"></i>
            </div>
            <div class="status-value">{{ number_format($stats['new_customers_month'] ?? 0) }}</div>
            <div class="status-label">New This Month</div>
          </div>
        </a>
      </div>

      {{-- Products --}}
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
        <a href="{{ company_route('catalog.products.index') }}" class="text-decoration-none">
          <div class="status-card status-primary">
            <div class="status-icon">
              <i class="fas fa-boxes"></i>
            </div>
            <div class="status-value">
              {{ number_format($stats['active_products'] ?? 0) }}<small>/{{ $stats['total_products'] ?? 0 }}</small>
            </div>
            <div class="status-label">Active Products</div>
          </div>
        </a>
      </div>

      {{-- Warehouses --}}
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
        <a href="{{ company_route('inventory.warehouses.index') }}" class="text-decoration-none">
          <div class="status-card status-dark">
            <div class="status-icon">
              <i class="fas fa-warehouse"></i>
            </div>
            <div class="status-value">{{ number_format($stats['total_warehouses'] ?? 0) }}</div>
            <div class="status-label">Warehouses</div>
          </div>
        </a>
      </div>

      {{-- Stores --}}
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
        <a href="{{ company_route('inventory.stores.index') }}" class="text-decoration-none">
          <div class="status-card status-secondary">
            <div class="status-icon">
              <i class="fas fa-store"></i>
            </div>
            <div class="status-value">{{ number_format($stats['total_stores'] ?? 0) }}</div>
            <div class="status-label">Stores</div>
          </div>
        </a>
      </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row">
      <div class="col-12">
        <div class="card quick-actions-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-bolt text-warning mr-2"></i>Quick Actions
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-lg-0">
                <a href="{{ company_route('sales-orders.create') }}" class="action-btn action-primary">
                  <i class="fas fa-plus-circle"></i>
                  <span>New Order</span>
                </a>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-lg-0">
                <a href="{{ company_route('customers.create') }}" class="action-btn action-success">
                  <i class="fas fa-user-plus"></i>
                  <span>Add Customer</span>
                </a>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-lg-0">
                <a href="{{ company_route('catalog.products.create') }}" class="action-btn action-info">
                  <i class="fas fa-box"></i>
                  <span>Add Product</span>
                </a>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="{{ company_route('inventory.transfers.create') }}" class="action-btn action-warning">
                  <i class="fas fa-exchange-alt"></i>
                  <span>New Transfer</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
@endsection

@push('head')
  <style>
    /* Welcome Card */
    .welcome-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    }

    .welcome-icon {
      width: 56px;
      height: 56px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: #fff;
    }

    /* Date Filter Styles */
    .date-filter-wrapper {
      display: flex;
      align-items: center;
    }

    .btn-filter-preset {
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: rgba(255, 255, 255, 0.8);
      font-size: 12px;
      padding: 6px 12px;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .btn-filter-preset:hover,
    .btn-filter-preset.active {
      background: rgba(255, 255, 255, 0.9);
      color: #667eea;
      border-color: rgba(255, 255, 255, 0.9);
    }

    .date-input-group {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 6px;
      overflow: hidden;
    }

    .date-input-group .input-group-text {
      background: transparent;
      border: none;
      color: #667eea;
      padding: 4px 8px;
    }

    .date-input-group .form-control {
      border: none;
      font-size: 12px;
      padding: 4px 8px;
      background: transparent;
    }

    .date-input-group .form-control:focus {
      box-shadow: none;
    }

    .btn-filter-apply {
      background: rgba(255, 255, 255, 0.9);
      color: #667eea;
      font-size: 12px;
      padding: 6px 12px;
      font-weight: 600;
      border: none;
      border-radius: 6px;
      transition: all 0.2s ease;
    }

    .btn-filter-apply:hover {
      background: #fff;
      transform: translateY(-1px);
    }

    .btn-filter-clear {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      font-size: 12px;
      padding: 6px 10px;
      border: none;
      border-radius: 6px;
      transition: all 0.2s ease;
    }

    .btn-filter-clear:hover {
      background: rgba(255, 255, 255, 0.3);
      color: #fff;
    }

    .badge-filter-active {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      font-size: 12px;
      padding: 6px 12px;
      font-weight: 500;
    }

    .gap-2 {
      gap: 0.5rem;
    }

    /* Metric Cards */
    .metric-card {
      background: #fff;
      border-radius: 16px;
      padding: 24px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      height: 100%;
    }

    .metric-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .metric-icon {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      margin-bottom: 16px;
    }

    .metric-value {
      font-size: 32px;
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 4px;
    }

    .metric-label {
      font-size: 14px;
      color: #6c757d;
      font-weight: 500;
    }

    .metric-footer {
      margin-top: 16px;
      padding-top: 12px;
      border-top: 1px solid #eee;
      font-size: 13px;
      font-weight: 500;
    }

    /* Metric Card Colors */
    .metric-blue .metric-icon {
      background: rgba(59, 130, 246, 0.15);
      color: #3b82f6;
    }

    .metric-blue .metric-value {
      color: #1e40af;
    }

    .metric-blue .metric-footer {
      color: #3b82f6;
    }

    .metric-green .metric-icon {
      background: rgba(16, 185, 129, 0.15);
      color: #10b981;
    }

    .metric-green .metric-value {
      color: #065f46;
    }

    .metric-green .metric-footer {
      color: #10b981;
    }

    .metric-orange .metric-icon {
      background: rgba(245, 158, 11, 0.15);
      color: #f59e0b;
    }

    .metric-orange .metric-value {
      color: #92400e;
    }

    .metric-orange .metric-footer {
      color: #f59e0b;
    }

    .metric-purple .metric-icon {
      background: rgba(139, 92, 246, 0.15);
      color: #8b5cf6;
    }

    .metric-purple .metric-value {
      color: #5b21b6;
    }

    .metric-purple .metric-footer {
      color: #8b5cf6;
    }

    /* Chart Cards */
    .chart-card {
      background: #fff;
      border-radius: 16px;
      border: none;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .chart-card .card-header {
      background: transparent;
      border-bottom: 1px solid #f1f5f9;
      padding: 20px 24px;
    }

    .chart-card .card-title {
      font-weight: 600;
      color: #1e293b;
    }

    .chart-card .card-body {
      padding: 24px;
    }

    .chart-container {
      position: relative;
      width: 100%;
    }

    .chart-container canvas {
      max-width: 100%;
    }

    /* Badge Soft */
    .badge-soft-primary {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }

    .badge-soft-success {
      background: rgba(16, 185, 129, 0.1);
      color: #10b981;
    }

    .badge-soft-info {
      background: rgba(6, 182, 212, 0.1);
      color: #06b6d4;
    }

    /* Status Cards */
    .status-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
    }

    .status-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .status-icon {
      font-size: 24px;
      margin-bottom: 10px;
    }

    .status-value {
      font-size: 28px;
      font-weight: 700;
      line-height: 1.2;
    }

    .status-value small {
      font-size: 14px;
      opacity: 0.7;
    }

    .status-label {
      font-size: 12px;
      color: #6c757d;
      margin-top: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Status Card Colors */
    .status-warning {
      border-left-color: #f59e0b;
    }

    .status-warning .status-icon {
      color: #f59e0b;
    }

    .status-warning .status-value {
      color: #92400e;
    }

    .status-success {
      border-left-color: #10b981;
    }

    .status-success .status-icon {
      color: #10b981;
    }

    .status-success .status-value {
      color: #065f46;
    }

    .status-info {
      border-left-color: #06b6d4;
    }

    .status-info .status-icon {
      color: #06b6d4;
    }

    .status-info .status-value {
      color: #0e7490;
    }

    .status-primary {
      border-left-color: #3b82f6;
    }

    .status-primary .status-icon {
      color: #3b82f6;
    }

    .status-primary .status-value {
      color: #1e40af;
    }

    .status-dark {
      border-left-color: #475569;
    }

    .status-dark .status-icon {
      color: #475569;
    }

    .status-dark .status-value {
      color: #1e293b;
    }

    .status-secondary {
      border-left-color: #64748b;
    }

    .status-secondary .status-icon {
      color: #64748b;
    }

    .status-secondary .status-value {
      color: #334155;
    }

    /* Quick Actions */
    .quick-actions-card {
      background: #fff;
      border-radius: 16px;
      border: none;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .quick-actions-card .card-header {
      background: transparent;
      border-bottom: 1px solid #f1f5f9;
      padding: 20px 24px;
    }

    .quick-actions-card .card-body {
      padding: 24px;
    }

    .action-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      padding: 16px 24px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 15px;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .action-btn i {
      font-size: 20px;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      text-decoration: none;
    }

    .action-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
    }

    .action-primary:hover {
      color: #fff;
    }

    .action-success {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      color: #fff;
    }

    .action-success:hover {
      color: #fff;
    }

    .action-info {
      background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
      color: #fff;
    }

    .action-info:hover {
      color: #fff;
    }

    .action-warning {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: #fff;
    }

    .action-warning:hover {
      color: #fff;
    }
  </style>
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Chart.js default config
      Chart.defaults.font.family = "'Source Sans Pro', -apple-system, BlinkMacSystemFont, sans-serif";
      Chart.defaults.color = '#64748b';

      // Data from controller
      const charts = @json($charts);

      // 1. Revenue Trend - Line Chart
      new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
          labels: charts.revenue.labels,
          datasets: [{
            label: 'Revenue',
            data: charts.revenue.data,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: '#1e293b',
              titleColor: '#fff',
              bodyColor: '#fff',
              padding: 12,
              cornerRadius: 8,
              displayColors: false
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              },
              ticks: {
                font: {
                  size: 12
                }
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: '#f1f5f9'
              },
              ticks: {
                font: {
                  size: 12
                }
              }
            }
          }
        }
      });

      // 2. Order Status - Donut Chart
      const statusColors = {
        'DRAFT': '#f59e0b',
        'CONFIRMED': '#10b981',
        'CANCELLED': '#ef4444',
        'FULFILLED': '#3b82f6',
        'UNKNOWN': '#94a3b8'
      };
      const orderStatusColors = charts.orderStatus.codes.map(code => statusColors[code] || '#94a3b8');

      new Chart(document.getElementById('orderStatusChart'), {
        type: 'doughnut',
        data: {
          labels: charts.orderStatus.labels,
          datasets: [{
            data: charts.orderStatus.data,
            backgroundColor: orderStatusColors,
            borderWidth: 0,
            hoverOffset: 10
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '65%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 15,
                usePointStyle: true,
                pointStyle: 'circle'
              }
            }
          }
        }
      });

      // 3. Monthly Orders - Bar Chart
      new Chart(document.getElementById('monthlyOrdersChart'), {
        type: 'bar',
        data: {
          labels: charts.monthlyOrders.labels,
          datasets: [{
            label: 'Orders',
            data: charts.monthlyOrders.data,
            backgroundColor: [
              'rgba(102, 126, 234, 0.8)',
              'rgba(16, 185, 129, 0.8)',
              'rgba(245, 158, 11, 0.8)',
              'rgba(239, 68, 68, 0.8)',
              'rgba(139, 92, 246, 0.8)',
              'rgba(6, 182, 212, 0.8)'
            ],
            borderRadius: 8,
            borderSkipped: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: '#f1f5f9'
              },
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });

      // 4. Customer Types - Pie Chart
      new Chart(document.getElementById('customerTypesChart'), {
        type: 'pie',
        data: {
          labels: charts.customerTypes.labels,
          datasets: [{
            data: charts.customerTypes.data,
            backgroundColor: ['#667eea', '#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0,
            hoverOffset: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 12,
                usePointStyle: true,
                pointStyle: 'circle'
              }
            }
          }
        }
      });

      // 5. Top Products - Horizontal Bar Chart
      new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
          labels: charts.topProducts.labels,
          datasets: [{
            label: 'Quantity Sold',
            data: charts.topProducts.data,
            backgroundColor: 'rgba(139, 92, 246, 0.8)',
            borderRadius: 6,
            borderSkipped: false
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              grid: {
                color: '#f1f5f9'
              }
            },
            y: {
              grid: {
                display: false
              },
              ticks: {
                font: {
                  size: 11
                },
                callback: function(value) {
                  const label = this.getLabelForValue(value);
                  return label.length > 12 ? label.substr(0, 12) + '...' : label;
                }
              }
            }
          }
        }
      });

      // Date Filter Preset Buttons
      document.querySelectorAll('.btn-filter-preset').forEach(btn => {
        btn.addEventListener('click', function() {
          const preset = this.dataset.preset;
          const dateFrom = document.getElementById('date_from');
          const dateTo = document.getElementById('date_to');
          const form = document.getElementById('dateFilterForm');

          const today = new Date();
          const formatDate = (d) => d.toISOString().split('T')[0];

          switch (preset) {
            case 'all':
              dateFrom.value = '';
              dateTo.value = '';
              break;
            case 'today':
              dateFrom.value = formatDate(today);
              dateTo.value = formatDate(today);
              break;
            case 'week':
              const weekStart = new Date(today);
              weekStart.setDate(today.getDate() - today.getDay());
              dateFrom.value = formatDate(weekStart);
              dateTo.value = formatDate(today);
              break;
            case 'month':
              const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
              dateFrom.value = formatDate(monthStart);
              dateTo.value = formatDate(today);
              break;
          }

          // Submit the form
          form.submit();
        });
      });
    });
  </script>
@endpush
