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

{{-- Welcome Section --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <h4 class="text-white mb-1">
                    <i class="fas fa-chart-line mr-2"></i>
                    Welcome to {{ active_company()->name ?? 'Your Company' }}
                </h4>
                <p class="text-white-50 mb-0">Here's an overview of your business performance</p>
            </div>
        </div>
    </div>
</div>

{{-- Main Stats Row --}}
<div class="row">
    {{-- Total Orders --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ company_route('sales-orders.index') }}" class="text-decoration-none">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_orders'] ?? 0) }}</h3>
                    <p>Total Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="small-box-footer">
                    View All Orders <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>

    {{-- New Orders Today --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ company_route('sales-orders.index') }}?date_from={{ now()->format('Y-m-d') }}&date_to={{ now()->format('Y-m-d') }}" class="text-decoration-none">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <h3>{{ number_format($stats['new_orders_today'] ?? 0) }}</h3>
                    <p>New Orders Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cart-plus"></i>
                </div>
                <span class="small-box-footer">
                    View Today's Orders <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>

    {{-- Total Customers --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ company_route('customers.index') }}" class="text-decoration-none">
            <div class="small-box bg-gradient-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['total_customers'] ?? 0) }}</h3>
                    <p>Total Customers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <span class="small-box-footer">
                    View All Customers <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>

    {{-- Total Revenue --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                <p>Total Revenue</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <span class="small-box-footer">
                From Confirmed Orders <i class="fas fa-chart-line"></i>
            </span>
        </div>
    </div>
</div>

{{-- Order Status Row --}}
<div class="row">
    {{-- Pending/Draft Orders --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ company_route('sales-orders.index') }}?status_id={{ $stats['draft_status_id'] ?? '' }}" class="text-decoration-none">
            <div class="small-box bg-gradient-secondary">
                <div class="inner">
                    <h3>{{ number_format($stats['pending_orders'] ?? 0) }}</h3>
                    <p>Pending Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="small-box-footer">
                    View Pending <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>

    {{-- Confirmed Orders --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ company_route('sales-orders.index') }}?status_id={{ $stats['confirmed_status_id'] ?? '' }}" class="text-decoration-none">
            <div class="small-box bg-gradient-olive">
                <div class="inner">
                    <h3>{{ number_format($stats['confirmed_orders'] ?? 0) }}</h3>
                    <p>Confirmed Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <span class="small-box-footer">
                    View Confirmed <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>

    {{-- New Customers This Month --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ company_route('customers.index') }}?new_this_month=1" class="text-decoration-none">
            <div class="small-box bg-gradient-teal">
                <div class="inner">
                    <h3>{{ number_format($stats['new_customers_month'] ?? 0) }}</h3>
                    <p>New Customers (This Month)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <span class="small-box-footer">
                    View New Customers <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>

    {{-- Active Products --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ company_route('catalog.products.index') }}" class="text-decoration-none">
            <div class="small-box bg-gradient-indigo">
                <div class="inner">
                    <h3>{{ number_format($stats['active_products'] ?? 0) }} <small class="text-white-50">/ {{ $stats['total_products'] ?? 0 }}</small></h3>
                    <p>Active Products</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <span class="small-box-footer">
                    View Products <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>
</div>

{{-- Inventory Row --}}
<div class="row">
    {{-- Warehouses --}}
    <div class="col-lg-4 col-md-6 col-sm-6">
        <a href="{{ company_route('inventory.warehouses.index') }}" class="text-decoration-none">
            <div class="small-box bg-gradient-purple">
                <div class="inner">
                    <h3>{{ number_format($stats['total_warehouses'] ?? 0) }}</h3>
                    <p>Warehouses</p>
                </div>
                <div class="icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <span class="small-box-footer">
                    Manage Warehouses <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>

    {{-- Stores --}}
    <div class="col-lg-4 col-md-6 col-sm-6">
        <a href="{{ company_route('inventory.stores.index') }}" class="text-decoration-none">
            <div class="small-box bg-gradient-navy">
                <div class="inner">
                    <h3>{{ number_format($stats['total_stores'] ?? 0) }}</h3>
                    <p>Stores</p>
                </div>
                <div class="icon">
                    <i class="fas fa-store"></i>
                </div>
                <span class="small-box-footer">
                    Manage Stores <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>

    {{-- Pending Transfers --}}
    <div class="col-lg-4 col-md-6 col-sm-6">
        <a href="{{ company_route('inventory.transfers.index') }}?status=pending" class="text-decoration-none">
            <div class="small-box bg-gradient-orange">
                <div class="inner">
                    <h3>{{ number_format($stats['pending_transfers'] ?? 0) }}</h3>
                    <p>Pending Transfers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <span class="small-box-footer">
                    View Pending Transfers <i class="fas fa-arrow-circle-right"></i>
                </span>
            </div>
        </a>
    </div>
</div>

{{-- Quick Actions --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-gradient-dark">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-2"></i>
                    Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <a href="{{ company_route('sales-orders.create') }}" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-plus-circle mr-2"></i> New Order
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <a href="{{ company_route('customers.create') }}" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-user-plus mr-2"></i> Add Customer
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <a href="{{ company_route('catalog.products.create') }}" class="btn btn-info btn-lg btn-block">
                            <i class="fas fa-box mr-2"></i> Add Product
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <a href="{{ company_route('inventory.transfers.create') }}" class="btn btn-warning btn-lg btn-block">
                            <i class="fas fa-exchange-alt mr-2"></i> New Transfer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('head')
<style>
    .small-box {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .small-box .inner h3 {
        font-weight: 700;
        font-size: 2.2rem;
    }
    .small-box .inner p {
        font-size: 1rem;
        margin-bottom: 0;
    }
    .small-box .icon {
        transition: all 0.3s ease;
    }
    .small-box:hover .icon {
        font-size: 80px;
        opacity: 0.3;
    }
    .small-box-footer {
        background-color: rgba(0,0,0,0.1);
        display: block;
        text-align: center;
        padding: 5px 0;
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
    }
    .small-box-footer:hover {
        color: #fff;
        background-color: rgba(0,0,0,0.15);
    }
    .btn-lg {
        padding: 1rem 1.5rem;
        font-size: 1rem;
    }
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }
</style>
@endpush

