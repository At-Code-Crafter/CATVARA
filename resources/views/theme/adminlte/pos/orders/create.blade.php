@extends('theme.adminlte.layouts.app')

@section('title', 'New Sales Order')

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8">
      <h1 class="m-0">
        <i class="fas fa-file-invoice mr-2 text-primary"></i> New Order
      </h1>
      <div class="text-muted">Create a new order.</div>
    </div>
  </div>
@endsection

@section('content')
  <div class="container-fluid pos-shell">

    <div class="card ent-card">
      <div class="card-header p-0 pt-3 border-bottom-0">
        <ul class="nav nav-tabs pos-steps" id="order-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="step1-tab" data-toggle="pill" data-bs-toggle="pill" href="#step1"
              role="tab" aria-controls="step1" aria-selected="true">
              <i class="fas fa-user mr-2"></i> 1. Customer
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" id="step2-tab" data-toggle="pill" data-bs-toggle="pill" href="#step2"
              role="tab" aria-controls="step2" aria-selected="false">
              <i class="fas fa-cubes mr-2"></i> 2. Products
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" id="step3-tab" data-toggle="pill" data-bs-toggle="pill" href="#step3"
              role="tab" aria-controls="step3" aria-selected="false">
              <i class="fas fa-file-invoice-dollar mr-2"></i> 3. Terms
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" id="step4-tab" data-toggle="pill" data-bs-toggle="pill" href="#step4"
              role="tab" aria-controls="step4" aria-selected="false">
              <i class="fas fa-check-circle mr-2"></i> 4. Preview
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body p-4">
        <div class="tab-content" id="order-tabs-content">

          {{-- STEP 1 --}}
          <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
            <div class="customer-picker">

              <div class="text-center mb-4">
                <h3 class="font-weight-bold mb-1">Select Customer</h3>
                <div class="text-muted">Pick from quick cards or search to continue.</div>
              </div>

              {{-- SEARCH + QUICK FILTER --}}
              <div class="customer-searchbar mb-3">
                <div class="row align-items-end">
                  <div class="col-md-8">
                    <label class="text-muted small font-weight-bold text-uppercase mb-1">Search Customer <small
                        class="text-muted ">
                        (Search by Name, Email, or Phone.)
                      </small></label>
                    <select class="form-control ent-control select2" id="customer_id" style="width: 100%;"></select>
                  </div>

                  <div class="col-md-4">
                    <label class="text-muted small font-weight-bold text-uppercase mb-1">Quick Filter Cards</label>
                    <input type="text" id="customer_card_filter" class="form-control ent-control"
                      placeholder="Type to filter cards...">
                  </div>
                </div>
              </div>

              {{-- QUICK CUSTOMER CARDS --}}
              <div class="customer-grid" id="customer-cards">

              </div>

              {{-- SELECTED MODE --}}
              <div class="mt-4" id="customer-selected-mode" style="display:none;">
                <div class="card ent-card">
                  <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                      <div class="d-flex align-items-center">
                        <div class="customer-card" style="cursor: default; box-shadow:none; border: none; padding:0;">
                          <div class="cust-logo" id="c-avatar">??</div>
                          <div class="cust-meta">
                            <div class="cust-name" id="c-name">Customer</div>
                            <div class="cust-sub">
                              <span id="c-email">—</span> <span class="mx-1">•</span> <span id="c-phone">—</span>
                            </div>
                            <div class="cust-address mt-1" id="c-address">—</div>
                          </div>
                        </div>
                      </div>

                      <div class="mt-3 mt-md-0 text-right">
                        <button type="button" class="btn btn-primary btn-ent" onclick="nextStep(2)">
                          Continue to Products <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-ent ml-2"
                          onclick="resetCustomer()">
                          Change Customer
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          {{-- STEP 2 --}}
          <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
            <div class="row">
              <div class="col-md-8">
                <div class="card ent-card">
                  <div class="card-body">
                    <div class="row mb-3">
                      <div class="col-md-7">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Search Product</label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                          </div>
                          <input type="text" class="form-control ent-control" id="product_search"
                            placeholder="Search by name or SKU...">
                        </div>
                      </div>
                      <div class="col-md-5 mt-3 mt-md-0">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Category</label>
                        <select class="form-control ent-control" id="category_filter">
                          <option value="">All Categories</option>
                          {{-- @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                          @endforeach --}}
                        </select>
                      </div>
                    </div>

                    <div id="product-grid" class="row pos-grid">
                      {{-- Products injected --}}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-4 mt-4 mt-md-0">
                <div class="sticky-cart">
                  <div class="card cart-panel">
                    <div class="cart-header d-flex justify-content-between align-items-center">
                      <div>
                        <div class="font-weight-bold">
                          <i class="fas fa-shopping-basket mr-2 text-warning"></i> Current Order
                        </div>
                        <small class="text-white-50">Draft</small>
                      </div>
                      <span class="badge badge-primary badge-pill px-3 py-2" id="cart-count">0 Items</span>
                    </div>

                    <div class="card-body p-0">
                      <div class="cart-table-wrapper">
                        <table class="table table-hover mb-0">
                          <thead class="bg-light sticky-top" style="z-index: 5;">
                            <tr class="text-secondary small font-weight-bold text-uppercase">
                              <th class="pl-3">Item</th>
                              <th class="text-center" style="width: 70px;">Qty</th>
                              <th class="text-center" style="width: 85px;">Disc</th>
                              <th class="text-right pr-3">Total</th>
                              <th style="width: 30px;"></th>
                            </tr>
                          </thead>
                          <tbody id="cart-items"></tbody>
                        </table>
                      </div>
                    </div>

                    <div class="cart-summary">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="font-weight-bold text-dark" id="cart-subtotal">$0.00</span>
                      </div>
                      <div class="d-flex justify-content-between h4 mb-3 align-items-center">
                        <span class="font-weight-bold text-dark">Total</span>
                        <span class="text-primary font-weight-bolder" style="font-size: 1.35rem;"
                          id="cart-total">$0.00</span>
                      </div>

                      <button type="button" class="btn btn-primary btn-ent btn-block py-3" onclick="nextStep(3)">
                        Proceed to Details <i class="fas fa-arrow-right ml-2"></i>
                      </button>

                      <button type="button" class="btn btn-outline-secondary btn-ent btn-block mt-2"
                        onclick="prevStep(1)">
                        Back to Customer
                      </button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          {{-- STEP 3 + STEP 4 --}}
          {{-- Keep your existing STEP 3 and STEP 4 HTML as-is for now (only class cleanup later). --}}
          {{-- To keep this answer focused, I did not re-paste 1000+ lines. --}}
          {{-- You can paste your Step 3/4 blocks below exactly as you have them. --}}

        </div>
      </div>
    </div>

  </div>


@endsection

@push('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>


  <script>
    $(document).ready(function() {

      $.ajax({
        url: '{{ company_route('load-customers') }}',
        type: 'GET',
        success: function(response) {
          $('#customer-cards').html(response.view);
        }
      })

    });
  </script>
@endpush
