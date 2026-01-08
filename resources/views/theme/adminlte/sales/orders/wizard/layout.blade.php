@extends('theme.adminlte.layouts.app')

@section('title', 'Sales Order Wizard')

@push('css')
  <style>
    /* -------------------------------------------------------------------------- */
    /*                     ENTERPRISE POS / WIZARD THEME                          */
    /* -------------------------------------------------------------------------- */
    :root{
      --primary:#4f46e5;
      --primary-dark:#4338ca;
      --secondary:#64748b;
      --success:#10b981;
      --danger:#ef4444;

      --surface:#f8fafc;
      --panel:#ffffff;
      --border:#e2e8f0;

      --shadow-sm: 0 1px 2px rgba(0,0,0,.05);
      --shadow-md: 0 6px 18px rgba(15,23,42,.08);
      --shadow-lg: 0 16px 30px rgba(15,23,42,.12);

      --r-lg: 16px;
      --r-xl: 22px;
    }

    body { background: var(--surface); }

    .wiz-shell {
      background: transparent;
    }

    /* Steps */
    .wiz-steps {
      border: 0;
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-bottom: 18px;
      padding: 0;
    }
    .wiz-steps .nav-link{
      border: 1px solid var(--border);
      background: var(--panel);
      border-radius: 999px;
      padding: 10px 16px;
      font-weight: 700;
      color: var(--secondary);
      box-shadow: var(--shadow-sm);
      transition: .18s ease;
    }
    .wiz-steps .nav-link.active{
      background: var(--primary);
      border-color: var(--primary);
      color: #fff;
      transform: translateY(-1px);
      box-shadow: var(--shadow-md);
    }
    .wiz-steps .nav-link.disabled{
      opacity: .55;
      pointer-events: none;
    }

    /* Cards / Panels */
    .panel-card{
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      box-shadow: var(--shadow-md);
      background: var(--panel);
      overflow: hidden;
    }
    .panel-header{
      background: #0f172a;
      color: #fff;
      padding: 18px 20px;
    }
    .panel-subtitle{ color: rgba(255,255,255,.65); }

    /* Customer Cards */
    .customer-grid{
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
    }
    @media (max-width: 1200px){ .customer-grid{ grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 992px){  .customer-grid{ grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px){  .customer-grid{ grid-template-columns: repeat(1, 1fr); } }

    .customer-card{
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      background: #fff;
      padding: 14px;
      cursor: pointer;
      transition: .18s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .customer-card:hover{
      border-color: var(--primary);
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
    }
    .cust-top{
      display: flex;
      gap: 12px;
      align-items: center;
    }
    .cust-avatar{
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight: 900;
      color: var(--primary-dark);
      flex: 0 0 auto;
      overflow: hidden;
      border: 1px solid rgba(79,70,229,.20);
    }
    .cust-avatar img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display:block;
    }
    .cust-name{
      font-weight: 900;
      color: #0f172a;
      line-height: 1.2;
      margin: 0;
      font-size: 15px;
    }
    .cust-meta{
      color: var(--secondary);
      font-size: 12px;
      margin: 0;
    }
    .cust-badges .badge{
      border-radius: 999px;
      padding: 6px 10px;
      font-weight: 800;
      letter-spacing: .2px;
    }
    .cust-body{
      color: #334155;
      font-size: 12px;
      line-height: 1.45;
      min-height: 42px;
    }
    .cust-foot{
      margin-top: auto;
      display:flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      color: var(--secondary);
      font-size: 12px;
    }
    .cust-select-pill{
      border: 1px dashed var(--border);
      border-radius: 999px;
      padding: 6px 10px;
      font-weight: 800;
      color: #0f172a;
      background: #f8fafc;
    }

    /* Search Bar */
    .search-bar{
      border-radius: 999px;
      overflow: hidden;
      background: #f1f5f9;
      border: 1px solid var(--border);
      box-shadow: var(--shadow-sm);
    }
    .search-bar .input-group-text{
      background: transparent;
      border: 0;
      padding-left: 16px;
    }
    .search-bar .form-control{
      background: transparent;
      border: 0;
      height: 48px;
    }

    /* Primary actions */
    .btn-primary{
      background: var(--primary);
      border-color: var(--primary);
    }
    .btn-primary:hover{
      background: var(--primary-dark);
      border-color: var(--primary-dark);
    }
  </style>
@endpush

@section('content_header')
  <div class="row mb-2">
    <div class="col-12">
      <h1 class="m-0">New Sales Order</h1>
      <small class="text-muted">Wizard flow: Customer → Items → Review</small>
    </div>
  </div>
@endsection

@section('content')
  <div class="container-fluid wiz-shell">
    <ul class="nav nav-pills wiz-steps">
      <li class="nav-item">
        <a class="nav-link {{ ($step ?? 1) == 1 ? 'active' : 'disabled' }}" href="javascript:void(0)">
          <i class="fas fa-user mr-2"></i> 1. Customer
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ ($step ?? 1) == 2 ? 'active' : 'disabled' }}" href="javascript:void(0)">
          <i class="fas fa-cubes mr-2"></i> 2. Items
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ ($step ?? 1) == 3 ? 'active' : 'disabled' }}" href="javascript:void(0)">
          <i class="fas fa-check-circle mr-2"></i> 3. Review
        </a>
      </li>
    </ul>

    @yield('wizard-content')
  </div>
@endsection

@push('scripts')
  {{-- Lodash is required because wizard pages use _.debounce() --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
@endpush
