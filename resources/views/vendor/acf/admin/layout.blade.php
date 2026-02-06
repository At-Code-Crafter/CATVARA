<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ACF Admin - {{ $title ?? 'Dashboard' }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <script src="{{ asset('vendor/acf/js/conditional-logic.js') }}"></script>
  <style>
    :root {
      --acf-primary: #4f46e5;
      --acf-secondary: #64748b;
      --acf-bg: #f8fafc;
    }

    body {
      background-color: var(--acf-bg);
      font-family: 'Inter', sans-serif;
    }

    .acf-sidebar {
      width: 260px;
      height: 100vh;
      position: fixed;
      background: #fff;
      border-right: 1px solid #e2e8f0;
    }

    .acf-main {
      margin-left: 260px;
      padding: 2rem;
    }

    .acf-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(8px);
    }

    .nav-link {
      border-radius: 8px;
      margin-bottom: 0.5rem;
      transition: all 0.2s;
      color: var(--acf-secondary);
    }

    .nav-link.active {
      background: var(--acf-primary);
      color: #fff !important;
    }

    .nav-link:hover:not(.active) {
      background: #f1f5f9;
    }

    .field-row {
      cursor: move;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: #fff;
      margin-bottom: 0.75rem;
      transition: box-shadow 0.2s;
    }

    .field-row:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
  </style>
</head>

<body>
  <div class="acf-sidebar p-4 d-flex flex-column">
    <h4 class="mb-4 fw-bold text-primary">
      <i class="bi bi-layers-half me-2"></i>ACF v1.0
    </h4>
    <nav class="nav flex-column mb-auto">
      <a class="nav-link {{ request()->routeIs('acf.admin.index') ? 'active' : '' }}"
        href="{{ route('acf.admin.index') }}">
        <i class="bi bi-grid-fill me-2"></i> Field Groups
      </a>
      <a class="nav-link {{ request()->routeIs('acf.admin.import-export.*') ? 'active' : '' }}"
        href="{{ route('acf.admin.import-export.index') }}">
        <i class="bi bi-arrow-left-right me-2"></i> Import/Export
      </a>
      <a class="nav-link {{ request()->routeIs('acf.admin.health') ? 'active' : '' }}"
        href="{{ route('acf.admin.health') }}">
        <i class="bi bi-heart-pulse-fill me-2"></i> Health Check
      </a>
    </nav>
    <div class="mt-4 pt-4 border-top">
      <p class="text-muted small mb-0">Logged in as: <strong>{{ auth()->user()->name ?? 'Admin' }}</strong></p>
    </div>
  </div>

  <main class="acf-main">
    <header class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('acf.admin.index') }}">Field Groups</a></li>
            @yield('breadcrumb')
          </ol>
        </nav>
        <h2 class="fw-bold m-0">@yield('title', 'Dashboard')</h2>
      </div>
      <div>
        @yield('actions')
      </div>
    </header>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show acf-card mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>

</html>
