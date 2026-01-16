<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'CATVARA') }}</title>

  <!-- Google Fonts: Nunito -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  <!-- Tailwind CSS (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Nunito', 'sans-serif'],
          },
          colors: {
            brand: {
              50: '#fff3e6',
              100: '#ffe6cc',
              200: '#ffcc99',
              300: '#ffb366',
              400: '#ff9f43', // Primary Orange
              500: '#e68a33',
              600: '#cc7a29',
              700: '#b36b24',
              800: '#995c1f',
              900: '#804d1a',
            },
            slate: {
              850: '#1e293b',
              950: '#0f172a',
            }
          },
          boxShadow: {
            'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02)',
            'card': '0 8px 30px rgba(0, 0, 0, 0.04)',
            'glow': '0 0 15px rgba(255, 159, 67, 0.15)',
          },
          borderRadius: {
            'xl': '12px',
            '2xl': '16px',
            '3xl': '24px',
          }
        }
      }
    }
  </script>

  <!-- Icons & Plugins -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
  <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
    rel="stylesheet" />

  <!-- Custom Styles -->
  <style type="text/tailwindcss">
    body {
      background-color: #f7f8f9;
      color: #334155;
      font-size: 0.9375rem;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 5px;
      height: 5px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    /* Select2 Modernization */
    .select2-container .select2-selection--single {
      height: 42px !important;
      border-color: #e2e8f0 !important;
      border-radius: 10px !important;
      font-size: 0.875rem !important;
      display: flex !important;
      align-items: center !important;
      box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 38px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 40px !important;
      padding-left: 12px !important;
    }

    .select2-dropdown {
      border: none !important;
      border-radius: 12px !important;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1) !important;
      padding: 5px;
      overflow: hidden;
    }

    .select2-search__field {
      border-radius: 0.375rem !important;
      border: 1px solid #e2e8f0 !important;
    }

    .select2-results__option--highlighted {
      background-color: #ffe6cc !important;
      /* Brand 100 */
      color: #b36b24 !important;
      /* Brand 700 */
    }

    /* DataTables Premium Look */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 0.5rem 0.75rem;
      font-size: 0.8125rem;
      color: #64748b;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
      outline: none;
      border-color: #ff9f43;
      box-shadow: 0 0 0 2px rgba(255, 159, 67, 0.1);
    }

    table.dataTable.no-footer {
      border-bottom: none !important;
    }

    table.dataTable thead th {
      background-color: #f8fafc;
      border-bottom: 1px solid #f1f5f9 !important;
      color: #64748b !important;
      font-weight: 600 !important;
      font-size: 0.75rem;
      letter-spacing: 0.025em;
      padding: 1rem !important;
      text-align: left;
    }

    table.dataTable tbody td {
      border-bottom: 1px solid #f1f5f9;
      padding: 0.875rem 1rem !important;
      font-size: 0.875rem;
      color: #334155;
    }

    /* Professional Zebra Striping */
    table tbody tr:nth-child(even) {
      background-color: rgba(248, 250, 252, 0.8) !important;
      /* Slate 50 with alpha */
    }

    table.dataTable tbody tr:hover {
      background-color: #fff9f4 !important;
    }

    /* Global Input Contrast */
    input[type="text"],
    input[type="number"],
    input[type="email"],
    input[type="password"],
    textarea,
    select {
      @apply border border-slate-300 focus:border-brand-400 focus:ring focus:ring-brand-400/10 rounded-xl text-sm transition-all bg-white px-3;
    }

    /* Premium UI Touches */
    .btn {
      @apply inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-bold transition-all duration-300 cursor-pointer text-sm;
    }

    /* Input with Icon patterns */
    .input-icon-group {
      @apply relative flex items-center;
    }

    .input-icon-group i {
      @apply absolute left-4 text-slate-400 z-10 pointer-events-none transition-colors;
    }

    .input-icon-group input {
      @apply pl-11 !important;
    }

    .input-icon-group:focus-within i {
      @apply text-brand-400;
    }

    .btn-sm {
      @apply px-4 py-2 text-xs;
    }

    .btn-primary {
      @apply bg-brand-400 text-white hover:bg-brand-500 shadow-lg shadow-brand-400/20 active:scale-95 border-b-2 border-brand-600/20;
    }

    .btn-white {
      @apply bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 shadow-sm active:scale-95;
    }

    /* Cards */
    .card {
      background-color: #ffffff !important;
      border-radius: 1rem !important;
      box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05), 0 2px 10px -5px rgba(0, 0, 0, 0.05) !important;
      border: 1px solid #f1f5f9 !important;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card:hover {
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.04), 0 10px 10px -5px rgba(0, 0, 0, 0.02) !important;
      transform: translateY(-2px);
    }

    /* Sidebar Links */
    .nav-link.active {
      @apply bg-brand-50 text-brand-400 relative overflow-hidden;
    }

    .nav-link.active::after {
      content: '';
      @apply absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-brand-400 rounded-l-full;
    }

    /* Table Improvements */
    .table-premium {
      @apply w-full border-collapse;
    }

    .table-premium thead th {
      @apply px-6 py-4 bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100;
    }

    .table-premium tbody td {
      @apply px-6 py-5 border-b border-slate-50 text-sm font-medium text-slate-600;
    }

    /* Animations */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fade-in {
      animation: fadeIn 0.4s ease-out forwards;
    }

    /* Badges */
    .badge {
      @apply inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border;
    }

    .badge-success {
      @apply bg-emerald-50 text-emerald-500 border-emerald-100;
    }

    .badge-warning {
      @apply bg-orange-50 text-orange-500 border-orange-100;
    }

    .badge-danger {
      @apply bg-red-50 text-red-500 border-red-100;
    }

    .badge-info {
      @apply bg-blue-50 text-blue-500 border-blue-100;
    }

    .badge-secondary {
      @apply bg-slate-50 text-slate-500 border-slate-100;
    }

    /* Dashboard High-Fidelity Styles */
    .dashboard-stat-card {
      position: relative;
      overflow: hidden;
      border-radius: 1rem;
      padding: 1.5rem;
      color: white;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .dashboard-stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .stat-icon-wrapper {
      height: 3rem;
      width: 3rem;
      border-radius: 0.75rem;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(8px);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      margin-bottom: 1rem;
    }

    .list-item-hover {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.875rem;
      border-radius: 0.75rem;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .list-item-hover:hover {
      background-color: #f8fafc;
    }

    .trend-badge {
      @apply px-2 py-0.5 rounded-full text-[10px] font-bold border;
    }

    .trend-up {
      @apply bg-emerald-50 text-emerald-500 border-emerald-100;
    }

    .trend-down {
      @apply bg-red-50 text-red-500 border-red-100;
    }

    /* Custom Colors from reference */
    .bg-pos-orange {
      background: linear-gradient(135deg, #ff9f43 0%, #ffb366 100%);
    }

    .bg-pos-dark {
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }

    .bg-pos-emerald {
      background: linear-gradient(135deg, #28c76f 0%, #48d689 100%);
    }

    .bg-pos-blue {
      background: linear-gradient(135deg, #00cfe8 0%, #33dcf0 100%);
    }

    .bg-pos-indigo {
      background: linear-gradient(135deg, #7367f0 0%, #8e85f3 100%);
    }

    /* Tabs Styling */
    .pos-tab {
      @apply px-5 py-2 text-xs font-black text-slate-400 border-b-2 border-transparent transition-all cursor-pointer hover:text-slate-600;
    }

    .pos-tab.active {
      @apply text-brand-400 border-brand-400;
    }

    /* Heatmap / Order Stats Grid */
    .heatmap-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 4px;
    }

    .heatmap-cell {
      @apply h-8 rounded-sm transition-all duration-300;
    }

    .heatmap-cell-0 {
      @apply bg-orange-50/30;
    }

    .heatmap-cell-1 {
      @apply bg-orange-100/50;
    }

    .heatmap-cell-2 {
      @apply bg-orange-200;
    }

    .heatmap-cell-3 {
      @apply bg-orange-300;
    }

    .heatmap-cell-4 {
      @apply bg-brand-400;
    }

    /* Glass Effect */
    .glass-sidebar {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(10px);
    }

    /* --- Sidebar Collapse Logic --- */
    :root {
      --sidebar-width: 260px;
      /* w-64 */
      --sidebar-collapsed-width: 80px;
      /* w-20 */
      --header-height: 4rem;
    }

    /* Smooth Transition for Layout Elements */
    #mainSidebar,
    .content-wrapper,
    .sidebar-text,
    .logo-text {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Collapsed State */
    body.sidebar-collapsed #mainSidebar {
      width: var(--sidebar-collapsed-width);
    }

    /* Hide Text when Collapsed */
    body.sidebar-collapsed .sidebar-text,
    body.sidebar-collapsed .logo-text,
    body.sidebar-collapsed .arrow,
    body.sidebar-collapsed .nav-group-header {
      opacity: 0;
      width: 0;
      display: none;
    }

    /* Center Icons when Collapsed */
    body.sidebar-collapsed #mainSidebar .nav-link,
    body.sidebar-collapsed #mainSidebar .nav-group button {
      justify-content: center;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    body.sidebar-collapsed #mainSidebar .nav-link i,
    body.sidebar-collapsed #mainSidebar .nav-group button i {
      margin-right: 0 !important;
    }

    /* Hover Expansion (The Magic) */
    body.sidebar-collapsed #mainSidebar:hover {
      width: var(--sidebar-width);
      position: absolute;
      /* Overlay content */
      height: 100vh;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      z-index: 100;
    }

    /* Restore Text on Hover */
    body.sidebar-collapsed #mainSidebar:hover .sidebar-text,
    body.sidebar-collapsed #mainSidebar:hover .logo-text {
      opacity: 1;
      width: auto;
      display: inline-block;
      white-space: nowrap;
    }

    body.sidebar-collapsed #mainSidebar:hover .nav-group-header {
      opacity: 1;
      width: auto;
      display: block;
    }

    body.sidebar-collapsed #mainSidebar:hover .arrow {
      opacity: 1;
      width: auto;
      display: inline-block;
      margin-left: auto;
      /* Push to right */
    }

    /* Adjust Icons on Hover back to left */
    body.sidebar-collapsed #mainSidebar:hover .nav-link,
    body.sidebar-collapsed #mainSidebar:hover .nav-group button {
      justify-content: flex-start;
      /* Default for links */
      padding-left: 0.75rem !important;
      padding-right: 0.75rem !important;
    }

    /* Ensure Nav Groups are Between (Text ... Arrow) */
    body.sidebar-collapsed #mainSidebar:hover .nav-group button {
      justify-content: space-between;
    }

    /* Child Div of Nav Group (The Submenu) */
    body.sidebar-collapsed #mainSidebar:hover .nav-group>div {
      /* Ensure submenu takes full width */
      width: 100%;
    }

    body.sidebar-collapsed #mainSidebar:hover .nav-link i:first-child,
    body.sidebar-collapsed #mainSidebar:hover .nav-group button i:first-child {
      margin-right: 0.75rem !important;
      width: 1.5rem;
      /* Fixed width for icon consistency */
      text-align: center;
    }

    /* Sidebar Responsive Sidebar Overlay */
    @media (max-width: 1024px) {
      .sidebar-open #mainSidebar {
        transform: translateX(0);
      }

      .sidebar-overlay {
        @apply fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity;
      }
    }

    /* Filter Cards - Collapsible */
    .filter-card-content {
      transition: all 0.3s ease-in-out;
      overflow: hidden;
      max-height: 1000px;
    }

    .filter-card-content.collapsed {
      max-height: 0 !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      margin-top: 0 !important;
      opacity: 0;
    }

    .filter-toggle-icon {
      transition: transform 0.3s ease;
    }

    .filter-toggle-icon.rotated {
      transform: rotate(180deg);
    }

    /* Standard Button Row for Filters */
    .filter-actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid #f1f5f9;
      width: 100%;
    }
  </style>
  @stack('styles')
