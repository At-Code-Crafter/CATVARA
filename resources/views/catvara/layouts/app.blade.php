<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Dashboard' }} | CATVARA</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#0f172a',
            secondary: '#f1f5f9',
            accent: '#3b82f6',
          },
          borderRadius: {
            'xl': '0.75rem',
            '2xl': '1rem',
          }
        }
      }
    }
  </script>

  <!-- Plugins CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('catvara/css/theme.css') }}">

  @stack('head')
</head>

<body class="antialiased text-slate-900 bg-slate-50/50">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    @include('catvara.layouts.sidebar')

    <!-- Main Content -->
    <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
      <!-- Navbar -->
      @include('catvara.layouts.navbar')

      <main class="grow">
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
          <!-- Page header -->
          <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
              <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">@yield('page-title', 'Dashboard')</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
              @yield('page-actions')
            </div>
          </div>

          @yield('content')
        </div>
      </main>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <script>
    $(document).ready(function() {
      lucide.createIcons();

      // Global Select2 initialization
      $('.select2').select2({
        theme: 'catvara',
        width: '100%'
      });
    });
  </script>

  @stack('scripts')
</body>

</html>
