@extends('catvara.layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Welcome, {{ auth()->user()->name }}</h1>
        <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
          You have <span class="text-brand-400">200+ Orders</span> Today
        </p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative">
          <input type="text"
            class="bg-white px-10 py-2.5 rounded-xl border border-slate-200 shadow-sm text-xs font-bold text-slate-500 focus:ring-2 focus:ring-brand-400 outline-none w-64"
            value="01/15/2026 - 01/21/2026">
          <i class="far fa-calendar-alt text-brand-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
          <i class="fas fa-chevron-down text-slate-300 absolute right-4 top-1/2 -translate-y-1/2 text-[10px]"></i>
        </div>
      </div>
    </div>

    <!-- Alert Message -->
    <div id="lowStockAlert"
      class="bg-orange-50/80 border border-orange-100 rounded-2xl p-4 flex items-center justify-between shadow-sm animate-fade-in">
      <div class="flex items-center gap-3 text-orange-700 font-bold text-sm">
        <div class="h-8 w-8 bg-orange-100 rounded-lg flex items-center justify-center text-orange-500">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <span>Your Product <span class="underline font-black decoration-2 underline-offset-4">Apple iPhone 15</span> is
          running Low, already below 5 Pcs. <span
            class="text-orange-500 cursor-pointer font-black ml-2 hover:underline">Add Stock</span></span>
      </div>
      <button onclick="document.getElementById('lowStockAlert').style.display='none'"
        class="h-8 w-8 flex items-center justify-center text-orange-300 hover:bg-orange-100 hover:text-orange-600 rounded-lg transition-all"><i
          class="fas fa-times"></i></button>
    </div>

    <!-- Top Row: Colorful Block Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Card 1: Total Sales -->
      <div class="dashboard-stat-card bg-pos-orange">
        <div class="flex justify-between items-start relative z-10">
          <div class="stat-icon-wrapper">
            <i class="fas fa-file-invoice text-xl"></i>
          </div>
          <div class="text-right">
            <h4 class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Total Sales</h4>
            <p class="text-2xl font-black">${{ number_format($stats['total_revenue'], 2) }}</p>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2 relative z-10">
          <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold">+22% vs last week</span>
        </div>
        <div class="absolute -right-4 -bottom-4 h-24 w-24 bg-white/10 rounded-full blur-2xl"></div>
      </div>

      <!-- Card 2: Sales Return -->
      <div class="dashboard-stat-card bg-pos-dark">
        <div class="flex justify-between items-start relative z-10">
          <div class="stat-icon-wrapper">
            <i class="fas fa-undo text-xl"></i>
          </div>
          <div class="text-right">
            <h4 class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Total Sales Return</h4>
            <p class="text-2xl font-black">$16,478,145</p>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2 relative z-10">
          <span class="px-2 py-0.5 bg-white/10 rounded-full text-[10px] font-bold text-slate-300">-22% vs last week</span>
        </div>
        <div class="absolute -right-4 -bottom-4 h-24 w-24 bg-white/5 rounded-full blur-2xl"></div>
      </div>

      <!-- Card 3: Total Purchase -->
      <div class="dashboard-stat-card bg-pos-emerald">
        <div class="flex justify-between items-start relative z-10">
          <div class="stat-icon-wrapper">
            <i class="fas fa-shopping-bag text-xl"></i>
          </div>
          <div class="text-right">
            <h4 class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Total Purchase</h4>
            <p class="text-2xl font-black">$24,145,789</p>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2 relative z-10">
          <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold">+18% vs last week</span>
        </div>
        <div class="absolute -right-4 -bottom-4 h-24 w-24 bg-white/10 rounded-full blur-2xl"></div>
      </div>

      <!-- Card 4: Purchase Return -->
      <div class="dashboard-stat-card bg-pos-blue">
        <div class="flex justify-between items-start relative z-10">
          <div class="stat-icon-wrapper">
            <i class="fas fa-box-open text-xl"></i>
          </div>
          <div class="text-right">
            <h4 class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Total Purchase Return</h4>
            <p class="text-2xl font-black">$18,458,747</p>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2 relative z-10">
          <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold">+12% vs last week</span>
        </div>
        <div class="absolute -right-4 -bottom-4 h-24 w-24 bg-white/10 rounded-full blur-2xl"></div>
      </div>
    </div>

    <!-- Second Row: Secondary White Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      @php
        $secondaryStats = [
            [
                'label' => 'Profit',
                'value' => '$8,458,798',
                'trend' => '+35%',
                'icon' => 'fas fa-chart-line',
                'color' => 'blue',
            ],
            [
                'label' => 'Invoice Due',
                'value' => '$48,988,78',
                'trend' => '+35%',
                'icon' => 'far fa-clock',
                'color' => 'emerald',
            ],
            [
                'label' => 'Total Expenses',
                'value' => '$8,980,097',
                'trend' => '+41%',
                'icon' => 'fas fa-wallet',
                'color' => 'orange',
            ],
            [
                'label' => 'Total Payment Returns',
                'value' => '$78,458,798',
                'trend' => '-20%',
                'icon' => 'fas fa-hashtag',
                'color' => 'indigo',
            ],
        ];
      @endphp
      @foreach ($secondaryStats as $s)
        <div class="card p-6 flex items-center justify-between group">
          <div class="space-y-1">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $s['label'] }}</p>
            <p class="text-xl font-black text-slate-800 group-hover:text-brand-400 transition-colors">{{ $s['value'] }}
            </p>
            <div class="flex items-center gap-2 pt-2">
              <span class="text-emerald-500 text-[10px] font-bold">{{ $s['trend'] }} vs Month</span>
              <a href="#"
                class="text-[10px] font-bold text-slate-300 underline underline-offset-4 hover:text-brand-400">View
                All</a>
            </div>
          </div>
          <div
            class="h-12 w-12 rounded-2xl bg-{{ $s['color'] }}-50 flex items-center justify-center text-{{ $s['color'] }}-400 group-hover:scale-110 transition-transform">
            <i class="{{ $s['icon'] }} text-lg"></i>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Third Row: Sales & Purchase Chart + Overall Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 card p-8">
        <div class="flex justify-between items-center mb-10">
          <h3 class="flex items-center gap-3 font-black text-slate-800 text-lg">
            <i class="fas fa-chart-bar text-brand-400"></i>
            Sales & Purchase
          </h3>
          <div class="flex bg-slate-50 p-1 rounded-xl gap-1">
            @foreach (['1D', '1W', '1M', '3M', '6M', '1Y'] as $period)
              <button
                class="chart-filter-btn px-4 py-1.5 text-[10px] font-black rounded-lg transition-all {{ $period == '1Y' ? 'bg-white shadow-sm text-brand-400 active' : 'text-slate-400 hover:text-slate-600' }}"
                onclick="toggleChartFilter(this)">
                {{ $period }}
              </button>
            @endforeach
          </div>
        </div>
        <div class="flex gap-12 mb-8 border-b border-slate-50 pb-6">
          <div class="flex items-center gap-4">
            <div class="h-3 w-3 rounded-full bg-orange-400 ring-4 ring-orange-50"></div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Purchase</p>
              <p class="text-2xl font-black text-slate-800">3,000</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <div class="h-3 w-3 rounded-full bg-orange-200 ring-4 ring-orange-50"></div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Sales</p>
              <p class="text-2xl font-black text-slate-800">1,248</p>
            </div>
          </div>
        </div>
        <div class="h-[350px]">
          <canvas id="salesPurchaseChart"></canvas>
        </div>
      </div>

      <div class="space-y-8">
        <!-- Overall Information -->
        <div class="card p-8 bg-slate-50/30">
          <h3 class="flex items-center gap-3 font-black text-slate-800 mb-8 border-b border-slate-100 pb-4">
            <i class="fas fa-info-circle text-blue-400"></i>
            Overall Information
          </h3>
          <div class="grid grid-cols-3 gap-4">
            @php
              $infoItems = [
                  ['label' => 'Suppliers', 'val' => '6987', 'icon' => 'fas fa-truck', 'color' => 'blue'],
                  [
                      'label' => 'Customer',
                      'val' => $stats['total_customers'],
                      'icon' => 'fas fa-users',
                      'color' => 'orange',
                  ],
                  ['label' => 'Orders', 'val' => $stats['total_orders'], 'icon' => 'fas fa-box', 'color' => 'emerald'],
              ];
            @endphp
            @foreach ($infoItems as $item)
              <div class="card p-4 text-center space-y-3 transition-all hover:scale-105">
                <div
                  class="h-10 w-10 mx-auto rounded-xl bg-{{ $item['color'] }}-50 flex items-center justify-center text-{{ $item['color'] }}-400 shadow-sm border border-{{ $item['color'] }}-100/50">
                  <i class="{{ $item['icon'] }} text-xs"></i>
                </div>
                <div>
                  <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $item['label'] }}</p>
                  <p class="text-sm font-black text-slate-800">{{ number_format($item['val']) }}</p>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Customer Overview -->
        <div class="card p-8">
          <div class="flex justify-between items-center mb-10">
            <h3 class="font-black text-slate-800">Customers Overview</h3>
            <select
              class="p-0 border-none text-[10px] font-black text-slate-400 uppercase tracking-widest focus:ring-0 cursor-pointer bg-transparent">
              <option>Today</option>
            </select>
          </div>
          <div class="flex items-center gap-10">
            <div class="h-36 w-36 relative flex items-center justify-center">
              <canvas id="customerDonutChart"></canvas>
            </div>
            <div class="flex-1 space-y-8">
              <div class="flex items-center justify-between border-l-4 border-emerald-400 pl-4">
                <div>
                  <p class="text-2xl font-black text-slate-800">5,548</p>
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">First Time</p>
                </div>
                <span
                  class="px-2 py-1 bg-emerald-50 text-emerald-500 rounded-lg text-[10px] font-black border border-emerald-100">+25%</span>
              </div>
              <div class="flex items-center justify-between border-l-4 border-orange-400 pl-4">
                <div>
                  <p class="text-2xl font-black text-slate-800">3,489</p>
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Returning</p>
                </div>
                <span
                  class="px-2 py-1 bg-orange-50 text-orange-500 rounded-lg text-[10px] font-black border border-orange-100">+21%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fourth Row: Multi-Grid Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Top Selling Products -->
      <div class="card p-8">
        <div class="flex justify-between items-center mb-8 border-b border-slate-50 pb-6">
          <h3 class="flex items-center gap-3 font-black text-slate-800">
            <i class="fas fa-star text-yellow-400"></i>
            Top Selling Products
          </h3>
          <select
            class="p-0 border-none text-[10px] font-black text-slate-400 uppercase tracking-widest focus:ring-0 bg-transparent">
            <option>Today</option>
          </select>
        </div>
        <div class="space-y-4">
          @forelse($charts['topProducts']['labels'] as $index => $name)
            <div class="list-item-hover group">
              <div class="flex items-center gap-4">
                <div
                  class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-100 group-hover:bg-brand-50 group-hover:text-brand-400 transition-all">
                  <i class="fas fa-box text-sm"></i>
                </div>
                <div>
                  <p class="text-sm font-black text-slate-700 group-hover:text-slate-900 transition-colors">
                    {{ $name }}</p>
                  <p class="text-[10px] text-slate-400 font-bold mt-1">Total Sales: <span
                      class="text-slate-600 font-black">{{ number_format($charts['topProducts']['data'][$index]) }}</span>
                  </p>
                </div>
              </div>
              <div
                class="px-2 py-1 bg-emerald-50 text-emerald-500 rounded-lg text-[9px] font-black border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                HOT</div>
            </div>
          @empty
            <div class="p-12 text-center text-slate-300 text-xs font-bold bg-slate-50 rounded-2xl">No sales data yet
            </div>
          @endforelse
        </div>
      </div>

      <!-- Low Stock Products -->
      <div class="card p-8">
        <div class="flex justify-between items-center mb-8 border-b border-slate-50 pb-6">
          <h3 class="flex items-center gap-3 font-black text-slate-800">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            Low Stock Products
          </h3>
          <a href="#"
            class="text-[10px] font-black text-brand-400 uppercase tracking-widest hover:underline decoration-2 underline-offset-4">View
            All</a>
        </div>
        <div class="space-y-4">
          @php
            $lowStock = [
                ['name' => 'Dell XPS 13', 'id' => '#665814', 'stock' => '08'],
                ['name' => 'Vacuum Cleaner Robot', 'id' => '#940004', 'stock' => '14'],
                ['name' => 'KitchenAid Stand Mixer', 'id' => '#325569', 'stock' => '21'],
                ['name' => 'Levi\'s Trucker Jacket', 'id' => '#124588', 'stock' => '12'],
            ];
          @endphp
          @foreach ($lowStock as $ls)
            <div class="list-item-hover group">
              <div class="flex items-center gap-4">
                <div
                  class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center border border-slate-100 overflow-hidden">
                  <img
                    src="https://ui-avatars.com/api/?name={{ urlencode($ls['name']) }}&background=f1f5f9&color=cbd5e1"
                    class="h-full w-full object-cover opacity-50 transition-opacity group-hover:opacity-100"
                    alt="">
                </div>
                <div>
                  <p class="text-sm font-black text-slate-700">{{ $ls['name'] }}</p>
                  <p class="text-[10px] text-slate-400 font-bold mt-1">ID: <span
                      class="text-slate-500">{{ $ls['id'] }}</span></p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-[9px] text-slate-400 uppercase font-black mb-1">Stock</p>
                <p class="text-sm font-black text-red-500 bg-red-50 px-2 py-0.5 rounded-lg border border-red-100">
                  {{ $ls['stock'] }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Recent Sales -->
      <div class="card p-8">
        <div class="flex justify-between items-center mb-8 border-b border-slate-50 pb-6">
          <h3 class="flex items-center gap-3 font-black text-slate-800">
            <i class="fas fa-receipt text-indigo-400"></i>
            Recent Sales
          </h3>
          <select
            class="p-0 border-none text-[10px] font-black text-slate-400 uppercase tracking-widest focus:ring-0 bg-transparent">
            <option>Weekly</option>
          </select>
        </div>
        <div class="space-y-4">
          @php
            $recentSales = [
                [
                    'name' => 'Apple Watch Series 9',
                    'desc' => 'Electronics • $640',
                    'status' => 'Processing',
                    'color' => 'indigo',
                    'date' => 'Today',
                ],
                [
                    'name' => 'Gold Bracelet',
                    'desc' => 'Fashion • $126',
                    'status' => 'Cancelled',
                    'color' => 'red',
                    'date' => 'Today',
                ],
                [
                    'name' => 'Parachute Down Duvet',
                    'desc' => 'Health • $69',
                    'status' => 'Onfold',
                    'color' => 'blue',
                    'date' => '15 Jan 2026',
                ],
                [
                    'name' => 'YETI Rambler Tumbler',
                    'desc' => 'Sports • $55',
                    'status' => 'Processing',
                    'color' => 'indigo',
                    'date' => '12 Jan 2026',
                ],
            ];
          @endphp
          @foreach ($recentSales as $rs)
            <div class="list-item-hover group">
              <div class="flex items-center gap-4">
                <div
                  class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100 group-hover:border-indigo-100 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-all">
                  <i class="fas fa-shopping-bag text-xs"></i>
                </div>
                <div>
                  <p class="text-sm font-black text-slate-700 transition-colors group-hover:text-slate-900">
                    {{ $rs['name'] }}</p>
                  <p class="text-[10px] text-slate-400 font-bold mt-1">{{ $rs['desc'] }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-[10px] text-slate-400 mb-2 font-bold">{{ $rs['date'] }}</p>
                <span
                  class="px-2 py-1 bg-{{ $rs['color'] }}-50 text-{{ $rs['color'] }}-500 rounded-lg text-[8px] font-black border border-{{ $rs['color'] }}-100 group-hover:bg-{{ $rs['color'] }}-500 group-hover:text-white transition-all uppercase tracking-tighter">{{ $rs['status'] }}</span>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    <!-- Bottom Sections: Sales Statics & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
      <!-- Sales Statics -->
      <div class="card p-8 bg-slate-50/20">
        <div class="flex justify-between items-center mb-10">
          <h3 class="flex items-center gap-3 font-black text-slate-800 text-lg">
            <i class="fas fa-chart-line text-emerald-500"></i>
            Sales Statics
          </h3>
          <select
            class="p-0 border-none text-[10px] font-black text-slate-400 uppercase tracking-widest focus:ring-0 bg-transparent">
            <option>2026</option>
          </select>
        </div>
        <div class="flex gap-10 mb-8 border-b border-slate-100 pb-6">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Revenue</p>
              <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-500 rounded text-[9px] font-black">+25%</span>
            </div>
            <p class="text-2xl font-black text-slate-800">$12,189.00</p>
          </div>
          <div class="border-l border-slate-100 pl-10">
            <div class="flex items-center gap-2 mb-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Expense</p>
              <span class="px-1.5 py-0.5 bg-red-50 text-red-500 rounded text-[9px] font-black">-15%</span>
            </div>
            <p class="text-2xl font-black text-slate-800">$48,988.78</p>
          </div>
        </div>
        <div class="h-[280px]">
          <canvas id="revenueExpenseChart"></canvas>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="card border-slate-200 flex flex-col">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
          <h3 class="flex items-center gap-3 font-black text-slate-800">
            <i class="fas fa-exchange-alt text-brand-400"></i>
            Recent Transactions
          </h3>
          <a href="#"
            class="text-[10px] font-black text-brand-400 uppercase tracking-widest hover:underline decoration-2 underline-offset-4">View
            All</a>
        </div>
        <div class="flex border-b border-slate-50 bg-white">
          <div class="pos-tab active">Sale</div>
          <div class="pos-tab">Purchase</div>
          <div class="pos-tab">Quotation</div>
          <div class="pos-tab">Expenses</div>
          <div class="pos-tab">Invoices</div>
        </div>
        <div class="p-0 flex-1 overflow-x-auto">
          <table class="w-full text-left text-xs font-bold text-slate-500 border-separate border-spacing-0">
            <thead class="bg-slate-50/50">
              <tr>
                <th class="px-8 py-4 uppercase tracking-widest text-[10px] text-slate-400">Date</th>
                <th class="px-8 py-4 uppercase tracking-widest text-[10px] text-slate-400">Customer</th>
                <th class="px-8 py-4 uppercase tracking-widest text-[10px] text-slate-400">Status</th>
                <th class="px-8 py-4 uppercase tracking-widest text-[10px] text-slate-400 text-right">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
              @php
                $txs = [
                    [
                        'date' => '24 May 2026',
                        'cust' => 'Andrea Willer',
                        'id' => '#114589',
                        'status' => 'Completed',
                        'total' => '$4,560',
                        'color' => 'emerald',
                    ],
                    [
                        'date' => '23 May 2026',
                        'cust' => 'Timothy Sands',
                        'id' => '#114589',
                        'status' => 'Completed',
                        'total' => '$3,569',
                        'color' => 'emerald',
                    ],
                    [
                        'date' => '22 May 2026',
                        'cust' => 'Bonnie Rodrigues',
                        'id' => '#114589',
                        'status' => 'Draft',
                        'total' => '$4,560',
                        'color' => 'red',
                    ],
                    [
                        'date' => '21 May 2026',
                        'cust' => 'Randy McCree',
                        'id' => '#114589',
                        'status' => 'Completed',
                        'total' => '$2,155',
                        'color' => 'emerald',
                    ],
                    [
                        'date' => '21 May 2026',
                        'cust' => 'Dennis Anderson',
                        'id' => '#114589',
                        'status' => 'Completed',
                        'total' => '$5,123',
                        'color' => 'emerald',
                    ],
                ];
              @endphp
              @foreach ($txs as $tx)
                <tr class="hover:bg-slate-50 transition-all cursor-pointer group">
                  <td class="px-8 py-5 text-slate-400 font-bold">{{ $tx['date'] }}</td>
                  <td class="px-8 py-5">
                    <div class="flex items-center gap-4">
                      <div
                        class="h-10 w-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-400 font-black border border-brand-100/50 group-hover:scale-110 transition-transform">
                        {{ substr($tx['cust'], 0, 1) }}
                      </div>
                      <div>
                        <p class="text-slate-800 transition-colors group-hover:text-brand-400">{{ $tx['cust'] }}</p>
                        <p class="text-[9px] text-slate-400 font-medium">ID: {{ $tx['id'] }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-8 py-5">
                    <span
                      class="px-3 py-1 bg-{{ $tx['color'] }}-50 text-{{ $tx['color'] }}-500 rounded-lg text-[9px] font-black border border-{{ $tx['color'] }}-100 group-hover:bg-{{ $tx['color'] }}-500 group-hover:text-white transition-all">{{ $tx['status'] }}</span>
                  </td>
                  <td class="px-8 py-5 text-right text-slate-800 font-black text-sm">{{ $tx['total'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Fifth Row: Footer Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
      <!-- Top Customers -->
      <div class="card p-8">
        <div class="flex justify-between items-center mb-10 border-b border-slate-50 pb-6">
          <h3 class="flex items-center gap-3 font-black text-slate-800">
            <i class="fas fa-crown text-yellow-500"></i>
            Top Customers
          </h3>
          <a href="#"
            class="text-[10px] font-black text-brand-400 uppercase tracking-widest hover:underline decoration-2 underline-offset-4">View
            All</a>
        </div>
        <div class="space-y-6">
          @php
            $topCust = [
                ['name' => 'Carlos Curran', 'loc' => 'USA • 24 Orders', 'val' => '$8,964.5'],
                ['name' => 'Stan Gaunter', 'loc' => 'UAE • 22 Orders', 'val' => '$16,985'],
                ['name' => 'Richard Wilson', 'loc' => 'Germany • 14 Orders', 'val' => '$5,366'],
                ['name' => 'Mary Bronson', 'loc' => 'Belgium • 08 Orders', 'val' => '$4,569'],
            ];
          @endphp
          @foreach ($topCust as $tc)
            <div
              class="flex items-center justify-between group cursor-pointer hover:translate-x-2 transition-all p-3 rounded-2xl hover:bg-slate-50">
              <div class="flex items-center gap-4">
                <div
                  class="h-12 w-12 rounded-2xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-slate-400 font-black group-hover:bg-brand-50 group-hover:text-brand-400 group-hover:border-brand-100 transition-all">
                  {{ substr($tc['name'], 0, 1) }}
                </div>
                <div>
                  <p class="text-sm font-black text-slate-700 group-hover:text-brand-400 transition-colors">
                    {{ $tc['name'] }}</p>
                  <p class="text-[10px] text-slate-400 font-bold mt-1"><i
                      class="fas fa-map-marker-alt text-[8px] mr-1"></i> {{ $tc['loc'] }}</p>
                </div>
              </div>
              <p
                class="text-sm font-black text-slate-800 bg-white px-3 py-1 rounded-lg border border-slate-100 group-hover:bg-brand-400 group-hover:text-white group-hover:border-brand-400 transition-all">
                {{ $tc['val'] }}</p>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Top Categories -->
      <div class="card p-8 bg-slate-50/20">
        <div class="flex justify-between items-center mb-10">
          <h3 class="flex items-center gap-3 font-black text-slate-800">
            <i class="fas fa-layer-group text-blue-500"></i>
            Top Categories
          </h3>
          <select
            class="p-0 border-none text-[10px] font-black text-slate-400 uppercase tracking-widest focus:ring-0 bg-transparent">
            <option>Weekly</option>
          </select>
        </div>
        <div class="flex flex-col items-center">
          <div class="h-48 w-48 mb-10">
            <canvas id="categoryDonutChart"></canvas>
          </div>
          <div class="w-full space-y-4">
            @php
              $catsArr = [
                  ['label' => 'Electronics', 'val' => '698', 'color' => '#1e293b'],
                  ['label' => 'Sports', 'val' => '545', 'color' => '#ff9f43'],
                  ['label' => 'Lifestyles', 'val' => '456', 'color' => '#cbd5e1'],
              ];
            @endphp
            @foreach ($catsArr as $c)
              <div
                class="card p-4 flex items-center justify-between border-slate-100 transition-all hover:translate-x-1">
                <div class="flex items-center gap-3 text-xs font-black text-slate-600">
                  <span class="h-3 w-3 rounded-full shadow-sm" style="background-color: {{ $c['color'] }}"></span>
                  {{ $c['label'] }}
                </div>
                <p class="text-xs font-black text-slate-800">{{ $c['val'] }} <span
                    class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Sales</span></p>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Order Statistics Heatmap -->
      <div class="card p-8">
        <div class="flex justify-between items-center mb-10 border-b border-slate-50 pb-6">
          <h3 class="flex items-center gap-3 font-black text-slate-800">
            <i class="fas fa-th text-orange-500"></i>
            Order Statistics
          </h3>
          <select
            class="p-0 border-none text-[10px] font-black text-slate-400 uppercase tracking-widest focus:ring-0 bg-transparent">
            <option>Weekly</option>
          </select>
        </div>
        <div class="space-y-8">
          <div class="flex justify-between text-[10px] font-black text-slate-300 uppercase tracking-widest px-4">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
          </div>
          <div class="heatmap-grid px-2">
            @for ($i = 0; $i < 70; $i++)
              <div class="heatmap-cell heatmap-cell-{{ rand(0, 4) }} hover:scale-125 hover:shadow-lg transition-all"
                title="Date: {{ $i }}"></div>
            @endfor
          </div>
          <div class="flex justify-between items-center px-4 pt-6 border-t border-slate-50 mt-4">
            <div class="flex items-center gap-2">
              <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest mr-2">Less Performance</span>
              <div class="h-3 w-3 heatmap-cell-0 rounded-sm"></div>
              <div class="h-3 w-3 heatmap-cell-1 rounded-sm"></div>
              <div class="h-3 w-3 heatmap-cell-2 rounded-sm"></div>
              <div class="h-3 w-3 heatmap-cell-3 rounded-sm"></div>
              <div class="heatmap-cell-4 h-3 w-3 rounded-sm ring-2 ring-brand-100"></div>
              <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest ml-2">High Peak</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    function toggleChartFilter(btn) {
      document.querySelectorAll('.chart-filter-btn').forEach(b => {
        b.classList.remove('bg-white', 'shadow-sm', 'text-brand-400', 'active');
        b.classList.add('text-slate-400', 'hover:text-slate-600');
      });
      btn.classList.add('bg-white', 'shadow-sm', 'text-brand-400', 'active');
      btn.classList.remove('text-slate-400', 'hover:text-slate-600');
      // Logic to update chart data would go here
    }

    document.addEventListener('DOMContentLoaded', function() {
      Chart.defaults.font.family = "'Nunito', sans-serif";
      Chart.defaults.color = '#64748b';
      Chart.defaults.font.weight = '700';

      // --- Sales & Purchase Chart (Stacked Bars) ---
      const ctxSales = document.getElementById('salesPurchaseChart').getContext('2d');
      new Chart(ctxSales, {
        type: 'bar',
        data: {
          labels: ['2 am', '4 am', '6 am', '8 am', '10 am', '12 am', '14 pm', '16 pm', '18 pm', '20 pm', '22 pm',
            '24 pm'
          ],
          datasets: [{
              label: 'Purchase',
              data: [20, 25, 15, 20, 30, 20, 25, 30, 45, 10, 25, 20],
              backgroundColor: '#ff9f43',
              borderRadius: 6,
              barThickness: 20,
            },
            {
              label: 'Sales',
              data: [40, 35, 35, 45, 55, 40, 35, 45, 50, 20, 35, 30],
              backgroundColor: 'rgba(255, 159, 67, 0.15)',
              borderRadius: 6,
              barThickness: 20,
            }
          ]
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
              titleFont: {
                size: 12,
                weight: '900'
              },
              bodyFont: {
                size: 11
              },
              cornerRadius: 8,
              displayColors: false
            }
          },
          scales: {
            x: {
              stacked: true,
              grid: {
                display: false
              },
              border: {
                display: false
              }
            },
            y: {
              stacked: true,
              grid: {
                color: '#f8fafc',
                drawTicks: false
              },
              border: {
                dash: [5, 5],
                display: false
              },
              ticks: {
                stepSize: 20
              }
            }
          }
        }
      });

      // --- Revenue vs Expense Chart ---
      const ctxRevExp = document.getElementById('revenueExpenseChart').getContext('2d');
      new Chart(ctxRevExp, {
        type: 'bar',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          datasets: [{
              label: 'Revenue',
              data: [15, 25, 20, 25, 18, 22, 28, 20, 15, 25, 20, 22],
              backgroundColor: '#28c76f',
              borderRadius: 4,
            },
            {
              label: 'Expense',
              data: [-12, -18, -15, -20, -12, -16, -24, -15, -10, -18, -12, -15],
              backgroundColor: '#ff9f43',
              borderRadius: 4,
            }
          ]
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
              },
              border: {
                display: false
              }
            },
            y: {
              grid: {
                color: '#f8fafc'
              },
              border: {
                dash: [5, 5],
                display: false
              },
              min: -30,
              max: 30
            }
          }
        }
      });

      // --- Category Donut Chart ---
      const ctxCat = document.getElementById('categoryDonutChart').getContext('2d');
      new Chart(ctxCat, {
        type: 'doughnut',
        data: {
          labels: ['Electronics', 'Sports', 'Lifestyles'],
          datasets: [{
            data: [40, 35, 25],
            backgroundColor: ['#1e293b', '#ff9f43', '#cbd5e1'],
            borderWidth: 0,
            hoverOffset: 15
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
          cutout: '75%'
        }
      });

      // --- Customer Overview Donut ---
      const ctxCust = document.getElementById('customerDonutChart').getContext('2d');
      new Chart(ctxCust, {
        type: 'doughnut',
        data: {
          datasets: [{
              data: [75, 25],
              backgroundColor: ['#00cfe8', '#f1f5f9'],
              borderWidth: 0,
              cutout: '80%',
            },
            {
              data: [60, 40],
              backgroundColor: ['#ff9f43', '#f1f5f9'],
              borderWidth: 0,
              cutout: '65%',
              radius: '85%'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
    });
  </script>
@endpush