</head>

<body
  class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-brand-100 selection:text-brand-900 {{ request()->cookie('sidebar_collapsed') ? 'sidebar-collapsed' : '' }}">
  <script>
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
      document.body.classList.add('sidebar-collapsed');
    }
  </script>

  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    @include('catvara.layouts.sidebar')

    <!-- Content Area -->
    <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden bg-slate-50 content-wrapper">

      <!-- Navbar -->
      <header
        class="flex items-center justify-between px-8 py-4 bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-100">
        <div class="flex items-center gap-4">
          <!-- Mobile Toggle -->
          <button class="text-slate-400 hover:text-slate-600 focus:outline-none lg:hidden" @click="sidebarOpen = true">
            <i class="fas fa-bars fa-lg"></i>
          </button>

          <!-- Desktop Collapse Toggle -->
          <button id="sidebarToggle"
            class="hidden lg:flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors focus:outline-none">
            <i class="fas fa-outdent transform transition-transform" id="toggleIcon"></i>
            <!-- Icon rotates in JS -->
          </button>

          <h1 class="text-xl font-semibold text-slate-800 tracking-tight ml-2">
            @yield('title', 'Dashboard')
          </h1>
        </div>

        <div class="flex items-center gap-6">
          <!-- Quick Actions -->
          <button class="text-slate-400 hover:text-brand-600 transition-colors relative">
            <i class="fas fa-bell"></i>
            <span
              class="absolute top-0 right-0 block h-2 w-2 rounded-full ring-2 ring-white bg-red-400 transform translate-x-1/2 -translate-y-1/2"></span>
          </button>

          <!-- Company Selector -->
          @if (active_company())
            <div
              class="hidden md:flex items-center px-3 py-1.5 bg-brand-50 text-brand-700 rounded-full text-xs font-semibold border border-brand-100">
              <i class="fas fa-building mr-2"></i>
              {{ active_company()->name }}
            </div>
          @endif

          <div class="h-6 w-px bg-slate-200 mx-2"></div>

          <!-- User Dropdown (Same as before) -->
          <div class="relative group">
            <button class="flex items-center gap-3 focus:outline-none">
              <span
                class="hidden md:block text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">
                {{ auth()->user()->name }}
              </span>
              <div
                class="h-9 w-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-sm border-2 border-white ring-1 ring-slate-100 shadow-sm">
                {{ substr(auth()->user()->name, 0, 1) }}
              </div>
            </button>

            <div
              class="absolute right-0 w-56 mt-2 bg-white rounded-lg shadow-xl border border-slate-100 py-1 z-50 hidden group-hover:block hover:block transform transition-all duration-200 origin-top-right">
              <div class="px-4 py-3 border-b border-slate-50 bg-slate-50/50">
                <p class="text-xs text-slate-400 uppercase font-semibold">Signed in as</p>
                <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->email }}</p>
              </div>

              <div class="py-1">
                <a href="#"
                  class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-brand-600">Your
                  Profile</a>
                <a href="#"
                  class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-brand-600">Company
                  Settings</a>
              </div>

              <div class="py-1 border-t border-slate-50">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    Sign out
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="w-full flex-1 p-8">
        <!-- Alerts (Same) -->
        @if (session('success'))
          <div
            class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700 flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
          </div>
        @endif
        @if (session('error'))
          <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-100 text-red-700 flex items-center shadow-sm">
            <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
            <span class="text-sm font-medium">{{ session('error') }}</span>
          </div>
        @endif

        @yield('content')
      </main>

      <!-- Footer -->
      <footer class="border-t border-slate-200 bg-white/50 px-8 py-4">
        <p class="text-xs text-slate-400 text-center">
          &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved. Version 2.0
        </p>
      </footer>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
  <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

  <script>
    $(document).ready(function() {
      // Initialize Lucide Icons
      lucide.createIcons();
      // Auto-initialize Select2 on all selects except opted-out
      $('select:not(.no-select2)').select2({
        width: '100%'
      });

      // Auto-initialize Flatpickr on date inputs
      $('.datepicker, input[type="date"]').flatpickr({
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        disableMobile: true
      });

      // Collapsible Card Logic
      $(document).on('click', '.filter-toggle-btn', function() {
        const card = $(this).closest('.card');
        const content = card.find('.filter-card-content');
        const icon = $(this).find('.filter-toggle-icon');

        content.toggleClass('collapsed');
        icon.toggleClass('rotated');

        // Save state if needed (optional)
      });

      $('.datatable').DataTable({
        responsive: true,
        language: {
          search: "",
          searchPlaceholder: "Search..."
        },
        dom: '<"flex justify-between items-center mb-4"lf>rtip'
      });

      // Sidebar Mobile Toggle
      const sidebar = document.querySelector('#mainSidebar');
      const mobileToggle = document.querySelector('header button.lg\\:hidden'); // Specifically targeted
      if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
          sidebar.classList.toggle('-translate-x-full');
        });
      }

      // Sidebar Desktop Collapse Logic
      const desktopToggle = document.getElementById('sidebarToggle');
      const body = document.body;
      const toggleIcon = document.getElementById('toggleIcon');

      function updateToggleIcon() {
        if (body.classList.contains('sidebar-collapsed')) {
          toggleIcon.classList.remove('fa-outdent');
          toggleIcon.classList.add('fa-indent');
        } else {
          toggleIcon.classList.remove('fa-indent');
          toggleIcon.classList.add('fa-outdent');
        }
      }

      // Init icon
      updateToggleIcon();

      desktopToggle.addEventListener('click', () => {
        body.classList.toggle('sidebar-collapsed');
        const isCollapsed = body.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', isCollapsed);
        updateToggleIcon();

        // Trigger resize for Charts/Tables to adjust
        setTimeout(() => {
          window.dispatchEvent(new Event('resize'));
        }, 300);
      });
    });

    // Global Delete Confirmation
    window.confirmDelete = function(url) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5', // Brand 600
        cancelButtonColor: '#94a3b8', // Slate 400
        confirmButtonText: 'Yes, delete it!',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-xl px-5 py-2.5 btn-primary',
          cancelButton: 'rounded-xl px-5 py-2.5'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          let form = document.createElement('form');
          form.action = url;
          form.method = 'POST';
          form.innerHTML = `
              <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
              <input type="hidden" name="_method" value="DELETE">
            `;
          document.body.appendChild(form);
          form.submit();
        }
      });
    };
  </script>
  @stack('scripts')
</body>

</html>
