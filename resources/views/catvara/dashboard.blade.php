@extends('catvara.layouts.app')

@section('title', 'Overview')

@section('content')
  <div class="space-y-8">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Welcome back, {{ auth()->user()->name }}</h2>
        <p class="text-slate-500 text-sm mt-1">Here's what's happening with your business today.</p>
      </div>
      <div class="flex items-center gap-2">
        <form action="{{ route('dashboard') }}" method="GET"
          class="flex items-center gap-2 bg-white p-1 rounded-lg border border-slate-200 shadow-sm">
          <input type="date" name="date_from" value="{{ $stats['date_from'] }}"
            class="border-none text-xs text-slate-600 focus:ring-0 bg-transparent">
          <input type="date" name="date_to" value="{{ $stats['date_to'] }}"
            class="border-none text-xs text-slate-600 focus:ring-0 bg-transparent">
          <button type="submit" class="bg-brand-50 text-brand-600 hover:bg-brand-100 p-1.5 rounded-md transition-colors">
            <i class="fas fa-search"></i>
          </button>
        </form>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Revenue -->
      <div
        class="bg-white p-6 rounded-2xl shadow-soft border border-slate-100 hover:shadow-lg transition-all duration-300 group">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-slate-500">Total Revenue</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($stats['total_revenue'], 2) }}</h3>
          </div>
          <div class="p-3 bg-brand-50 rounded-xl text-brand-600 group-hover:scale-110 transition-transform">
            <i class="fas fa-dollar-sign text-lg"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-xs">
          <span class="text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full mr-2">
            <i class="fas fa-arrow-up mr-1"></i> 12%
          </span>
          <span class="text-slate-400 mt-0.5">vs last month</span>
        </div>
      </div>

      <!-- Orders -->
      <div
        class="bg-white p-6 rounded-2xl shadow-soft border border-slate-100 hover:shadow-lg transition-all duration-300 group">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-slate-500">Total Orders</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($stats['total_orders']) }}</h3>
          </div>
          <div class="p-3 bg-blue-50 rounded-xl text-blue-600 group-hover:scale-110 transition-transform">
            <i class="fas fa-shopping-bag text-lg"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-xs">
          <span class="text-brand-600 font-semibold mr-2">
            {{ $stats['new_orders_today'] }} New
          </span>
          <span class="text-slate-400 mt-0.5">orders today</span>
        </div>
      </div>

      <!-- Customers -->
      <div
        class="bg-white p-6 rounded-2xl shadow-soft border border-slate-100 hover:shadow-lg transition-all duration-300 group">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-slate-500">Active Customers</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($stats['total_customers']) }}</h3>
          </div>
          <div class="p-3 bg-purple-50 rounded-xl text-purple-600 group-hover:scale-110 transition-transform">
            <i class="fas fa-users text-lg"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-xs">
          <span class="text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full mr-2">
            <i class="fas fa-plus mr-1"></i> {{ $stats['new_customers_month'] }}
          </span>
          <span class="text-slate-400 mt-0.5">this month</span>
        </div>
      </div>

      <!-- Pending Actions -->
      <div
        class="bg-white p-6 rounded-2xl shadow-soft border border-slate-100 hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
        <div
          class="absolute right-0 top-0 w-16 h-16 bg-gradient-to-br from-amber-100 to-transparent opacity-50 rounded-bl-full -mr-4 -mt-4">
        </div>
        <div class="flex justify-between items-start relative z-10">
          <div>
            <p class="text-sm font-medium text-slate-500">Pending Orders</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($stats['pending_orders']) }}</h3>
          </div>
          <div class="p-3 bg-amber-50 rounded-xl text-amber-600">
            <i class="fas fa-clock text-lg"></i>
          </div>
        </div>
        @if($stats['pending_orders'] > 0)
          <div class="mt-4">
            <a href="{{ company_route('sales-orders.index') }}"
              class="text-xs font-semibold text-amber-600 hover:text-amber-700 flex items-center">
              Review Orders <i class="fas fa-arrow-right ml-1"></i>
            </a>
          </div>
        @else
          <div class="mt-4 text-xs text-slate-400 flex items-center">
            <i class="fas fa-check text-emerald-500 mr-1"></i> All clear
          </div>
        @endif
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Line Chart -->
      <div class="bg-white p-6 rounded-2xl shadow-soft border border-slate-100 lg:col-span-2">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-bold text-slate-800">Revenue Analytics</h3>
          <div class="flex gap-2">
            <span class="flex items-center gap-1 text-xs text-brand-600 bg-brand-50 px-2 py-1 rounded-md font-medium">
              <span class="w-2 h-2 rounded-full bg-brand-600"></span> Sales
            </span>
            <span class="flex items-center gap-1 text-xs text-slate-500 bg-slate-50 px-2 py-1 rounded-md font-medium">
              <span class="w-2 h-2 rounded-full bg-blue-400"></span> Orders
            </span>
          </div>
        </div>
        <div class="relative h-72 w-full">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>

      <!-- Donut Chart -->
      <div class="bg-white p-6 rounded-2xl shadow-soft border border-slate-100">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Order Status</h3>
        <div class="relative h-64 flex justify-center items-center">
          <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-6 space-y-2">
          <!-- Legend constructed in JS or simply shown here -->
          <p class="text-center text-xs text-slate-400">Distribution of order statuses</p>
        </div>
      </div>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
      <div class="p-6 border-b border-slate-50 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-800">Top Performing Products</h3>
        <button class="text-sm text-brand-600 font-medium hover:text-brand-700">View Report</button>
      </div>
      <div class="p-0">
        <table class="w-full text-left border-collapse">
          <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
            <tr>
              <th class="px-6 py-4">Product</th>
              <th class="px-6 py-4 text-right">Units Sold</th>
              <th class="px-6 py-4 text-right">Trend</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            @foreach($charts['topProducts']['labels'] as $index => $name)
              <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded bg-slate-100 flex items-center justify-center text-slate-400">
                      <i class="fas fa-box"></i>
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ $name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-sm font-bold text-slate-800">{{ $charts['topProducts']['data'][$index] }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <!-- Mock Trend -->
                  <div
                    class="inline-flex items-center text-emerald-600 text-xs font-semibold bg-emerald-50 px-2 py-0.5 rounded-full">
                    <i class="fas fa-arrow-trend-up mr-1"></i> High
                  </div>
                </td>
              </tr>
            @endforeach
            @if(empty($charts['topProducts']['labels']))
              <tr>
                <td colspan="3" class="px-6 py-8 text-center text-slate-400 text-sm">No data available</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>

  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {

      // --- Shared Chart Defaults ---
      Chart.defaults.font.family = "'Inter', sans-serif";
      Chart.defaults.color = '#64748b';

      // --- Revenue Chart (Gradient) ---
      const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
      const gradientRevenue = ctxRevenue.createLinearGradient(0, 0, 0, 400);
      gradientRevenue.addColorStop(0, 'rgba(79, 70, 229, 0.2)'); // Brand 600
      gradientRevenue.addColorStop(1, 'rgba(79, 70, 229, 0)');

      new Chart(ctxRevenue, {
        type: 'line',
        data: {
          labels: {!! json_encode($charts['revenue']['labels']) !!},
          datasets: [
            {
              label: 'Revenue',
              data: {!! json_encode($charts['revenue']['data']) !!},
              borderColor: '#4f46e5',
              backgroundColor: gradientRevenue,
              borderWidth: 3,
              pointBackgroundColor: '#ffffff',
              pointBorderColor: '#4f46e5',
              pointBorderWidth: 2,
              pointRadius: 4,
              pointHoverRadius: 6,
              tension: 0.4,
              fill: true,
              yAxisID: 'y'
            },
            {
              label: 'Orders',
              data: {!! json_encode($charts['revenue']['orders']) !!},
              borderColor: '#93c5fd', // Blue 300
              borderWidth: 2,
              borderDash: [5, 5],
              pointRadius: 0,
              tension: 0.4,
              fill: false,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#1e293b',
              padding: 12,
              titleFont: { size: 13 },
              bodyFont: { size: 12 },
              cornerRadius: 8,
              displayColors: false
            }
          },
          scales: {
            x: { grid: { display: false } },
            y: {
              position: 'left',
              grid: { borderDash: [4, 4], color: '#f1f5f9' },
              ticks: { callback: function (value) { return '$' + value; } }
            },
            y1: { display: false }
          }
        }
      });

      // --- Status Chart (Modern Donut) ---
      const ctxStatus = document.getElementById('statusChart').getContext('2d');
      new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
          labels: {!! json_encode($charts['orderStatus']['labels']) !!},
          datasets: [{
            data: {!! json_encode($charts['orderStatus']['data']) !!},
            backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            borderWidth: 0,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '75%',
          plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 20 } }
          }
        }
      });
    });
  </script>
@endpush