<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'CATVARA') }}</title>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CSS (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
          colors: {
            brand: {
              50: '#eef2ff',
              100: '#e0e7ff',
              200: '#c7d2fe',
              300: '#a5b4fc',
              400: '#818cf8',
              500: '#6366f1', // Indigo 500
              600: '#4f46e5', // Indigo 600 (Primary Action)
              700: '#4338ca',
              800: '#3730a3',
              900: '#312e81',
            },
            slate: {
              850: '#1e293b',
            }
          },
          boxShadow: {
            'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02)',
            'glow': '0 0 15px rgba(99, 102, 241, 0.15)',
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

  <!-- Custom Styles -->
  <style>
    body {
      background-color: #f8fafc;
      /* Slate 50 */
      color: #18181b;
      /* Zinc 900 */
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }

    ::-webkit-scrollbar-track {
      background: transparent;
    }

    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    /* Select2 Modernization */
    .select2-container .select2-selection--single {
      height: 40px !important;
      border-color: #e2e8f0 !important;
      /* Slate 200 */
      border-radius: 0.5rem !important;
      display: flex !important;
      align-items: center !important;
      box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 38px !important;
    }

    .select2-dropdown {
      border-color: #e2e8f0 !important;
      border-radius: 0.5rem !important;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
      overflow: hidden;
    }

    .select2-search__field {
      border-radius: 0.375rem !important;
      border: 1px solid #e2e8f0 !important;
    }

    .select2-results__option--highlighted {
      background-color: #e0e7ff !important;
      /* Indigo 100 */
      color: #4338ca !important;
      /* Indigo 700 */
    }

    /* DataTables Cleaning */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid #e2e8f0;
      border-radius: 0.375rem;
      padding: 0.4rem 0.6rem;
      font-size: 0.875rem;
      color: #475569;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }

    table.dataTable.no-footer {
      border-bottom: 1px solid #f1f5f9 !important;
    }

    table.dataTable thead th {
      border-bottom: 1px solid #e2e8f0 !important;
      color: #64748b !important;
      font-weight: 500 !important;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.05em;
      padding-bottom: 1rem !important;
    }

    table.dataTable tbody td {
      padding: 1rem 1rem !important;
      color: #334155;
      border-bottom: 1px solid #f8fafc;
    }

    /* Utility */
    .glass {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }

    /* --- Sidebar Collapse Logic --- */
    :root {
      --sidebar-width: 16rem;
      /* w-64 */
      --sidebar-collapsed-width: 5rem;
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

    /* Hide Submenus when Collapsed (and not hovered) */
    body.sidebar-collapsed:not(:hover) .nav-group>div {
      display: none !important;
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
  </style>
  @stack('styles')
  <script>
    // Apply preference immediately to avoid flash
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
      document.documentElement.classList.add('sidebar-collapsed'); // Add to HTML or Body logic below
    }
  </script>
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
          @if(active_company())
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
                <a href="#" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-brand-600">Your
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
        @if(session('success'))
          <div
            class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700 flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
          </div>
        @endif
        @if(session('error'))
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

  <script>
    $(document).ready(function () {
      $('.select2').select2({
        width: '100%'
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
  </script>
  @stack('scripts')
</body>

</html>