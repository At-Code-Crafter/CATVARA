@extends('theme.adminlte.layouts.app')

@section('title', 'New Sales Order')

@section('content_header')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1>New Sales Order</h1>
    </div>
  </div>
@endsection

@section('css')
  <style>
    @section('css')

    <style>

    /* -------------------------------------------------------------------------- */
    /*                   ULTRA ENTERPRISE GRADE POS THEME                         */
    /* -------------------------------------------------------------------------- */
    :root {
      --primary-color: #4f46e5;
      --primary-dark: #4338ca;
      --secondary-color: #64748b;
      --success-color: #10b981;
      --bg-surface: #f8fafc;
      --bg-panel: #ffffff;
      --border-color: #e2e8f0;

      --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);

      --radius-lg: 1rem;
      --radius-xl: 1.5rem;
    }

    body {
      background-color: var(--bg-surface);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    /* --- CARDS & PANELS --- */
    .glass-panel {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.5);
      box-shadow: var(--shadow-xl);
      border-radius: var(--radius-xl);
    }

    .card {
      border: none;
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
      transition: transform 0.2s, box-shadow 0.2s;
    }

    /* --- TABS --- */
    .pos-steps {
      background: transparent;
      padding: 0;
      margin-bottom: 2rem !important;
      border: none !important;
      display: flex;
      justify-content: center;
      gap: 1rem;
    }

    .pos-steps .nav-link {
      background: #fff;
      border: 1px solid var(--border-color) !important;
      border-radius: 50rem !important;
      padding: 0.75rem 1.5rem;
      color: var(--secondary-color);
      font-weight: 600;
      transition: all 0.2s;
      box-shadow: var(--shadow-sm);
    }

    .pos-steps .nav-link.active {
      background: var(--primary-color) !important;
      color: #fff !important;
      border-color: var(--primary-color) !important;
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
    }

    .pos-steps .nav-link.disabled {
      opacity: 0.6;
      background: #e2e8f0;
    }

    /* --- CUSTOMER CARD --- */
    .customer-avatar-large {
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
      color: var(--primary-dark);
      font-size: 2.5rem;
      font-weight: 800;
      border: 4px solid #fff;
      box-shadow: var(--shadow-md);
    }

    .stat-card-mini {
      background: #f8fafc;
      border-radius: 12px;
      padding: 1rem;
      text-align: center;
      border: 1px solid var(--border-color);
    }

    /* --- PRODUCT GRID --- */
    .pos-card {
      height: 100%;
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      overflow: hidden;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      transition: all 0.25s ease;
    }

    .pos-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary-color);
    }

    .product-img-container {
      height: 180px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      padding: 1.5rem;
      position: relative;
    }

    .pos-card .card-body {
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      flex: 1;
    }

    .brand-label {
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #94a3b8;
      margin-bottom: 0.25rem;
    }

    .product-name {
      font-weight: 700;
      color: #1e293b;
      font-size: 1rem;
      line-height: 1.4;
      margin-bottom: auto;
      /* Push price down */
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .price-tag {
      color: var(--primary-color);
      background: #e0e7ff;
      padding: 4px 10px;
      border-radius: 8px;
      font-weight: 800;
      font-size: 0.9rem;
    }

    /* --- SIDEBAR CART --- */
    .sticky-cart {
      position: sticky;
      top: 1.5rem;
    }

    .cart-panel {
      border-radius: var(--radius-xl);
      border: none;
      overflow: hidden;
      box-shadow: var(--shadow-xl);
    }

    .cart-header {
      background: #1e293b;
      color: white;
      padding: 1.5rem;
    }

    .cart-table-wrapper {
      max-height: calc(100vh - 450px);
      overflow-y: auto;
      background: #fff;
    }

    .cart-summary {
      background: #f1f5f9;
      padding: 1.5rem;
      border-top: 1px solid var(--border-color);
    }

    /* --- MODAL --- */
    .modal-content {
      border-radius: var(--radius-xl);
      overflow: hidden;
      border: none;
    }

    .modal-product-img-wrapper {
      height: 250px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
    }

    .variant-card {
      border: 1px solid var(--border-color);
      border-radius: 12px;
      transition: 0.2s;
      cursor: pointer;
      padding: 1rem;
      background: #fff;
    }

    .variant-card:hover {
      border-color: var(--primary-color);
      background: #f8fafc;
      box-shadow: var(--shadow-md);
    }
  </style>
@endsection
@endsection

@section('content')
<div class="container-fluid">
  <div class="card card-default shadow-sm border-0">
    <div class="card-header p-0 pt-1 border-bottom-0">
      <ul class="nav nav-tabs pos-steps" id="order-tabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="step1-tab" data-bs-toggle="pill" href="#step1" role="tab"
            aria-controls="step1" aria-selected="true"><i class="fas fa-user mr-2"></i> 1. Select Customer</a>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled" id="step2-tab" data-bs-toggle="pill" href="#step2" role="tab"
            aria-controls="step2" aria-selected="false"><i class="fas fa-cubes mr-2"></i> 2. Add Products</a>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled" id="step3-tab" data-bs-toggle="pill" href="#step3" role="tab"
            aria-controls="step3" aria-selected="false"><i class="fas fa-file-invoice-dollar mr-2"></i> 3. Payment
            Terms</a>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled" id="step4-tab" data-bs-toggle="pill" href="#step4" role="tab"
            aria-controls="step4" aria-selected="false"><i class="fas fa-check-circle mr-2"></i> 4. Preview & Finish</a>
        </li>
      </ul>
    </div>
    <div class="card-body p-4 bg-light">
      <div class="tab-content" id="order-tabs-content">

        <!-- STEP 1: CUSTOMER -->
        <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
          <div class="row justify-content-center py-5">
            <div class="col-lg-7 col-md-9">
              <div class="text-center mb-5">
                <h2 class="font-weight-bold text-dark">Create New Sales Order</h2>
                <p class="text-muted">Begin by selecting the customer for this transaction.</p>
              </div>

              <div class="card glass-panel border-0 mx-auto" style="max-width: 700px;">
                <div class="card-body p-5">

                  <!-- Search Mode -->
                  <div id="customer-search-mode">
                    <div class="text-center mb-4">
                      <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3">
                        <i class="fas fa-search fa-2x"></i>
                      </div>
                      <h4 class="font-weight-bold">Search Customer</h4>
                    </div>

                    <div class="form-group mb-4">
                      <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden">
                        <div class="input-group-prepend">
                          <span class="input-group-text bg-white border-0 pl-4"><i
                              class="fas fa-search text-muted"></i></span>
                        </div>
                        <select class="form-control select2 border-0" id="customer_id"
                          style="width: 100%; height: 50px;"></select>
                      </div>
                      <small class="text-muted text-center d-block mt-3">Try searching by Name, Email, or Phone
                        Number</small>
                    </div>
                  </div>

                  <!-- Selected Mode -->
                  <div id="customer-selected-mode" style="display: none;">
                    <div class="d-flex flex-column align-items-center mb-5">
                      <div
                        class="customer-avatar-large rounded-circle d-flex align-items-center justify-content-center shadow mb-3"
                        id="c-avatar">
                        JD
                      </div>
                      <h2 class="mb-1 font-weight-bold display-4" style="font-size: 2rem;" id="c-name">John Doe</h2>
                      <div class="d-flex text-muted gap-3">
                        <span><i class="fas fa-envelope mr-1"></i> <span id="c-email">email@example.com</span></span>
                        <span class="mx-2">|</span>
                        <span><i class="fas fa-phone mr-1"></i> <span id="c-phone">555-1234</span></span>
                      </div>
                    </div>

                    <div class="row g-3 mb-5">
                      <div class="col-4">
                        <div class="stat-card-mini">
                          <small class="text-muted d-block text-uppercase font-weight-bold"
                            style="font-size: 0.65rem;">Balance</small>
                          <span class="font-weight-bold h5 mb-0 text-success">$0.00</span>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="stat-card-mini">
                          <small class="text-muted d-block text-uppercase font-weight-bold"
                            style="font-size: 0.65rem;">Open Orders</small>
                          <span class="font-weight-bold h5 mb-0 text-dark">0</span>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="stat-card-mini">
                          <small class="text-muted d-block text-uppercase font-weight-bold"
                            style="font-size: 0.65rem;">Terms</small>
                          <span class="font-weight-bold h5 mb-0 text-primary">Net 30</span>
                        </div>
                      </div>
                    </div>

                    <div class="text-center">
                      <button class="btn btn-primary btn-lg px-5 shadow-lg rounded-pill" onclick="nextStep(2)">
                        Start Product Selection <i class="fas fa-arrow-right ml-2"></i>
                      </button>
                      <br>
                      <button class="btn btn-link text-muted btn-sm mt-3" onclick="resetCustomer()">
                        Select Different Customer
                      </button>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- STEP 2: POS -->
        <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
          <div class="row">
            <!-- Left: Product Grid -->
            <div class="col-md-8">
              <div class="card shadow-none bg-transparent">
                <div class="card-body p-0">
                  <div class="row mb-4">
                    <div class="col-md-7">
                      <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-right-0"><i
                            class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-left-0" id="product_search"
                          placeholder="Search by name or SKU...">
                      </div>
                    </div>
                    <div class="col-md-5">
                      <select class="form-control form-control-lg shadow-sm" id="category_filter">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                          <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div id="product-grid" class="row pos-grid"
                    style="max-height: 700px; overflow-y: auto; padding: 10px;">


                    <!-- Products load here -->
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: Cart Summary -->
            <div class="col-md-4">
              <div class="sticky-cart mt-4 mt-md-0">
                <div class="card cart-panel">
                  <div class="cart-header d-flex justify-content-between align-items-center">
                    <div>
                      <h5 class="card-title mb-0 font-weight-bold"><i
                          class="fas fa-shopping-basket mr-2 text-warning"></i> Current Order</h5>
                      <small class="text-white-50">Draft Order</small>
                    </div>
                    <span class="badge badge-primary badge-pill px-3 py-2" id="cart-count">0 Items</span>
                  </div>

                  <div class="card-body p-0">
                    <div class="cart-table-wrapper">
                      <table class="table table-hover mb-0">
                        <thead class="bg-light sticky-top" style="z-index: 5;">
                          <tr class="text-secondary small font-weight-bold text-uppercase">
                            <th class="pl-4">Item</th>
                            <th class="text-center" style="width: 70px;">Qty</th>
                            <th class="text-center" style="width: 80px;">Disc</th>
                            <th class="text-right pr-4">Total</th>
                            <th style="width: 30px;"></th>
                          </tr>
                        </thead>
                        <tbody id="cart-items">
                          <!-- Items -->
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div class="cart-summary">
                    <div class="d-flex justify-content-between mb-2">
                      <span class="text-muted">Subtotal</span>
                      <span class="font-weight-bold text-dark" id="cart-subtotal">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between h4 mb-4 align-items-center">
                      <span class="font-weight-bold text-dark">Total</span>
                      <span class="text-primary font-weight-bolder display-5" style="font-size: 1.5rem;"
                        id="cart-total">$0.00</span>
                    </div>
                    <button
                      class="btn btn-primary btn-lg btn-block py-3 font-weight-bold shadow rounded-pill transition-all"
                      onclick="nextStep(3)">
                      Proceed to Details <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <button class="btn btn-link btn-block text-muted btn-sm mt-3" onclick="prevStep(1)">
                      Back to Customer
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- STEP 3: PAYMENT -->
        <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">
          <div class="row">
            <div class="col-md-7">
              <div class="card shadow-lg border-0 mb-4" style="border-radius: 20px;">
                <div class="card-header bg-dark text-white py-3" style="border-radius: 20px 20px 0 0;">
                  <h5 class="card-title mb-0"><i class="fas fa-file-invoice-dollar mr-2"></i> Payment & Order Details
                  </h5>
                </div>
                <div class="card-body p-4">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group mb-4">
                        <label class="font-weight-bold text-muted small text-uppercase">Payment Term</label>
                        <select class="form-control form-control-lg shadow-sm border-0 bg-white" id="payment_term_id"
                          onchange="saveDraft()" style="border-radius: 12px;">
                          <option value="">Select Term</option>
                          @foreach ($paymentTerms as $term)
                            <option value="{{ $term->id }}" data-days="{{ $term->due_days }}">{{ $term->name }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group mb-4">
                        <label class="font-weight-bold text-muted small text-uppercase">Due Date</label>
                        <input type="text" class="form-control form-control-lg shadow-sm border-0 bg-white"
                          id="due_date" readonly style="border-radius: 12px;">
                      </div>
                    </div>
                  </div>

                  <div class="form-group mb-4">
                    <label class="font-weight-bold text-muted small text-uppercase">Order Notes / Internal
                      Comments</label>
                    <textarea id="order_notes" class="form-control shadow-sm border-0 bg-white" rows="4"
                      placeholder="Enter any extra details, gate codes, or delivery instructions..." style="border-radius: 15px;"
                      onchange="saveDraft()"></textarea>
                  </div>

                  <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted small text-uppercase">Estimated Shipping Cost</label>
                    <div class="input-group shadow-sm" style="border-radius: 12px; overflow: hidden;">
                      <span class="input-group-text bg-white border-0"><i class="fas fa-truck text-muted"></i></span>
                      <input type="number" id="shipping_cost" class="form-control form-control-lg border-0 bg-white"
                        value="0" min="0" onchange="saveDraft()">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-5">
              <div class="card shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-header bg-dark text-white py-3" style="border-radius: 20px 20px 0 0;">
                  <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt mr-2"></i> Delivery Details</h5>
                </div>
                <div class="card-body p-4">
                  <div class="mb-4">
                    <label class="font-weight-bold text-muted small text-uppercase d-block mb-3">Billing
                      Address</label>
                    <div id="bill-to-preview" class="p-4 bg-light rounded-xl border-dashed text-muted small"
                      style="border: 2px dashed #ddd; border-radius: 15px;">
                      <i class="fas fa-info-circle mr-1"></i> Address will be pulled from customer profile.
                    </div>
                  </div>
                  <div>
                    <label class="font-weight-bold text-muted small text-uppercase d-block mb-3">Shipping
                      Address</label>
                    <div id="ship-to-preview" class="p-4 bg-light rounded-xl border-dashed text-muted small"
                      style="border: 2px dashed #ddd; border-radius: 15px;">
                      <i class="fas fa-info-circle mr-1"></i> Defaults to billing address.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center mt-5">
            <button class="btn btn-outline-secondary btn-lg mr-2 px-5" onclick="prevStep(2)">Back</button>
            <button class="btn btn-primary btn-lg px-5 shadow" onclick="nextStep(4)">Review Order <i
                class="fas fa-chevron-right ml-2"></i></button>
          </div>
        </div>

        <!-- STEP 4: PREVIEW -->
        <div class="tab-pane fade" id="step4" role="tabpanel" aria-labelledby="step4-tab">
          <div class="row">
            <div class="col-md-7">
              <!-- Customer Details Card -->
              <div class="card shadow-lg border-0 mb-4" style="border-radius: 20px;">
                <div class="card-header bg-gradient-info text-white py-3" style="border-radius: 20px 20px 0 0;">
                  <h5 class="card-title mb-0"><i class="fas fa-user-circle mr-2"></i> Customer Details</h5>
                </div>
                <div class="card-body p-4">
                  <div class="row">
                    <!-- Customer Info Column -->
                    <div class="col-md-5 border-right">
                      <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3"
                             id="preview-customer-avatar"
                             style="width: 70px; height: 70px; font-size: 1.6rem; font-weight: bold;">
                          ??
                        </div>
                        <div>
                          <h5 class="mb-0 font-weight-bold" id="preview-customer-name">-</h5>
                          <small class="text-muted" id="preview-customer-type-badge"></small>
                        </div>
                      </div>

                      <!-- Contact Info -->
                      <div class="mt-3 mb-3">
                        <div class="mb-2">
                          <i class="fas fa-envelope text-muted mr-2" style="width: 16px;"></i>
                          <span id="preview-customer-email" class="small">-</span>
                        </div>
                        <div class="mb-2">
                          <i class="fas fa-phone text-muted mr-2" style="width: 16px;"></i>
                          <span id="preview-customer-phone" class="small">-</span>
                        </div>
                        <div class="mb-2" id="preview-customer-legal-row" style="display: none;">
                          <i class="fas fa-building text-muted mr-2" style="width: 16px;"></i>
                          <span id="preview-customer-legal" class="small">-</span>
                        </div>
                        <div class="mb-2" id="preview-customer-tax-row" style="display: none;">
                          <i class="fas fa-id-card text-muted mr-2" style="width: 16px;"></i>
                          <span id="preview-customer-tax" class="small">-</span>
                        </div>
                      </div>

                      <!-- Payment Terms Badge -->
                      <div class="mt-3">
                        <span class="badge badge-primary px-3 py-2">
                          <i class="fas fa-credit-card mr-1"></i> <span id="preview-customer-terms">-</span>
                        </span>
                      </div>
                    </div>

                    <!-- Address Column -->
                    <div class="col-md-7">
                      <div class="row">
                        <!-- Primary Address -->
                        <div class="col-12 mb-3">
                          <label class="small text-muted text-uppercase font-weight-bold d-block mb-2">
                            <i class="fas fa-map-marker-alt mr-1"></i> Primary Address
                          </label>
                          <div id="preview-primary-address" class="small text-dark p-3 bg-light rounded" style="line-height: 1.7;">
                            -
                          </div>
                        </div>

                        <!-- Billing & Shipping Addresses -->
                        <div class="col-6">
                          <label class="small text-muted text-uppercase font-weight-bold d-block mb-2">
                            <i class="fas fa-file-invoice mr-1"></i> Billing
                          </label>
                          <div id="preview-billing-address" class="small text-dark" style="line-height: 1.6;">
                            -
                          </div>
                        </div>
                        <div class="col-6">
                          <label class="small text-muted text-uppercase font-weight-bold d-block mb-2">
                            <i class="fas fa-truck mr-1"></i> Shipping
                          </label>
                          <div id="preview-shipping-address" class="small text-dark" style="line-height: 1.6;">
                            -
                          </div>
                        </div>
                      </div>

                      <!-- Notes -->
                      <div class="mt-3" id="preview-customer-notes-row" style="display: none;">
                        <label class="small text-muted text-uppercase font-weight-bold d-block mb-2">
                          <i class="fas fa-sticky-note mr-1"></i> Customer Notes
                        </label>
                        <div id="preview-customer-notes" class="small text-dark p-2 bg-warning-light rounded" style="background: #fff8e1; line-height: 1.5;">
                          -
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Order Breakdown Card -->
              <div class="card shadow-lg border-0 mb-4" style="border-radius: 20px;">
                <div class="card-header bg-dark text-white py-3" style="border-radius: 20px 20px 0 0;">
                  <h5 class="card-title mb-0"><i class="fas fa-list-ul mr-2"></i> Order Breakdown</h5>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="bg-light">
                        <tr class="small text-muted text-uppercase">
                          <th>Item Description</th>
                          <th class="text-right">Price</th>
                          <th class="text-center">Qty</th>
                          <th class="text-right">Line Total</th>
                        </tr>
                      </thead>
                      <tbody id="preview-items">
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="card shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-body p-4">
                  <div class="row">
                    <div class="col-sm-6">
                      <label class="small text-muted text-uppercase font-weight-bold">Internal Notes</label>
                      <p id="preview-notes" class="text-dark small" style="white-space: pre-line;"></p>
                    </div>
                    <div class="col-sm-6">
                      <label class="small text-muted text-uppercase font-weight-bold">Due Date</label>
                      <p id="preview-due" class="text-dark font-weight-bold"></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-5">
              <div class="card shadow-lg border-0 mb-4"
                style="background: #0f172a; color: white; border-radius: 25px;">
                <div class="card-body p-5">
                  <h4 class="mb-4">Order Summary</h4>
                  <div class="d-flex justify-content-between mb-3 text-white-50">
                    <span>Items Total</span>
                    <span id="preview-subtotal">$0.00</span>
                  </div>
                  <div class="d-flex justify-content-between mb-3 text-danger">
                    <span>Total Discounts</span>
                    <span id="preview-discount">-$0.00</span>
                  </div>
                  <div class="d-flex justify-content-between mb-3 text-white-50">
                    <span>VAT (20%)</span>
                    <span id="preview-tax">$0.00</span>
                  </div>
                  <div class="d-flex justify-content-between mb-4 text-white-50">
                    <span>Shipping</span>
                    <span id="preview-shipping">$0.00</span>
                  </div>
                  <hr style="border-color: rgba(255,255,255,0.1);">
                  <div class="d-flex justify-content-between h2 mb-5 font-weight-bold">
                    <span>Grand Total</span>
                    <span class="text-primary" id="preview-total">$0.00</span>
                  </div>
                  <button class="btn btn-primary btn-block btn-xl py-4 font-weight-bold shadow-lg"
                    onclick="saveOrder()" style="border-radius: 20px; font-size: 1.4rem;">
                    CONFIRM & PRINT <i class="fas fa-print ml-2"></i>
                  </button>
                  <button class="btn btn-link btn-block text-white-50 mt-3" onclick="prevStep(3)">Back to
                    settings</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Variant Modal - Wider & Better Scroll -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content shadow-2xl" style="height: 85vh; display: flex; flex-direction: column;">

      <!-- Header -->
      <div class="modal-header bg-dark text-white border-0 px-4 py-3 flex-shrink-0">
        <div class="d-flex align-items-center">
          <div class="bg-primary rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center"
            style="width: 40px; height: 40px;">
            <i class="fas fa-cubes"></i>
          </div>
          <div>
            <h5 class="modal-title font-weight-bold mb-0">Product Options</h5>
            <small class="text-white-50">Select specific variations to add to cart</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>

      <!-- Body with Sidebar and Scroll area -->
      <div class="modal-body p-0 d-flex h-100" style="overflow: hidden;">

        <!-- Left: Product Info (Fixed) -->
        <div class="bg-light border-right p-5 d-flex flex-column align-items-center justify-content-center text-center"
          style="width: 350px; flex-shrink: 0;">
          <div id="modal-product-img-wrapper"
            class="p-3 bg-white rounded-xl shadow-sm border mb-4 d-flex align-items-center justify-content-center"
            style="width: 200px; height: 200px;">
            <!-- Image -->
          </div>
          <h4 id="modal-product-name" class="font-weight-bold mb-2 text-dark"></h4>
          <div class="badge badge-secondary px-3 py-2 mb-3" style="font-size: 0.9rem;" id="modal-product-sku"></div>
          <p class="text-muted small">Choose options from the list on the right.</p>
        </div>

        <!-- Right: Scrollable Variants -->
        <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
          <div id="variant-loader"
            class="h-100 d-flex flex-column align-items-center justify-content-center text-center text-muted">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <span>Loading variations...</span>
          </div>

          <div id="variant-list" class="flex-grow-1 overflow-auto p-4" style="background: #fff;">
            <!-- Content Injected Here -->
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Invoice Preview Modal - PDF Style -->
<div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;" role="document">
    <div class="modal-content shadow-2xl" style="max-height: 95vh; display: flex; flex-direction: column; border-radius: 12px; overflow: hidden;">

      <!-- Header -->
      <div class="modal-header bg-dark text-white border-0 px-4 py-3 flex-shrink-0">
        <div class="d-flex align-items-center">
          <div class="bg-danger rounded p-2 mr-3 d-flex align-items-center justify-content-center"
            style="width: 40px; height: 40px;">
            <i class="fas fa-file-pdf"></i>
          </div>
          <div>
            <h5 class="modal-title font-weight-bold mb-0">Invoice Preview</h5>
            <small class="text-white-50">PDF Document Preview</small>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <button type="button" class="btn btn-outline-light btn-sm mr-2" onclick="downloadInvoicePDF()">
            <i class="fas fa-download mr-1"></i> Download PDF
          </button>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <!-- Body - PDF Style Invoice -->
      <div class="modal-body p-0" style="overflow-y: auto; background: #525659;">
        <!-- A4 Paper Style Container -->
        <div id="invoice-pdf-container" style="padding: 30px;">
          <div id="invoice-preview-content" style="
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.4);
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            position: relative;
          ">

            <!-- Page Content -->
            <div style="padding: 40px;">

              <!-- Header Section -->
              <table style="width: 100%; margin-bottom: 30px;">
                <tr>
                  <td style="vertical-align: top; width: 50%;">
                    <div id="inv-company-logo" style="font-size: 28pt; font-weight: 800; color: #1e293b; letter-spacing: -1px; line-height: 1;">
                      INVOICE
                    </div>
                    <div style="margin-top: 5px; font-size: 9pt; color: #64748b;">
                      Draft Preview - Subject to Confirmation
                    </div>
                  </td>
                  <td style="vertical-align: top; text-align: right; width: 50%;">
                    <table style="margin-left: auto;">
                      <tr>
                        <td style="padding: 3px 15px 3px 0; font-size: 9pt; color: #64748b; text-align: right;">Invoice Number</td>
                        <td style="padding: 3px 0; font-weight: 600;" id="inv-number">-</td>
                      </tr>
                      <tr>
                        <td style="padding: 3px 15px 3px 0; font-size: 9pt; color: #64748b; text-align: right;">Date of Issue</td>
                        <td style="padding: 3px 0; font-weight: 600;" id="inv-date">-</td>
                      </tr>
                      <tr>
                        <td style="padding: 3px 15px 3px 0; font-size: 9pt; color: #64748b; text-align: right;">Due Date</td>
                        <td style="padding: 3px 0; font-weight: 600;" id="inv-due-date">-</td>
                      </tr>
                      <tr>
                        <td style="padding: 3px 15px 3px 0; font-size: 9pt; color: #64748b; text-align: right;">Invoice Total</td>
                        <td style="padding: 3px 0; font-weight: 700; font-size: 14pt; color: #4f46e5;" id="inv-header-total">$0.00</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Company Branding -->
              <div style="border-bottom: 2px solid #e2e8f0; padding-bottom: 25px; margin-bottom: 25px;">
                <table style="width: 100%;">
                  <tr>
                    <td style="vertical-align: top; width: 40%;">
                      <div id="inv-company-name" style="font-size: 18pt; font-weight: 700; color: #1e293b; margin-bottom: 5px;">
                        Company Name
                      </div>
                      <div id="inv-company-address" style="font-size: 9pt; color: #64748b; line-height: 1.6;"></div>
                    </td>
                    <td style="vertical-align: top; text-align: right; width: 60%;">
                      <div style="background: #f8fafc; padding: 15px; border-radius: 6px; display: inline-block; text-align: left;">
                        <div style="font-size: 8pt; text-transform: uppercase; color: #94a3b8; font-weight: 600; margin-bottom: 5px;">Additional Details</div>
                        <div style="font-size: 9pt; color: #64748b;">
                          <span id="inv-uuid">-</span>
                        </div>
                      </div>
                    </td>
                  </tr>
                </table>
              </div>

              <!-- Address Section -->
              <table style="width: 100%; margin-bottom: 30px;">
                <tr>
                  <td style="vertical-align: top; width: 33%; padding-right: 20px;">
                    <div style="font-size: 8pt; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 8px;">Bill To</div>
                    <div id="inv-billing-address" style="font-size: 10pt; line-height: 1.6; color: #334155;"></div>
                  </td>
                  <td style="vertical-align: top; width: 33%; padding-right: 20px;">
                    <div style="font-size: 8pt; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 8px;">Ship To</div>
                    <div id="inv-shipping-address" style="font-size: 10pt; line-height: 1.6; color: #334155;"></div>
                  </td>
                  <td style="vertical-align: top; width: 33%; text-align: right;">
                    <div style="font-size: 8pt; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 8px;">Merchant</div>
                    <div id="inv-merchant-address" style="font-size: 10pt; line-height: 1.6; color: #334155;"></div>
                  </td>
                </tr>
              </table>

              <!-- Items Table -->
              <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <thead>
                  <tr style="border-bottom: 2px solid #1e293b;">
                    <th style="padding: 12px 0; text-align: left; font-size: 9pt; text-transform: uppercase; font-weight: 700; color: #1e293b;">Description</th>
                    <th style="padding: 12px 0; text-align: center; font-size: 9pt; text-transform: uppercase; font-weight: 700; color: #1e293b; width: 70px;">Qty</th>
                    <th style="padding: 12px 0; text-align: right; font-size: 9pt; text-transform: uppercase; font-weight: 700; color: #1e293b; width: 90px;">Unit Price</th>
                    <th style="padding: 12px 0; text-align: right; font-size: 9pt; text-transform: uppercase; font-weight: 700; color: #1e293b; width: 70px;">VAT</th>
                    <th style="padding: 12px 0; text-align: right; font-size: 9pt; text-transform: uppercase; font-weight: 700; color: #1e293b; width: 100px;">Amount</th>
                  </tr>
                </thead>
                <tbody id="inv-items-body">
                  <!-- Items will be injected here -->
                </tbody>
              </table>

              <!-- Summary Section -->
              <table style="width: 100%;">
                <tr>
                  <td style="vertical-align: top; width: 50%; padding-right: 30px;">
                    <div style="font-size: 8pt; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 8px;">Additional Comments</div>
                    <div id="inv-notes" style="font-size: 9pt; color: #64748b; line-height: 1.6; min-height: 60px;">
                      -
                    </div>
                  </td>
                  <td style="vertical-align: top; width: 50%;">
                    <table style="width: 100%; border-collapse: collapse;">
                      <tr>
                        <td style="padding: 8px 0; font-size: 10pt; color: #64748b;">Subtotal (Net)</td>
                        <td style="padding: 8px 0; text-align: right; font-size: 10pt;" id="inv-subtotal">$0.00</td>
                      </tr>
                      <tr>
                        <td style="padding: 8px 0; font-size: 10pt; color: #dc2626;">Discount</td>
                        <td style="padding: 8px 0; text-align: right; font-size: 10pt; color: #dc2626;" id="inv-discount">-$0.00</td>
                      </tr>
                      <tr>
                        <td style="padding: 8px 0; font-size: 10pt; color: #64748b;">VAT (20%)</td>
                        <td style="padding: 8px 0; text-align: right; font-size: 10pt;" id="inv-tax">$0.00</td>
                      </tr>
                      <tr>
                        <td style="padding: 8px 0; font-size: 10pt; color: #64748b;">Shipping</td>
                        <td style="padding: 8px 0; text-align: right; font-size: 10pt;" id="inv-shipping">$0.00</td>
                      </tr>
                      <tr style="border-top: 2px solid #1e293b;">
                        <td style="padding: 12px 0; font-size: 13pt; font-weight: 700; color: #1e293b;">Total</td>
                        <td style="padding: 12px 0; text-align: right; font-size: 13pt; font-weight: 700; color: #1e293b;" id="inv-total">$0.00</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

            </div>

            <!-- Footer -->
            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 20px 40px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
              <table style="width: 100%;">
                <tr>
                  <td style="font-size: 8pt; color: #64748b;">
                    <strong>Payment Terms:</strong> <span id="inv-payment-terms">-</span>
                  </td>
                  <td style="text-align: right; font-size: 8pt; color: #94a3b8;">
                    Generated on <span id="inv-generated-date">-</span>
                  </td>
                </tr>
              </table>
            </div>

          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="modal-footer bg-light border-0 px-4 py-3 flex-shrink-0">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          <i class="fas fa-arrow-left mr-2"></i> Back to Edit
        </button>
        <button type="button" class="btn btn-outline-primary px-4 mr-2" onclick="downloadInvoicePDF()">
          <i class="fas fa-file-pdf mr-2"></i> Download PDF
        </button>
        <button type="button" class="btn btn-success btn-lg px-5 font-weight-bold" onclick="confirmAndPrint()">
          <i class="fas fa-check-circle mr-2"></i> Confirm Order
        </button>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
  // -----------------------------
  // STATE
  // -----------------------------
  let orderState = {
    uuid: null,
    customerId: null,
    customerName: '',
    customerEmail: '',
    customerPhone: '',
    customerInitials: '',
    customerType: '',
    customerLegalName: '',
    customerTaxNumber: '',
    customerNotes: '',
    customerAddress: '',
    customerPostalCode: '',
    customerCountry: '',
    customerState: '',
    billingAddress: null,
    shippingAddress: null,

    items: [], // { id(variant_id), name, sku, price, qty, discount }

    paymentTermId: null,
    paymentTermName: '',
    dueDate: null
  };

  // -----------------------------
  // CONFIG (Blade route templates)
  // -----------------------------
  const routes = {
    searchCustomers: @json(company_route('sales-orders.searchCustomers')),
    searchProducts: @json(company_route('sales-orders.searchProducts')),
    getVariantsTemplate: @json(company_route('sales-orders.getVariants', ['product' => '__PRODUCT__'])),
    storeDraft: @json(company_route('sales-orders.storeDraft')),
    storeOrder: @json(company_route('sales-orders.store'))
  };

  // -----------------------------
  // UTIL HELPERS
  // -----------------------------
  function money(n) {
    n = parseFloat(n || 0);
    return '$' + n.toFixed(2);
  }

  function showToast(type, msg) {
    if (window.toastr) {
      toastr[type](msg);
      return;
    }
    if (window.Swal) {
      Swal.fire({
        title: type === 'success' ? 'Done' : 'Info',
        text: msg,
        timer: 1200,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
      return;
    }
    alert(msg);
  }

  function confirmDialog(title, text) {
    if (!window.Swal) return Promise.resolve(confirm(text));
    return Swal.fire({
      title: title,
      text: text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#000',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Confirm'
    }).then(r => r.isConfirmed);
  }

  // Bootstrap 4/5 modal safe helpers
  function showModal(selector) {
    // BS4 jQuery modal
    if ($.fn.modal) return $(selector).modal('show');

    // BS5 native
    const el = document.querySelector(selector);
    const modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
  }

  function hideModal(selector) {
    if ($.fn.modal) return $(selector).modal('hide');

    const el = document.querySelector(selector);
    const modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.hide();
  }

  function enableAndGoToStep(step) {
    const tabEl = document.querySelector('#step' + step + '-tab');
    if (!tabEl) return;

    $('#step' + step + '-tab').removeClass('disabled');

    // BS5 Tab
    if (window.bootstrap && bootstrap.Tab) {
      const tab = new bootstrap.Tab(tabEl);
      tab.show();
    } else {
      // fallback
      $(tabEl).trigger('click');
    }

    if (step === 4) updatePreview();
  }

  // -----------------------------
  // ADDRESS PREVIEW
  // -----------------------------
  function updateAddressPreviews() {
    // Billing
    if (orderState.billingAddress) {
      let b = orderState.billingAddress;
      let html = `<strong>${b.contact_name || orderState.customerName}</strong><br>`;
      if (b.address_line_1) {
        html += `${b.address_line_1}<br>`;
        if (b.address_line_2) html += `${b.address_line_2}<br>`;
        html += `${b.city || ''}${b.city ? ',' : ''} ${b.postal_code || ''}<br>${b.country || ''}`;
      } else {
        html += `<span class="text-danger"><em>No address on file</em></span>`;
      }
      $('#bill-to-preview').html(html);
    }

    // Shipping
    if (orderState.shippingAddress) {
      let s = orderState.shippingAddress;
      let html = `<strong>${s.contact_name || orderState.customerName}</strong><br>`;
      if (s.address_line_1) {
        html += `${s.address_line_1}<br>`;
        if (s.address_line_2) html += `${s.address_line_2}<br>`;
        html += `${s.city || ''}${s.city ? ',' : ''} ${s.postal_code || ''}<br>${s.country || ''}`;
      } else {
        html += `<em>Same as billing</em>`;
      }
      $('#ship-to-preview').html(html);
    }
  }

  // -----------------------------
  // CART TOTALS + RENDER
  // -----------------------------
  function computeTotals() {
    let subtotal = 0;
    let discountTotal = 0;

    orderState.items.forEach(i => {
      subtotal += (parseFloat(i.price) || 0) * (parseInt(i.qty) || 0);
      discountTotal += (parseFloat(i.discount) || 0);
    });

    let shipping = parseFloat($('#shipping_cost').val()) || 0;
    let taxable = Math.max(0, subtotal - discountTotal);
    let vat = taxable * 0.20; // 20% VAT
    let total = taxable + vat + shipping;

    return {
      subtotal,
      discountTotal,
      shipping,
      vat,
      total,
      taxable
    };
  }

  function renderCart() {
    let html = '';
    const totals = computeTotals();

    if (orderState.items.length === 0) {
      html = '<tr><td colspan="5" class="text-center text-muted p-4">Cart is empty</td></tr>';
      $('#cart-items').html(html);
      $('#cart-count').text('0 Items');
      $('#cart-subtotal').text(money(0));
      $('#cart-total').text(money(0));
      return;
    }

    orderState.items.forEach((item, index) => {
      const safeName = (item.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      const safeSku = (item.sku || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');

      html += `
          <tr>
            <td>
              <div class="font-weight-bold show-ellipsis" style="max-width: 150px;">${safeName}</div>
              <div class="small text-muted">${safeSku}</div>
            </td>
            <td class="text-center">
              <input type="number"
                     class="form-control form-control-sm text-center mx-auto js-qty"
                     style="width: 60px"
                     value="${item.qty}"
                     min="1"
                     data-index="${index}">
            </td>
            <td class="text-center">
              <input type="number"
                     class="form-control form-control-sm text-center mx-auto js-discount"
                     style="width: 80px"
                     value="${item.discount || 0}"
                     min="0"
                     step="0.01"
                     data-index="${index}">
            </td>
            <td class="text-right">${money(item.price)}</td>
            <td class="text-center">
              <button class="btn btn-xs btn-outline-danger js-remove" data-index="${index}">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
    });

    $('#cart-items').html(html);
    $('#cart-count').text(orderState.items.length + ' Items');
    $('#cart-subtotal').text(money(totals.taxable));
    $('#cart-total').text(money(totals.total));
  }

  // -----------------------------
  // DRAFT SAVE (debounced)
  // -----------------------------
  const saveDraft = _.debounce(function() {
    // If route not available yet, just skip quietly
    if (!routes.storeDraft || routes.storeDraft === '#') return;

    $.post(routes.storeDraft, {
      _token: "{{ csrf_token() }}",
      order_uuid: orderState.uuid,

      customer_id: orderState.customerId,

      items: orderState.items.map(i => ({
        variant_id: i.id,
        qty: i.qty,
        price: i.price,
        name: i.name,
        discount: i.discount || 0
      })),

      payment_term_id: orderState.paymentTermId,
      notes: $('#order_notes').val(),
      shipping_total: $('#shipping_cost').val() || 0,

      billing_address: orderState.billingAddress,
      shipping_address: orderState.shippingAddress
    }).done(function(res) {
      if (res && res.success && res.order_uuid) {
        orderState.uuid = res.order_uuid;
      }
    });
  }, 500);

  // -----------------------------
  // PRODUCTS + VARIANTS
  // -----------------------------
  function loadProducts() {
    let q = $('#product_search').val();
    let cat = $('#category_filter').val();

    $.get(routes.searchProducts, {
      q: q,
      category_id: cat
    }, function(data) {
      let html = '';

      if (!data || data.length === 0) {
        html = '<div class="col-12 text-center p-5 text-muted"><h4>No products found</h4></div>';
        $('#product-grid').html(html);
        return;
      }

      data.forEach(item => {
        const safeName = (item.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const safeSku = (item.sku || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const img = item.image || '';

        html += `
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
              <div class="card pos-card js-open-variant"
                   data-product-id="${item.id}"
                   data-product-name="${_.escape(item.name || '')}"
                   data-product-sku="${_.escape(item.sku || '')}"
                   data-product-image="${_.escape(img)}">
                <div class="product-img-container">
                  <img src="${img}" alt="${safeName}" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                </div>
                <div class="card-body">
                  <div class="brand-label">SKU: ${safeSku}</div>
                  <h6 class="product-name" title="${_.escape(item.name)}">${safeName}</h6>
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="price-tag">View</span>
                    <i class="fas fa-plus-circle text-primary fa-lg"></i>
                  </div>
                </div>
              </div>
            </div>
          `;
      });

      $('#product-grid').html(html);
    });
  }

  function openVariantModal(productId, productName, productSku, productImage) {
    showModal('#variantModal');

    $('#modal-product-name').text(productName || '');
    $('#modal-product-sku').text(productSku || '');
    $('#modal-product-img-wrapper').html(
      productImage ?
      `<img src="${productImage}" class="img-fluid" style="max-height: 200px; object-fit: contain;">` :
      `<div class="text-muted small">No image</div>`
    );

    $('#variant-list').html('');
    $('#variant-loader').show();

    const url = routes.getVariantsTemplate.replace('__PRODUCT__', productId);

    $.get(url, function(variants) {
      $('#variant-loader').hide();

      if (!variants || variants.length === 0) {
        $('#variant-list').html(
          '<div class="p-5 text-center text-muted h-100 d-flex flex-column align-items-center justify-content-center">' +
          '<h4>No variants available</h4><p>This product may not have any active variations.</p>' +
          '</div>'
        );
        return;
      }

      let html = '<div class="row g-3">';
      variants.forEach(v => {
        // Encode variant safely for dataset
        const encoded = encodeURIComponent(JSON.stringify(v));
        html += `
            <div class="col-xl-6 col-lg-6 col-md-12">
              <div class="variant-card js-select-variant d-flex align-items-center p-3 h-100"
                   data-variant="${encoded}">

                <div class="flex-grow-1">
                    <h6 class="font-weight-bold text-dark mb-1">${_.escape(v.name || '')}</h6>
                    <small class="text-muted d-block">SKU: ${_.escape(v.sku || '')}</small>
                </div>

                <div class="text-right pl-3">
                    <div class="h5 font-weight-bold text-primary mb-0">${money(v.price)}</div>
                    <small class="text-muted">Click to Add</small>
                </div>

              </div>
            </div>
          `;
      });
      html += '</div>';

      $('#variant-list').html(html);
    }).fail(function() {
      $('#variant-loader').hide();
      $('#variant-list').html(
        '<p class="text-danger p-4 mb-0">Error loading variants. Route missing or server error.</p>');
    });
  }

  function addToCart(variant) {
    // Variant must include: id, name, sku, price
    if (!variant || !variant.id) return;

    let existing = orderState.items.find(i => i.id === variant.id);
    if (existing) {
      existing.qty = (parseInt(existing.qty) || 0) + 1;
    } else {
      orderState.items.push({
        id: variant.id,
        name: variant.name || 'Item',
        sku: variant.sku || '',
        price: parseFloat(variant.price) || 0,
        qty: 1,
        discount: 0
      });
    }

    renderCart();
    saveDraft();
    showToast('success', 'Item added to cart');
  }

  // -----------------------------
  // PREVIEW
  // -----------------------------
  function updatePreview() {
    $('#preview-notes').text($('#order_notes').val() || 'No notes added.');
    $('#preview-due').text(orderState.dueDate || 'Not set');

    // Update Customer Details
    $('#preview-customer-avatar').text(orderState.customerInitials || '??');
    $('#preview-customer-name').text(orderState.customerName || '-');
    $('#preview-customer-email').text(orderState.customerEmail || '-');
    $('#preview-customer-phone').text(orderState.customerPhone || '-');
    $('#preview-customer-terms').text(orderState.paymentTermName || 'No payment terms');

    // Customer Type Badge
    if (orderState.customerType) {
      let typeClass = orderState.customerType === 'BUSINESS' ? 'badge-info' : 'badge-secondary';
      let typeLabel = orderState.customerType === 'BUSINESS' ? 'Business' : 'Individual';
      $('#preview-customer-type-badge').html(`<span class="badge ${typeClass}">${typeLabel}</span>`);
    } else {
      $('#preview-customer-type-badge').html('');
    }

    // Legal Name (for business customers)
    if (orderState.customerLegalName) {
      $('#preview-customer-legal').text(orderState.customerLegalName);
      $('#preview-customer-legal-row').show();
    } else {
      $('#preview-customer-legal-row').hide();
    }

    // Tax Number
    if (orderState.customerTaxNumber) {
      $('#preview-customer-tax').text(orderState.customerTaxNumber);
      $('#preview-customer-tax-row').show();
    } else {
      $('#preview-customer-tax-row').hide();
    }

    // Customer Notes
    if (orderState.customerNotes) {
      $('#preview-customer-notes').text(orderState.customerNotes);
      $('#preview-customer-notes-row').show();
    } else {
      $('#preview-customer-notes-row').hide();
    }

    // Primary Address (from customer profile)
    let primaryHtml = '';
    if (orderState.customerAddress) {
      primaryHtml = `${_.escape(orderState.customerAddress)}<br>`;
      if (orderState.customerState) primaryHtml += `${_.escape(orderState.customerState)}, `;
      if (orderState.customerPostalCode) primaryHtml += `${_.escape(orderState.customerPostalCode)}<br>`;
      if (orderState.customerCountry) primaryHtml += `${_.escape(orderState.customerCountry)}`;
    } else {
      primaryHtml = '<span class="text-muted"><em>No primary address on file</em></span>';
    }
    $('#preview-primary-address').html(primaryHtml);

    // Update Billing Address
    if (orderState.billingAddress) {
      let b = orderState.billingAddress;
      let billHtml = '';
      if (b.address_line_1) {
        billHtml = `<strong>${_.escape(b.contact_name || orderState.customerName)}</strong><br>`;
        billHtml += `${_.escape(b.address_line_1)}<br>`;
        if (b.address_line_2) billHtml += `${_.escape(b.address_line_2)}<br>`;
        billHtml += `${_.escape(b.city || '')}${b.city ? ', ' : ''}${_.escape(b.postal_code || '')}<br>`;
        billHtml += `${_.escape(b.country || '')}`;
      } else {
        billHtml = '<span class="text-muted"><em>No address on file</em></span>';
      }
      $('#preview-billing-address').html(billHtml);
    } else {
      $('#preview-billing-address').html('<span class="text-muted"><em>No address on file</em></span>');
    }

    // Update Shipping Address
    if (orderState.shippingAddress) {
      let s = orderState.shippingAddress;
      let shipHtml = '';
      if (s.address_line_1) {
        shipHtml = `<strong>${_.escape(s.contact_name || orderState.customerName)}</strong><br>`;
        shipHtml += `${_.escape(s.address_line_1)}<br>`;
        if (s.address_line_2) shipHtml += `${_.escape(s.address_line_2)}<br>`;
        shipHtml += `${_.escape(s.city || '')}${s.city ? ', ' : ''}${_.escape(s.postal_code || '')}<br>`;
        shipHtml += `${_.escape(s.country || '')}`;
      } else {
        shipHtml = '<span class="text-muted"><em>Same as billing</em></span>';
      }
      $('#preview-shipping-address').html(shipHtml);
    } else {
      $('#preview-shipping-address').html('<span class="text-muted"><em>Same as billing</em></span>');
    }

    let html = '';
    let subtotal = 0;
    let discountTotal = 0;

    orderState.items.forEach(item => {
      let itemSub = (parseFloat(item.price) || 0) * (parseInt(item.qty) || 0);
      let disc = parseFloat(item.discount) || 0;
      let lineTotal = Math.max(0, itemSub - disc);

      subtotal += itemSub;
      discountTotal += disc;

      html += `
          <tr>
            <td>
              <div class="font-weight-bold">${_.escape(item.name || '')}</div>
              <small class="text-muted">SKU: ${_.escape(item.sku || '')}</small>
            </td>
            <td class="text-right">${money(item.price)}</td>
            <td class="text-center">${item.qty}</td>
            <td class="text-right">${money(lineTotal)}</td>
          </tr>
        `;
    });

    let shipping = parseFloat($('#shipping_cost').val()) || 0;
    let taxable = Math.max(0, subtotal - discountTotal);
    let vat = taxable * 0.20;
    let total = taxable + vat + shipping;

    $('#preview-items').html(html);
    $('#preview-subtotal').text(money(subtotal));
    $('#preview-discount').text('-' + money(discountTotal));
    $('#preview-tax').text(money(vat));
    $('#preview-shipping').text(money(shipping));
    $('#preview-total').text(money(total));
  }

  // -----------------------------
  // STEP NAV
  // -----------------------------
  function nextStep(step) {
    if (step === 2 && !orderState.customerId) {
      showToast('error', 'Please select a customer first');
      return;
    }
    if (step === 3 && orderState.items.length === 0) {
      showToast('error', 'Please add at least one product');
      return;
    }
    enableAndGoToStep(step);
  }

  function prevStep(step) {
    enableAndGoToStep(step);
  }

  // -----------------------------
  // FINAL SAVE - Show Invoice Preview Modal
  // -----------------------------
  function saveOrder() {
    if (orderState.items.length === 0) {
      showToast('error', 'Missing line items');
      return;
    }

    // Ensure draft exists first
    if (!orderState.uuid) {
      saveDraft.flush && saveDraft.flush();
      saveDraft();

      if (window.Swal) {
        Swal.fire({
          title: 'Preparing invoice preview...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });
      }

      setTimeout(saveOrder, 700);
      return;
    }

    if (window.Swal) Swal.close();

    // Populate and show invoice preview modal
    populateInvoicePreview();
    showModal('#invoicePreviewModal');
  }

  // -----------------------------
  // POPULATE INVOICE PREVIEW
  // -----------------------------
  function populateInvoicePreview() {
    const today = new Date();
    const dateStr = today.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });
    const invoiceNum = 'DRAFT-' + (orderState.uuid ? orderState.uuid.substring(0, 8).toUpperCase() : 'PENDING');

    // Invoice Header
    $('#inv-number').text(invoiceNum);
    $('#inv-date').text(dateStr);
    $('#inv-due-date').text(orderState.dueDate || 'Upon Receipt');
    $('#inv-uuid').text('ID: ' + (orderState.uuid || 'Pending'));
    $('#inv-generated-date').text(dateStr);

    // Company Info
    $('#inv-company-name').text('{{ config("app.name", "Your Company") }}');
    $('#inv-company-address').html('{{ config("app.address", "Company Address") }}<br>{{ config("app.email", "") }}');

    // Merchant Info (right side)
    $('#inv-merchant-address').html('<strong>{{ config("app.name", "Your Company") }}</strong><br>{{ config("app.address", "Company Address") }}<br>{{ config("app.email", "") }}');

    // Billing Address
    let billHtml = `<strong>${_.escape(orderState.customerName)}</strong><br>`;
    if (orderState.customerEmail) billHtml += `${_.escape(orderState.customerEmail)}<br>`;
    if (orderState.customerPhone) billHtml += `${_.escape(orderState.customerPhone)}<br>`;

    if (orderState.billingAddress && orderState.billingAddress.address_line_1) {
      let b = orderState.billingAddress;
      billHtml += `${_.escape(b.address_line_1)}<br>`;
      if (b.address_line_2) billHtml += `${_.escape(b.address_line_2)}<br>`;
      billHtml += `${_.escape(b.city || '')}${b.city ? ', ' : ''}${_.escape(b.postal_code || '')}<br>`;
      billHtml += `${_.escape(b.country || '')}`;
    } else if (orderState.customerAddress) {
      billHtml += `${_.escape(orderState.customerAddress)}<br>`;
      if (orderState.customerState) billHtml += `${_.escape(orderState.customerState)}, `;
      if (orderState.customerPostalCode) billHtml += `${_.escape(orderState.customerPostalCode)}<br>`;
      if (orderState.customerCountry) billHtml += `${_.escape(orderState.customerCountry)}`;
    }
    $('#inv-billing-address').html(billHtml);

    // Shipping Address
    let shipHtml = '';
    if (orderState.shippingAddress && orderState.shippingAddress.address_line_1) {
      let s = orderState.shippingAddress;
      shipHtml = `<strong>${_.escape(s.contact_name || orderState.customerName)}</strong><br>`;
      shipHtml += `${_.escape(s.address_line_1)}<br>`;
      if (s.address_line_2) shipHtml += `${_.escape(s.address_line_2)}<br>`;
      shipHtml += `${_.escape(s.city || '')}${s.city ? ', ' : ''}${_.escape(s.postal_code || '')}<br>`;
      shipHtml += `${_.escape(s.country || '')}`;
    } else {
      shipHtml = '<em style="color: #94a3b8;">Same as billing address</em>';
    }
    $('#inv-shipping-address').html(shipHtml);

    // Items Table
    let itemsHtml = '';
    let subtotal = 0;
    let discountTotal = 0;

    orderState.items.forEach((item, index) => {
      const itemSubtotal = (parseFloat(item.price) || 0) * (parseInt(item.qty) || 0);
      const discount = parseFloat(item.discount) || 0;
      const lineTotal = Math.max(0, itemSubtotal - discount);
      const taxRate = 20; // 20% VAT

      subtotal += itemSubtotal;
      discountTotal += discount;

      itemsHtml += `
        <tr style="border-bottom: 1px solid #e2e8f0;">
          <td style="padding: 12px 0; vertical-align: top;">
            <div style="font-weight: 600; color: #1e293b;">${_.escape(item.name || '')}</div>
            <div style="font-size: 9pt; color: #94a3b8;">SKU: ${_.escape(item.sku || '-')}</div>
            ${discount > 0 ? `<div style="font-size: 9pt; color: #dc2626; font-style: italic;">Discount: -${money(discount)}</div>` : ''}
          </td>
          <td style="padding: 12px 0; text-align: center; color: #334155;">${item.qty}</td>
          <td style="padding: 12px 0; text-align: right; color: #334155;">${money(item.price)}</td>
          <td style="padding: 12px 0; text-align: right; color: #334155;">${taxRate}%</td>
          <td style="padding: 12px 0; text-align: right; font-weight: 600; color: #1e293b;">${money(lineTotal)}</td>
        </tr>
      `;
    });

    $('#inv-items-body').html(itemsHtml);

    // Calculate totals
    const shipping = parseFloat($('#shipping_cost').val()) || 0;
    const taxable = Math.max(0, subtotal - discountTotal);
    const vat = taxable * 0.20;
    const shippingVat = shipping * 0.20;
    const total = taxable + vat + shipping + shippingVat;

    $('#inv-subtotal').text(money(subtotal - discountTotal));
    $('#inv-discount').text('-' + money(discountTotal));
    $('#inv-tax').text(money(vat + shippingVat));
    $('#inv-shipping').text(money(shipping));
    $('#inv-total').text(money(total));
    $('#inv-header-total').text(money(total));

    // Notes & Payment Terms
    $('#inv-notes').text($('#order_notes').val() || 'None');
    $('#inv-payment-terms').text(orderState.paymentTermName || 'Due on Receipt');
  }

  // -----------------------------
  // DOWNLOAD INVOICE AS PDF
  // -----------------------------
  function downloadInvoicePDF() {
    const element = document.getElementById('invoice-preview-content');
    const invoiceNum = $('#inv-number').text() || 'Invoice';

    // Show loading
    if (window.Swal) {
      Swal.fire({
        title: 'Generating PDF...',
        text: 'Please wait while we create your invoice.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
    }

    const opt = {
      margin: 0,
      filename: `${invoiceNum}_${orderState.customerName.replace(/\s+/g, '_')}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: {
        scale: 2,
        useCORS: true,
        letterRendering: true
      },
      jsPDF: {
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait'
      }
    };

    html2pdf().set(opt).from(element).save().then(() => {
      if (window.Swal) Swal.close();
      showToast('success', 'PDF downloaded successfully!');
    }).catch((err) => {
      if (window.Swal) Swal.close();
      showToast('error', 'Failed to generate PDF');
      console.error(err);
    });
  }

  // -----------------------------
  // CONFIRM AND PRINT (After modal preview)
  // -----------------------------
  function confirmAndPrint() {
    hideModal('#invoicePreviewModal');

    if (window.Swal) {
      Swal.fire({
        title: 'Confirming Order...',
        text: 'Please wait while we finalize your order.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
    }

    $.post(routes.storeOrder, {
      _token: "{{ csrf_token() }}",
      order_uuid: orderState.uuid
    }).done(function(res) {
      if (window.Swal) Swal.close();

      if (res && res.success && res.redirect) {
        // Show success then redirect to print
        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: 'Order Confirmed!',
            text: 'Opening invoice for printing...',
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.href = res.redirect;
          });
        } else {
          window.location.href = res.redirect;
        }
      } else {
        showToast('error', (res && res.message) ? res.message : 'Unable to finalize order');
      }
    }).fail(function(err) {
      if (window.Swal) Swal.close();
      let msg = err.responseJSON ? err.responseJSON.message : 'Something went wrong';
      showToast('error', msg);
    });
  }

  // -----------------------------
  // CUSTOMER SELECT2 TEMPLATE
  // -----------------------------
  function formatCustomerResult(c) {
    if (!c.id) return c.text;
    return $(`
        <div class="d-flex align-items-center py-1">
          <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3"
               style="width: 40px; height: 40px; font-weight: bold; color: #555;">
            ${c.initials || '??'}
          </div>
          <div>
            <div class="font-weight-bold">${_.escape(c.display_name || '')}</div>
            <div class="small text-muted">${_.escape(c.email || '')}</div>
          </div>
        </div>
      `);
  }

  // -----------------------------
  // RESET CUSTOMER
  // -----------------------------
  function resetCustomer() {
    $('#customer_id').val(null).trigger('change');
    orderState.customerId = null;
    orderState.customerName = '';
    orderState.customerEmail = '';
    orderState.customerPhone = '';
    orderState.customerInitials = '';
    orderState.customerType = '';
    orderState.customerLegalName = '';
    orderState.customerTaxNumber = '';
    orderState.customerNotes = '';
    orderState.customerAddress = '';
    orderState.customerPostalCode = '';
    orderState.customerCountry = '';
    orderState.customerState = '';
    orderState.billingAddress = null;
    orderState.shippingAddress = null;

    $('#customer-selected-mode').hide();
    $('#customer-search-mode').fadeIn();
  }

  // -----------------------------
  // INIT
  // -----------------------------
  $(document).ready(function() {

    // Always render cart initially
    renderCart();

    // Recalc totals when shipping changes
    $('#shipping_cost').on('input change', function() {
      renderCart();
      saveDraft();
    });

    // Init Select2
    $('#customer_id').select2({
      ajax: {
        url: routes.searchCustomers,
        dataType: 'json',
        delay: 250,
        data: function(params) {
          return {
            q: params.term || '', // we read q in controller
            page: params.page || 1
          };
        },
        processResults: function(data) {
          // Supports either: {results:[...]} OR plain array fallback
          return {
            results: data.results || data || []
          };
        },
        cache: true
      },
      placeholder: 'Search by Name, Email or Phone',
      theme: 'bootstrap4',
      minimumInputLength: 0,
      templateResult: formatCustomerResult,
      templateSelection: function(c) {
        return c.display_name || c.text || 'Select Customer';
      },
      escapeMarkup: function(m) {
        return m;
      }
    }).on('select2:select', function(e) {
      let data = e.params.data;

      // Store all customer data in state
      orderState.customerId = data.id;
      orderState.customerName = data.display_name || '';
      orderState.customerEmail = data.email || '';
      orderState.customerPhone = data.phone || '';
      orderState.customerInitials = data.initials || '??';
      orderState.customerType = data.type || '';
      orderState.customerLegalName = data.legal_name || '';
      orderState.customerTaxNumber = data.tax_number || '';
      orderState.customerNotes = data.notes || '';
      orderState.customerAddress = data.address || '';
      orderState.customerPostalCode = data.postal_code || '';
      orderState.customerCountry = data.country_name || '';
      orderState.customerState = data.state_name || '';

      // UI
      $('#customer-search-mode').hide();
      $('#customer-selected-mode').show();

      $('#c-avatar').text(data.initials || '??');
      $('#c-name').text(data.display_name || 'Customer');
      $('#c-email').text(data.email || 'N/A');
      $('#c-phone').text(data.phone || 'N/A');

      // Addresses
      if (data.addresses && data.addresses.length > 0) {
        orderState.billingAddress = data.addresses.find(a => a.type === 'BILLING') || data.addresses[0];
        orderState.shippingAddress = data.addresses.find(a => a.type === 'SHIPPING') || orderState
          .billingAddress;
      } else {
        orderState.billingAddress = {
          contact_name: data.display_name,
          phone: data.phone
        };
        orderState.shippingAddress = orderState.billingAddress;
      }

      updateAddressPreviews();

      // Optional: auto-set payment term from customer
      if (data.payment_term_id) {
        $('#payment_term_id').val(data.payment_term_id).trigger('change');
        orderState.paymentTermName = data.payment_term_name || '';
      }

      $('#step2-tab').removeClass('disabled');
      saveDraft();
    });


    // Product search
    $('#product_search').on('keyup', _.debounce(loadProducts, 300));
    $('#category_filter').on('change', loadProducts);

    // initial product grid
    loadProducts();

    // Delegated click: open variant modal
    $(document).on('click', '.js-open-variant', function() {
      const productId = $(this).data('product-id');
      const productName = _.unescape($(this).data('product-name') || '');
      const productSku = _.unescape($(this).data('product-sku') || '');
      const productImage = _.unescape($(this).data('product-image') || '');
      openVariantModal(productId, productName, productSku, productImage);
    });

    // Delegated click: select variant
    $(document).on('click', '.js-select-variant', function() {
      const encoded = $(this).data('variant');
      try {
        const variant = JSON.parse(decodeURIComponent(encoded));
        hideModal('#variantModal');
        addToCart(variant);
      } catch (e) {
        showToast('error', 'Variant data corrupted');
      }
    });

    // Delegated cart actions
    $(document).on('input change', '.js-qty', function() {
      const index = parseInt($(this).data('index'));
      let qty = parseInt($(this).val());
      if (!qty || qty < 1) qty = 1;

      if (orderState.items[index]) {
        orderState.items[index].qty = qty;
        renderCart();
        saveDraft();
      }
    });

    $(document).on('input change', '.js-discount', function() {
      const index = parseInt($(this).data('index'));
      let discount = parseFloat($(this).val());
      if (!discount || discount < 0) discount = 0;

      if (orderState.items[index]) {
        orderState.items[index].discount = discount;
        renderCart();
        saveDraft();
      }
    });

    $(document).on('click', '.js-remove', function() {
      const index = parseInt($(this).data('index'));
      if (orderState.items[index]) {
        orderState.items.splice(index, 1);
        renderCart();
        saveDraft();
      }
    });

    // Payment term
    $('#payment_term_id').on('change', function() {
      let selected = $(this).find(':selected');
      let days = selected.data('days');

      orderState.paymentTermId = $(this).val() || null;
      orderState.paymentTermName = selected.text() || '';

      if (days !== undefined && days !== null && days !== '') {
        let date = new Date();
        date.setDate(date.getDate() + parseInt(days));
        const due = date.toISOString().split('T')[0];
        $('#due_date').val(due);
        orderState.dueDate = due;
      } else {
        $('#due_date').val('');
        orderState.dueDate = null;
      }

      saveDraft();
    });

    // Notes
    $('#order_notes').on('change', function() {
      saveDraft();
    });
  });

  function toggleProceedButton() {
    const disabled = orderState.items.length === 0;
    const btn = document.querySelector('#step2 button[onclick="nextStep(3)"]');
    if (!btn) return;
    btn.disabled = disabled;
    btn.classList.toggle('disabled', disabled);
    btn.style.opacity = disabled ? '0.6' : '1';
  }


  // Expose functions used by your HTML onclick (if any remain)
  window.nextStep = nextStep;
  window.prevStep = prevStep;
  window.saveOrder = saveOrder;
  window.confirmAndPrint = confirmAndPrint;
  window.downloadInvoicePDF = downloadInvoicePDF;
  window.resetCustomer = resetCustomer;
  window.updatePreview = updatePreview;
</script>
@endpush
