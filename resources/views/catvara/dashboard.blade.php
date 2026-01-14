@extends('catvara.layouts.app')

@section('page-title', 'Business Overview')

@section('page-actions')
  <div class="flex items-center space-x-3">
    <div class="relative">
      <input type="text" id="date_range"
        class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent"
        placeholder="Select date range">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
      </div>
    </div>
    <button
      class="flex items-center px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition-all shadow-lg shadow-accent/20">
      <i data-lucide="download" class="w-4 h-4 mr-2"></i>
      Export Report
    </button>
  </div>
@endsection

@section('content')
  <!-- Stats Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Revenue Card -->
    <div class="card p-6 border-l-4 border-l-accent">
      <div class="flex items-center justify-between mb-4">
        <div class="p-2 bg-accent/10 rounded-lg text-accent">
          <i data-lucide="dollar-sign" class="w-6 h-6"></i>
        </div>
        <span class="text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-full">+12.5%</span>
      </div>
      <div>
        <h3 class="text-slate-500 text-sm font-medium mb-1">Total Revenue</h3>
        <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
      </div>
      <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs text-slate-400">
        <i data-lucide="trending-up" class="w-3 h-3 mr-1 text-emerald-500"></i>
        Vs last month
      </div>
    </div>

    <!-- Orders Card -->
    <div class="card p-6 border-l-4 border-l-indigo-500">
      <div class="flex items-center justify-between mb-4">
        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-500">
          <i data-lucide="shopping-bag" class="w-6 h-6"></i>
        </div>
        <span
          class="text-xs font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded-full">{{ $stats['new_orders_today'] ?? 0 }}
          Today</span>
      </div>
      <div>
        <h3 class="text-slate-500 text-sm font-medium mb-1">Total Orders</h3>
        <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_orders'] ?? 0) }}</p>
      </div>
      <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs text-slate-400">
        <i data-lucide="clock" class="w-3 h-3 mr-1 text-indigo-500"></i>
        Updated just now
      </div>
    </div>

    <!-- Customers Card -->
    <div class="card p-6 border-l-4 border-l-rose-500">
      <div class="flex items-center justify-between mb-4">
        <div class="p-2 bg-rose-50 rounded-lg text-rose-500">
          <i data-lucide="users" class="w-6 h-6"></i>
        </div>
        <span
          class="text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-full">+{{ $stats['new_customers_month'] ?? 0 }}
          New</span>
      </div>
      <div>
        <h3 class="text-slate-500 text-sm font-medium mb-1">Active Customers</h3>
        <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_customers'] ?? 0) }}</p>
      </div>
      <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs text-slate-400">
        <i data-lucide="heart" class="w-3 h-3 mr-1 text-rose-500"></i>
        Customer loyalty
      </div>
    </div>

    <!-- Products Card -->
    <div class="card p-6 border-l-4 border-l-amber-500">
      <div class="flex items-center justify-between mb-4">
        <div class="p-2 bg-amber-50 rounded-lg text-amber-500">
          <i data-lucide="package" class="w-6 h-6"></i>
        </div>
        <span
          class="text-xs font-bold text-amber-500 bg-amber-50 px-2 py-1 rounded-full">{{ number_format($stats['active_products'] ?? 0) }}
          Active</span>
      </div>
      <div>
        <h3 class="text-slate-500 text-sm font-medium mb-1">Total Inventory</h3>
        <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_products'] ?? 0) }}</p>
      </div>
      <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs text-slate-400">
        <i data-lucide="alert-triangle" class="w-3 h-3 mr-1 text-amber-500"></i>
        Check low stock
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Main Revenue Chart -->
    <div class="lg:col-span-2 card">
      <div class="p-6 border-b border-slate-50 flex items-center justify-between">
        <h3 class="font-bold text-slate-800">Revenue Performance</h3>
        <div class="flex items-center space-x-2">
          <span class="flex items-center text-xs text-slate-500">
            <span class="w-3 h-3 rounded-full bg-accent mr-1"></span> Revenue
          </span>
          <span class="flex items-center text-xs text-slate-500">
            <span class="w-3 h-3 rounded-full bg-slate-200 mr-1"></span> Previous
          </span>
        </div>
      </div>
      <div class="p-6">
        <div id="revenueChart" class="min-h-[350px]"></div>
      </div>
    </div>

    <!-- Order Status Distribution -->
    <div class="card">
      <div class="p-6 border-b border-slate-50">
        <h3 class="font-bold text-slate-800">Order Status</h3>
      </div>
      <div class="p-6 flex flex-col justify-center items-center h-full">
        <div id="orderStatusChart"></div>
        <div class="grid grid-cols-2 gap-4 w-full mt-6">
          @foreach ($charts['orderStatus']['labels'] as $index => $label)
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
              <div class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-accent mr-2"></span>
                <span class="text-xs font-medium text-slate-600">{{ $label }}</span>
              </div>
              <span class="text-xs font-bold text-slate-800">{{ $charts['orderStatus']['data'][$index] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <!-- Tables Row -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Top Products -->
    <div class="card">
      <div class="p-6 border-b border-slate-50 flex items-center justify-between">
        <h3 class="font-bold text-slate-800">Top Selling Products</h3>
        <button class="text-xs font-bold text-accent hover:underline">View All</button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Product</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Sold</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Trend</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            @foreach ($charts['topProducts']['labels'] as $index => $name)
              <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 mr-3">
                      <i data-lucide="image" class="w-5 h-5"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">{{ $name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-right text-sm font-bold text-slate-800">
                  {{ $charts['topProducts']['data'][$index] }}</td>
                <td class="px-6 py-4 text-right">
                  <span
                    class="inline-flex items-center px-2 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-bold">
                    <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i>
                    Growth
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- System Updates / Notifications -->
    <div class="card">
      <div class="p-6 border-b border-slate-50">
        <h3 class="font-bold text-slate-800">Recent Activity</h3>
      </div>
      <div class="p-6">
        <div
          class="relative pl-8 space-y-6 before:absolute before:left-3 before:top-2 before:bottom-2 before:w-px before:bg-slate-100">
          <div class="relative">
            <div
              class="absolute -left-8 top-1.5 w-6 h-6 rounded-full bg-accent flex items-center justify-center text-white border-4 border-white">
              <i data-lucide="shopping-cart" class="w-3 h-3"></i>
            </div>
            <div>
              <p class="text-sm text-slate-700"><span class="font-bold">New Order</span> #SO-2024-001 received from
                <span class="font-bold text-accent">Tech Solutions</span></p>
              <span class="text-xs text-slate-400">2 minutes ago</span>
            </div>
          </div>
          <div class="relative">
            <div
              class="absolute -left-8 top-1.5 w-6 h-6 rounded-full bg-amber-500 flex items-center justify-center text-white border-4 border-white">
              <i data-lucide="alert-circle" class="w-3 h-3"></i>
            </div>
            <div>
              <p class="text-sm text-slate-700"><span class="font-bold">Inventory Alert:</span> "Wireless Headphones"
                reached minimum stock level.</p>
              <span class="text-xs text-slate-400">45 minutes ago</span>
            </div>
          </div>
          <div class="relative">
            <div
              class="absolute -left-8 top-1.5 w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-white border-4 border-white">
              <i data-lucide="user-plus" class="w-3 h-3"></i>
            </div>
            <div>
              <p class="text-sm text-slate-700"><span class="font-bold">New Customer</span> registered: Sarah Williams
                (Corporate)</p>
              <span class="text-xs text-slate-400">3 hours ago</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Revenue Chart
      var revenueOptions = {
        series: [{
          name: 'Revenue',
          data: @json($charts['revenue']['data'])
        }],
        chart: {
          height: 350,
          type: 'area',
          toolbar: {
            show: false
          },
          fontFamily: 'Inter, sans-serif'
        },
        dataLabels: {
          enabled: false
        },
        colors: ['#3b82f6'],
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100, 100, 100]
          }
        },
        stroke: {
          curve: 'smooth',
          width: 3
        },
        grid: {
          borderColor: '#f1f5f9',
          strokeDashArray: 4
        },
        xaxis: {
          categories: @json($charts['revenue']['labels']),
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
          labels: {
            style: {
              colors: '#64748b',
              fontSize: '12px'
            }
          }
        },
        yaxis: {
          labels: {
            formatter: function(val) {
              return '$' + val.toLocaleString();
            },
            style: {
              colors: '#64748b',
              fontSize: '12px'
            }
          }
        }
      };
      var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
      revenueChart.render();

      // Order Status Chart
      var statusOptions = {
        series: @json($charts['orderStatus']['data']),
        chart: {
          type: 'donut',
          height: 250,
        },
        labels: @json($charts['orderStatus']['labels']),
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#64748b'],
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        plotOptions: {
          pie: {
            donut: {
              size: '75%',
              labels: {
                show: true,
                total: {
                  show: true,
                  label: 'Total Orders',
                  fontSize: '12px',
                  fontWeight: 500,
                  color: '#64748b',
                  formatter: function(w) {
                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                  }
                }
              }
            }
          }
        }
      };
      var statusChart = new ApexCharts(document.querySelector("#orderStatusChart"), statusOptions);
      statusChart.render();
    });
  </script>
@endsection
