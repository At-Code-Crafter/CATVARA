@extends('catvara.layouts.app')

@section('title', 'Dashboard')

@section('content')

  @if (user_brand_ids()->isNotEmpty())

    <div class="text-center text-3xl font-bold text-slate-800 tracking-tight">{{ active_company()->name }}</div>
  @else
    <div class="space-y-8 animate-fade-in pb-12">
      <!-- Header & Date Filter -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div>
          <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Dashboard Overview</h1>
          <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
            Real-time business intelligence for <span class="text-brand-400">{{ active_company()->name }}</span>
          </p>
        </div>
        <form action="{{ route('company.dashboard', ['company' => active_company()->uuid]) }}" method="GET"
          class="flex items-center gap-3">
          <div class="input-icon-group group">
            <i class="far fa-calendar-alt text-brand-400 group-hover:scale-110 transition-transform"></i>
            <input type="text" name="date_range" id="date_range"
              class="bg-white py-2.5 rounded-xl border border-slate-200 shadow-sm text-xs font-bold text-slate-600 focus:ring-2 focus:ring-brand-400 outline-none w-64 cursor-pointer group-hover:border-brand-200 transition-all placeholder:text-slate-400"
              placeholder="Select Date Range">
            <input type="hidden" name="date_from" id="date_from" value="{{ $dateFrom->format('Y-m-d') }}">
            <input type="hidden" name="date_to" id="date_to" value="{{ $dateTo->format('Y-m-d') }}">
            <i class="fas fa-chevron-down text-slate-300 absolute right-4 top-1/2 -translate-y-1/2 text-[10px]"></i>
          </div>
          <button type="submit" class="btn btn-primary shadow-lg shadow-brand-500/30">
            <i class="fas fa-filter mr-2"></i> Filter
          </button>
        </form>
      </div>

      <!-- 1. Stats Row (5 Columns) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">

        {{-- Products --}}
        <div class="card p-6 border-b-4 border-b-blue-400 hover:-translate-y-1 transition-transform duration-300">
          <div class="flex justify-between items-start mb-4">
            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl">
              <i data-lucide="package"></i>
            </div>
            <span
              class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-wider">Total</span>
          </div>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Products</p>
          <p class="text-2xl font-black text-slate-800">{{ number_format($stats['total_products']) }}</p>
          <div class="mt-4 pt-3 border-t border-slate-50 space-y-1">
            <div class="flex justify-between text-[10px]">
              <span class="text-slate-500 font-bold">Variants</span>
              <span class="font-black text-slate-700">{{ number_format($stats['total_variants']) }}</span>
            </div>
            <div class="flex justify-between text-[10px]">
              <span class="text-slate-500 font-bold">Low Stock</span>
              <span class="font-black text-rose-500">{{ number_format($stats['low_stock_variants']) }}</span>
            </div>
          </div>
        </div>

        {{-- Categories --}}
        <div class="card p-6 border-b-4 border-b-indigo-400 hover:-translate-y-1 transition-transform duration-300">
          <div class="flex justify-between items-start mb-4">
            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl">
              <i data-lucide="layers"></i>
            </div>
            <span
              class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-wider">Total</span>
          </div>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Categories</p>
          <p class="text-2xl font-black text-slate-800">{{ number_format($stats['total_categories']) }}</p>
          <div class="mt-4 pt-3 border-t border-slate-50">
            <div class="flex items-center gap-1 text-[10px] text-slate-400">
              <i data-lucide="check-circle" class="w-3 h-3 text-emerald-500"></i> Active Catalog
            </div>
          </div>
        </div>

        {{-- Customers --}}
        <div class="card p-6 border-b-4 border-b-emerald-400 hover:-translate-y-1 transition-transform duration-300">
          <div class="flex justify-between items-start mb-4">
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
              <i data-lucide="users"></i>
            </div>
            <span
              class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-wider">Clients</span>
          </div>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Customers</p>
          <p class="text-2xl font-black text-slate-800">{{ number_format($stats['total_customers']) }}</p>
          <div class="mt-4 pt-3 border-t border-slate-50 space-y-1">
            <div class="flex justify-between text-[10px]">
              <span class="text-slate-500 font-bold">B2B (Company)</span>
              <span class="font-black text-slate-700">{{ number_format($stats['b2b_customers']) }}</span>
            </div>
            <div class="flex justify-between text-[10px]">
              <span class="text-slate-500 font-bold">B2C (Individual)</span>
              <span class="font-black text-slate-700">{{ number_format($stats['b2c_customers']) }}</span>
            </div>
          </div>
        </div>

        {{-- Orders --}}
        <div class="card p-6 border-b-4 border-b-orange-400 hover:-translate-y-1 transition-transform duration-300">
          <div class="flex justify-between items-start mb-4">
            <div class="h-10 w-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl">
              <i data-lucide="shopping-cart"></i>
            </div>
            <span
              class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded text-[9px] font-black uppercase tracking-wider">Filtered</span>
          </div>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Orders</p>
          <p class="text-2xl font-black text-slate-800">{{ number_format($stats['total_orders']) }}</p>
          <div class="mt-4 pt-3 border-t border-slate-50 space-y-1">
            <div class="flex justify-between text-[10px]">
              <span class="text-slate-500 font-bold">Confirmed</span>
              <span class="font-black text-emerald-600">{{ number_format($stats['confirmed_orders']) }}</span>
            </div>
            <div class="flex justify-between text-[10px]">
              <span class="text-slate-500 font-bold">Draft</span>
              <span class="font-black text-slate-400">{{ number_format($stats['draft_orders']) }}</span>
            </div>
          </div>
        </div>

        {{-- Sales --}}
        <div
          class="card p-6 border-b-4 border-b-teal-400 hover:-translate-y-1 transition-transform duration-300 bg-teal-50/20">
          <div class="flex justify-between items-start mb-4">
            <div
              class="h-10 w-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-xl shadow-lg shadow-teal-500/20">
              <i data-lucide="banknote"></i>
            </div>
            <span
              class="px-2 py-0.5 bg-teal-100 text-teal-600 rounded text-[9px] font-black uppercase tracking-wider">Revenue</span>
          </div>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Sales</p>
          <p class="text-2xl font-black text-slate-800">0</p>
          <div class="mt-4 pt-3 border-t border-slate-100 space-y-1">
            <div class="flex justify-between text-[10px]">
              <span class="text-slate-500 font-bold">Paid</span>
              <span class="font-black text-emerald-600">0</span>
            </div>
            <div class="flex justify-between text-[10px]">
              <span class="text-slate-500 font-bold">Unpaid</span>
              <span class="font-black text-rose-500">0</span>
            </div>
          </div>
        </div>

      </div>

      <!-- 2. Charts Row (Financials & Pie) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Line Chart --}}
        <div class="lg:col-span-2 card p-8">
          <div class="flex justify-between items-center mb-8">
            <h3 class="flex items-center gap-3 font-black text-slate-800">
              <div class="p-2 bg-brand-50 text-brand-400 rounded-lg">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
              </div>
              Financial Overview
            </h3>
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sales</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Expenses</span>
              </div>
            </div>
          </div>
          <div class="h-[350px]">
            <canvas id="financialChart"></canvas>
          </div>
        </div>

        {{-- Pie Chart --}}
        <div class="card p-8">
          <h3 class="flex items-center gap-3 font-black text-slate-800 mb-8">
            <div class="p-2 bg-indigo-50 text-indigo-400 rounded-lg">
              <i data-lucide="pie-chart" class="w-5 h-5"></i>
            </div>
            Distribution
          </h3>
          <div class="h-[250px] relative flex justify-center">
            <canvas id="distributionChart"></canvas>
          </div>
          <div class="mt-8 space-y-3">
            <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
              <div class="flex items-center gap-3">
                <span class="h-3 w-3 rounded-md bg-emerald-400"></span>
                <span class="text-xs font-bold text-slate-600">Total Sales</span>
              </div>
              <span class="text-sm font-black text-slate-800"> 0</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
              <div class="flex items-center gap-3">
                <span class="h-3 w-3 rounded-md bg-rose-400"></span>
                <span class="text-xs font-bold text-slate-600">Total Expenses</span>
              </div>
              <span class="text-sm font-black text-slate-800">0</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Tables Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Top Selling Products --}}
        <div class="card p-6">
          <h3 class="flex items-center gap-3 font-black text-slate-800 mb-6 pb-4 border-b border-slate-50">
            <i data-lucide="award" class="text-yellow-500 w-5 h-5"></i>
            Top Selling Products
          </h3>
          <div class="space-y-4">
            @forelse($topProducts as $top)
              <div class="flex items-center gap-4 group p-2 rounded-xl hover:bg-slate-50 transition-colors">
                <div
                  class="h-12 w-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                  @if ($top->image)
                    <img src="{{ Storage::url($top->image) }}" class="h-full w-full object-cover">
                  @else
                    <i data-lucide="box" class="text-slate-300"></i>
                  @endif
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-black text-slate-700 truncate group-hover:text-brand-400 transition-colors">
                    {{ $top->name }}</p>
                  <p class="text-[10px] text-slate-400 font-bold">{{ $top->order_count }} Orders</p>
                </div>
                <div class="text-right">
                  <span class="block text-sm font-black text-emerald-500">{{ number_format($top->total_qty) }}</span>
                  <span class="text-[9px] text-slate-400 uppercase font-bold">Sold</span>
                </div>
              </div>
            @empty
              <div class="text-center py-8 text-slate-400 text-xs italic">No sales in this range.</div>
            @endforelse
          </div>
        </div>

        {{-- Low Stock Products --}}
        <div class="card p-6">
          <h3 class="flex items-center gap-3 font-black text-slate-800 mb-6 pb-4 border-b border-slate-50">
            <i data-lucide="alert-triangle" class="text-rose-500 w-5 h-5"></i>
            Low Stock Alert
          </h3>
          <div class="space-y-4">
            @forelse($lowStockProducts as $variant)
              <div class="flex items-center gap-4 group p-2 rounded-xl hover:bg-rose-50/10 transition-colors">
                <div
                  class="h-12 w-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                  @if ($variant->product->image)
                    <img src="{{ Storage::url($variant->product->image) }}"
                      class="h-full w-full object-cover opacity-70">
                  @else
                    <i data-lucide="box" class="text-slate-300"></i>
                  @endif
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-black text-slate-700 truncate">{{ $variant->product->name }}</p>
                  <p class="text-[10px] text-slate-400 font-bold truncate">{{ $variant->name }}</p>
                </div>
                <div class="text-right">
                  <span class="px-2 py-1 bg-rose-100 text-rose-600 rounded-lg text-xs font-black border border-rose-200">
                    {{ (int) $variant->stock_sum }}
                  </span>
                </div>
              </div>
            @empty
              <div class="text-center py-8 text-slate-400 text-xs italic">
                <i data-lucide="check-circle-2" class="text-emerald-400 mb-3 w-8 h-8 mx-auto block"></i>
                Stock levels healthy!
              </div>
            @endforelse
          </div>
        </div>

        {{-- Recent Sales --}}
        <div class="card p-6 lg:col-span-1">
          <h3 class="flex items-center gap-3 font-black text-slate-800 mb-6 pb-4 border-b border-slate-50">
            <i data-lucide="clock" class="text-blue-500 w-5 h-5"></i>
            Recent Activity
          </h3>
          <div class="space-y-4">
            @forelse($recentSales as $sale)
              <div
                class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors border-b border-dashed border-slate-100 last:border-0">
                <div
                  class="h-10 w-10 bg-brand-50 rounded-lg flex items-center justify-center text-brand-600 font-bold text-xs shrink-0">
                  {{ substr($sale->customer->display_name ?? 'C', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-black text-slate-700 truncate">{{ $sale->customer->display_name ?? 'Guest' }}
                  </p>
                  <p class="text-[10px] text-slate-400">{{ $sale->created_at->format('M d, h:i A') }}</p>
                </div>
                <div class="text-right">
                  <p class="text-xs font-black text-slate-800">${{ number_format($sale->grand_total, 2) }}</p>
                  <span class="text-[9px] font-bold text-emerald-500 uppercase tracking-tight">Success</span>
                </div>
              </div>
            @empty
              <div class="text-center py-8 text-slate-400 text-xs italic">No recent activity.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

  @endif

@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Common Chart Options
      Chart.defaults.font.family = "'Nunito', sans-serif";
      Chart.defaults.color = '#94a3b8';
      Chart.defaults.font.weight = '600';

      // --- Financial Chart (Sales vs Expenses) ---
      const ctxFin = document.getElementById('financialChart').getContext('2d');

      // Gradient for Sales
      const salesGradient = ctxFin.createLinearGradient(0, 0, 0, 350);
      salesGradient.addColorStop(0, 'rgba(52, 211, 153, 0.2)');
      salesGradient.addColorStop(1, 'rgba(52, 211, 153, 0)');

      new Chart(ctxFin, {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
              label: 'Sales',
              data: [],
              borderColor: '#10b981',
              backgroundColor: salesGradient,
              fill: true,
              tension: 0.4,
              borderWidth: 3,
              pointRadius: 0,
              pointHoverRadius: 6,
              pointHoverBackgroundColor: '#10b981',
              pointHoverBorderColor: '#fff',
              pointHoverBorderWidth: 3,
            },
            {
              label: 'Expenses',
              data: [],
              borderColor: '#f43f5e',
              backgroundColor: 'transparent',
              fill: false,
              tension: 0.4,
              borderWidth: 2,
              borderDash: [5, 5],
              pointRadius: 0,
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            intersect: false,
            mode: 'index'
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: '#1e293b',
              padding: 12,
              titleFont: {
                size: 14,
                weight: 'bold'
              },
              bodyFont: {
                size: 13
              },
              cornerRadius: 8,
              displayColors: true
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              },
              border: {
                display: false
              },
            },
            y: {
              grid: {
                color: '#f1f5f9',
                drawTicks: false
              },
              border: {
                display: false
              },
              ticks: {
                padding: 10
              }
            }
          }
        }
      });

      // --- Distribution Chart (Pie/Doughnut) ---
      const ctxDist = document.getElementById('distributionChart').getContext('2d');

      new Chart(ctxDist, {
        type: 'doughnut',
        data: {
          labels: ['Sales', 'Expenses'],
          datasets: [{
            data: [],
            backgroundColor: ['#34d399', '#f43f5e'],
            hoverOffset: 10,
            borderWidth: 0,
            cutout: '75%'
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
              padding: 12,
              cornerRadius: 8
            }
          }
        }
      });

      // --- Date Range Picker (Flatpickr) ---
      flatpickr("#date_range", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: ["{{ $dateFrom->format('Y-m-d') }}", "{{ $dateTo->format('Y-m-d') }}"],
        onChange: function(selectedDates, dateStr, instance) {
          if (selectedDates.length === 2) {
            document.getElementById('date_from').value = instance.formatDate(selectedDates[0], "Y-m-d");
            document.getElementById('date_to').value = instance.formatDate(selectedDates[1], "Y-m-d");
          }
        }
      });
    });
  </script>
@endpush
