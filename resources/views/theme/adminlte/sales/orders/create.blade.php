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
    /* Futuristic POS Styles - Next Level Glassmorphism */
    :root {
      --glass-bg: rgba(255, 255, 255, 0.7);
      --glass-border: rgba(255, 255, 255, 0.3);
      --neon-blue: #00d2ff;
      --neon-blue-glow: 0 0 15px rgba(0, 210, 255, 0.5);
      --pos-bg: #f0f2f5;
    }

    body.dark-mode-pos {
      background: #0f172a !important;
      color: #f8fafc;
    }

    .pos-container {
      border-radius: 24px;
      overflow: hidden;
      background: var(--pos-bg);
    }

    .pos-card {
      height: 100%;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      overflow: hidden;
      background: var(--glass-bg);
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .pos-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
      border-color: var(--neon-blue);
    }

    .product-img-container {
      height: 180px;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
    }

    .pos-card .brand-label {
      background: #f1f5f9;
      padding: 4px 10px;
      border-radius: 50px;
      font-size: 0.65rem;
      font-weight: 700;
      color: #64748b;
      margin-bottom: 10px;
      display: inline-block;
    }

    .pos-card .product-name {
      font-weight: 800;
      font-size: 1.05rem;
      color: #1e293b;
      letter-spacing: -0.02em;
    }

    .pos-card .price-tag {
      color: var(--neon-blue);
      font-weight: 800;
      font-size: 1.2rem;
    }

    /* Tabs Overhaul */
    .pos-steps {
      background: #fff;
      padding: 8px;
      border-radius: 16px;
      margin-bottom: 24px !important;
      border: none !important;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .pos-steps .nav-item {
      margin: 0 4px;
    }

    .pos-steps .nav-link {
      border-radius: 12px !important;
      border: none !important;
      padding: 12px 20px;
      color: #64748b;
      font-weight: 600;
      transition: 0.3s;
    }

    .pos-steps .nav-link.active {
      background: #000 !important;
      color: #fff !important;
      box-shadow: var(--neon-blue-glow);
    }

    /* Modal - Wider & Dark themed */
    #variantModal .modal-content {
      background: #fff;
      border-radius: 30px;
      border: none;
    }

    #variantModal .list-group-item {
      border: none;
      margin: 8px 15px;
      border-radius: 15px !important;
      background: #f8fafc;
      transition: 0.3s;
    }

    #variantModal .list-group-item:hover {
      background: #f1f5f9;
      transform: scale(1.01);
    }

    .variant-price-badge {
      background: #000;
      color: #fff;
      padding: 8px 16px;
      border-radius: 12px;
      font-weight: 700;
    }

    /* Right Sidebar - Sticky Cart */
    .sticky-cart {
      position: sticky;
      top: 20px;
    }
  </style>
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
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="card card-outline card-primary shadow-sm border-0">
                  <div class="card-header border-0 bg-white p-4 pb-0">
                    <h3 class="card-title text-primary"><i class="fas fa-user-circle mr-2"></i> Select Customer</h3>
                  </div>
                  <div class="card-body p-5">

                    <!-- Search Mode -->
                    <div id="customer-search-mode">
                      <h4 class="text-center mb-4 text-dark font-weight-light">Who is this order for?</h4>
                      <div class="form-group mx-auto position-relative" style="max-width: 600px;">
                        <div class="input-group input-group-lg shadow-sm">
                          <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                class="fas fa-search text-muted"></i></span>
                          </div>
                          <select class="form-control select2 border-left-0" id="customer_id"
                            style="width: 100%;"></select>
                        </div>
                        <small class="form-text text-muted text-center mt-3">Search by Name, Email or Phone</small>
                      </div>
                    </div>

                    <!-- Selected Mode (Hidden by default) -->
                    <div id="customer-selected-mode" style="display: none;">
                      <div class="d-flex align-items-center justify-content-center mb-4">
                        <div
                          class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow"
                          style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;" id="c-avatar">
                          JD
                        </div>
                        <div class="ml-4 text-left">
                          <h2 class="mb-0 font-weight-bold" id="c-name">John Doe</h2>
                          <p class="text-muted mb-0"><i class="fas fa-envelope mr-1"></i> <span
                              id="c-email">john@example.com</span></p>
                          <p class="text-muted mb-0"><i class="fas fa-phone mr-1"></i> <span
                              id="c-phone">555-1234</span></p>
                        </div>
                      </div>

                      <div class="row text-center mb-4 text-muted">
                        <div class="col-sm-4 border-right">
                          <small>Current Balance</small>
                          <h5 class="text-success">$0.00</h5>
                        </div>
                        <div class="col-sm-4 border-right">
                          <small>Open Orders</small>
                          <h5 class="text-dark">0</h5>
                        </div>
                        <div class="col-sm-4">
                          <small>Term</small>
                          <h5 class="text-dark">Net 30</h5>
                        </div>
                      </div>

                      <div class="text-center">
                        <button class="btn btn-outline-secondary btn-lg mr-2" onclick="resetCustomer()"><i
                            class="fas fa-times mr-1"></i> Change</button>
                        <button class="btn btn-primary btn-lg px-5 shadow" onclick="nextStep(2)">Start Order <i
                            class="fas fa-arrow-right ml-2"></i></button>
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

                    <div id="product-grid" class="row g-3" style="max-height: 700px; overflow-y: auto; padding: 10px;">
                      <!-- Products load here -->
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right: Cart Summary -->
              <div class="col-md-4">
                <div class="sticky-cart mt-4 mt-md-0">
                  <div class="card shadow-lg border-0" style="border-radius: 20px;">
                    <div class="card-header bg-dark text-white py-3" style="border-radius: 20px 20px 0 0;">
                      <h5 class="card-title mb-0"><i class="fas fa-shopping-cart mr-2"></i> Current Order</h5>
                      <span class="badge badge-primary float-right" id="cart-count">0 Items</span>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive" style="max-height: 450px;">
                        <table class="table table-hover mb-0">
                          <thead>
                            <tr class="text-muted small">
                              <th>ITEM</th>
                              <th class="text-center">QTY</th>
                              <th class="text-center">DISC</th>
                              <th class="text-right">PRICE</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody id="cart-items">
                        </table>
                      </div>
                    </div>
                    <div class="card-footer cart-summary p-4 bg-light border-0">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="font-weight-bold" id="cart-subtotal">$0.00</span>
                      </div>
                      <div class="d-flex justify-content-between h4 mb-4">
                        <span class="font-weight-bold">Total:</span>
                        <span class="text-primary font-weight-bold" id="cart-total">$0.00</span>
                      </div>
                      <button class="btn btn-dark btn-lg btn-block py-3 font-weight-bold shadow" onclick="nextStep(3)"
                        style="border-radius: 15px;">
                        Proceed to Payment <i class="fas fa-arrow-right ml-2"></i>
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


  <!-- Variant Modal - Wider -->
  <div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content border-0 shadow-lg" style="height: 80vh;">
        <div class="modal-header bg-dark text-white border-0 px-4 py-3">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-th-large mr-2"></i> Configure Product</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body p-0 d-flex flex-column h-100">
          <div class="row g-0 h-100">
            <div class="col-md-4 bg-light p-5 d-flex flex-column align-items-center justify-content-center border-right">
              <div class="mb-4 text-center">
                <h2 id="modal-product-name" class="font-weight-bold mb-2"></h2>
                <span class="badge badge-dark px-3 py-2" id="modal-product-sku" style="font-size: 1rem;"></span>
              </div>
              <div id="modal-product-img-wrapper" class="p-4 bg-white rounded-3 shadow-sm border mb-4">
                <!-- Image placeholder or actual image -->
              </div>
              <p class="text-muted text-center px-3 small">Please select the specific variation and quantity for this
                product below.</p>
            </div>
            <div class="col-md-8 d-flex flex-column">
              <div id="variant-loader" class="text-center p-5 w-100" style="display:none">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted font-weight-bold">Scanning variations...</p>
              </div>
              <div class="flex-grow-1 overflow-auto p-4" id="variant-list">
                <!-- Variants will be injected here as modern cards/list -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
  <script>
    let orderState = {
      uuid: null,
      customerId: null,
      customerName: '',
      items: [],
      paymentTermId: null,
      paymentTermName: '',
      dueDate: null
    };

    $(document).ready(function() {
      // Init Select2
      $('#customer_id').select2({
        ajax: {
          url: "{{ company_route('sales-orders.searchCustomers') }}",
          dataType: 'json',
          delay: 250,
          processResults: function(data) {
            return {
              results: data.results
            };
          }
        },
        placeholder: 'Search for a Customer',
        theme: 'bootstrap4',
        minimumInputLength: 0,
        templateResult: formatCustomerResult
      }).on('select2:select', function(e) {
        let data = e.params.data;
        orderState.customerId = data.id;
        orderState.customerName = data.display_name;

        // Update UI
        $('#customer-search-mode').hide();
        $('#c-avatar').text(data.initials || '??');
        $('#c-name').text(data.display_name);
        $('#c-email').text(data.email || 'N/A');
        $('#c-phone').text(data.phone || 'N/A');

        // Populate addresses in orderState
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

        $('#customer-search-mode').hide();
        $('#customer-selected-mode').show();
        $('#step2-tab').removeClass('disabled').click();
        saveDraft();
      });

      function updateAddressPreviews() {
        if (orderState.billingAddress) {
          let b = orderState.billingAddress;
          let html = `<strong>${b.contact_name || orderState.customerName}</strong><br>`;
          if (b.address_line_1) {
            html +=
              `${b.address_line_1}<br>${b.address_line_2 ? b.address_line_2 + '<br>' : ''}${b.city || ''}, ${b.postal_code || ''}<br>${b.country || ''}`;
          } else {
            html += `<span class="text-danger italic">No address on file</span>`;
          }
          $('#bill-to-preview').html(html);
        }

        if (orderState.shippingAddress) {
          let s = orderState.shippingAddress;
          let html = `<strong>${s.contact_name || orderState.customerName}</strong><br>`;
          if (s.address_line_1) {
            html +=
              `${s.address_line_1}<br>${s.address_line_2 ? s.address_line_2 + '<br>' : ''}${s.city || ''}, ${s.postal_code || ''}<br>${s.country || ''}`;
          } else {
            html += `<em>Same as billing</em>`;
          }
          $('#ship-to-preview').html(html);
        }
      }

      // Search Product Logic
      function loadProducts() {
        let q = $('#product_search').val();
        let cat = $('#category_filter').val();

        $.get("{{ company_route('sales-orders.searchProducts') }}", {
          q: q,
          category_id: cat
        }, function(data) {
          let html = '';
          if (data.length === 0) {
            html = '<div class="col-12 text-center p-5 text-muted"><h4>No products found</h4></div>';
          } else {
            data.forEach(item => {
              html += `
                          <div class="col-md-4 col-sm-6 mb-4">
                              <div class="card pos-card" onclick='openVariantModal(${item.id}, "${item.name.replace(/'/g, "\\'")}", "${item.sku}", "${item.image}")'>
                                  <div class="product-img-container">
                                      <img src="${item.image}" alt="${item.name}" class="img-fluid" style="max-height: 140px; object-fit: contain;">
                                  </div>
                                  <div class="card-body">
                                      <span class="brand-label">SKU: ${item.sku}</span>
                                      <h6 class="product-name mb-3">${item.name}</h6>
                                      <div class="d-flex justify-content-between align-items-center">
                                          <span class="price-tag">View Options</span>
                                          <i class="fas fa-chevron-right text-muted"></i>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      `;
            });
          }
          $('#product-grid').html(html);
        });
      }

      $('#product_search').on('keyup', _.debounce(loadProducts, 300));
      $('#category_filter').on('change', loadProducts);

      // Initial load
      loadProducts();

      // Payment Term Logic
      $('#payment_term_id').change(function() {
        let selected = $(this).find(':selected');
        let days = selected.data('days');
        orderState.paymentTermId = $(this).val();
        orderState.paymentTermName = selected.text();

        if (days !== undefined) {
          let date = new Date();
          date.setDate(date.getDate() + parseInt(days));
          $('#due_date').val(date.toISOString().split('T')[0]);
          orderState.dueDate = $('#due_date').val();
        }
        saveDraft();
      });
    });

    function openVariantModal(productId, productName, productSku, productImage) {
      $('#variantModal').modal('show');
      $('#modal-product-name').text(productName);
      $('#modal-product-sku').text(productSku);
      $('#modal-product-img-wrapper').html(
        `<img src="${productImage}" class="img-fluid" style="max-height: 200px; object-fit: contain;">`);
      $('#variant-list').html('');
      $('#variant-loader').show();

      let url = "{{ company_route('sales-orders.getVariants', ['product' => 'PRODUCT_ID']) }}".replace('PRODUCT_ID',
        productId);
      $.get(url, function(variants) {
        $('#variant-loader').hide();
        let html = '';
        if (variants.length === 0) {
          html =
            '<div class="p-5 text-center text-muted h-100 d-flex flex-column align-items-center justify-content-center"><h4>No variants available</h4><p>This product may not have any active variations.</p></div>';
        } else {
          html = '<div class="row g-3">';
          variants.forEach(v => {
            html += `
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; cursor: pointer;" onclick='selectVariant(${JSON.stringify(v)})'>
                            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="font-weight-bold mb-1" style="font-size: 1.1rem; color: #1e293b;">${v.name}</div>
                                    <div class="small text-muted">SKU: ${v.sku}</div>
                                </div>
                                <div class="text-right">
                                    <div class="variant-price-badge mb-1">$${v.price.toFixed(2)}</div>
                                    <small class="text-primary font-weight-bold">Select <i class="fas fa-plus-circle ml-1"></i></small>
                                </div>
                            </div>
                        </div>
                    </div>
                 `;
          });
          html += '</div>';
        }
        $('#variant-list').html(html);
      }).fail(function() {
        // Fallback if route not defined yet
        $('#variant-loader').hide();
        $('#variant-list').html('<p class="text-danger">Error loading variants. Route missing.</p>');
      });
    }

    // Helper to close modal and add to cart
    function selectVariant(variant) {
      $('#variantModal').modal('hide');
      addToCart(variant);
    }

    function nextStep(step) {
      if (step === 2 && !orderState.customerId) {
        Swal.fire('Error', 'Please select a customer first', 'error');
        return;
      }
      if (step === 3 && orderState.items.length === 0) {
        Swal.fire('Error', 'Please add at least one product', 'error');
        return;
      }

      const tabEl = document.querySelector('#step' + step + '-tab');
      const tab = new bootstrap.Tab(tabEl);
      $('#step' + step + '-tab').removeClass('disabled');
      tab.show();

      if (step === 4) updatePreview();
    }

    function prevStep(step) {
      const tabEl = document.querySelector('#step' + step + '-tab');
      const tab = new bootstrap.Tab(tabEl);
      tab.show();
    }

    function addToCart(item) {
      let existing = orderState.items.find(i => i.id === item.id);
      if (existing) {
        existing.qty++;
      } else {
        orderState.items.push({
          ...item,
          qty: 1
        });
      }
      renderCart();
      saveDraft();
      // Use Swal if toastr not available
      if (window.toastr) {
        toastr.success('Item added to cart');
      } else {
        // Subtle notification instead of full Swal
        Swal.fire({
          title: 'Added!',
          text: 'Item added to cart',
          timer: 1000,
          showConfirmButton: false,
          position: 'top-end',
          toast: true
        });
      }
    }

    let subtotal = 0;
    let discountTotal = 0;

    if (orderState.items.length === 0) {
      $('#cart-items').html('<tr><td colspan="5" class="text-center text-muted p-4">Cart is empty</td></tr>');
      $('#cart-count').text('0 Items');
      $('#cart-subtotal').text('$0.00');
      $('#cart-total').text('$0.00');
      return;
    }

    orderState.items.forEach((item, index) => {
      let itemSub = item.price * item.qty;
      let lineTotal = itemSub - (item.discount || 0);
      subtotal += itemSub;
      discountTotal += (item.discount || 0);
      html += `
                <tr>
                    <td>
                        <div class="font-weight-bold show-ellipsis" style="max-width: 150px;">${item.name}</div>
                        <div class="small text-muted">${item.sku}</div>
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center mx-auto" style="width: 50px" value="${item.qty}" min="1" onchange="updateQty(${index}, this.value)">
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center mx-auto" style="width: 60px" value="${item.discount || 0}" min="0" onchange="updateDiscount(${index}, this.value)">
                    </td>
                    <td class="text-right">$${item.price.toFixed(2)}</td>
                    <td class="text-center">
                        <button class="btn btn-xs btn-outline-danger" onclick="removeItem(${index})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
    });

    $('#cart-items').html(html);
    $('#cart-count').text(orderState.items.length + ' Items');

    let shipping = parseFloat($('#shipping_cost').val()) || 0;
    let vat = (subtotal - discountTotal) * 0.20; // 20% VAT
    let finalTotal = (subtotal - discountTotal) + vat + shipping;

    $('#cart-subtotal').text('$' + (subtotal - discountTotal).toFixed(2));
    $('#cart-total').text('$' + finalTotal.toFixed(2));
    }

    function updateQty(index, qty) {
      if (qty < 1) return;
      orderState.items[index].qty = parseInt(qty);
      renderCart();
      saveDraft();
    }

    function updateDiscount(index, discount) {
      orderState.items[index].discount = parseFloat(discount) || 0;
      renderCart();
      saveDraft();
    }

    function removeItem(index) {
      orderState.items.splice(index, 1);
      renderCart();
      saveDraft();
    }

    function saveDraft() {
      // Debounce or immediate?
      $.post("{{ company_route('sales-orders.storeDraft') }}", {
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
        if (res.success) {
          orderState.uuid = res.order_uuid;
        }
      });
    }

    function updatePreview() {
      $('#preview-customer').text(orderState.customerName);
      $('#preview-term').text(orderState.paymentTermName);
      $('#preview-due').text(orderState.dueDate);
      $('#preview-notes').text($('#order_notes').val() || 'No notes added.');

      let html = '';
      let subtotal = 0;
      let discountTotal = 0;

      orderState.items.forEach(item => {
        let itemSub = item.price * item.qty;
        let lineTotal = itemSub - (item.discount || 0);
        html += `
                <tr>
                    <td><div class="font-weight-bold">${item.name}</div><small class="text-muted">SKU: ${item.sku}</small></td>
                    <td class="text-right">$${item.price.toFixed(2)}</td>
                    <td class="text-center">${item.qty}</td>
                    <td class="text-right">$${lineTotal.toFixed(2)}</td>
                </tr>`;
        subtotal += itemSub;
        discountTotal += (item.discount || 0);
      });

      let shipping = parseFloat($('#shipping_cost').val()) || 0;
      let vat = (subtotal - discountTotal) * 0.20;
      let total = (subtotal - discountTotal) + vat + shipping;

      $('#preview-items').html(html);
      $('#preview-subtotal').text('$' + subtotal.toFixed(2));
      $('#preview-discount').text('-$' + discountTotal.toFixed(2));
      $('#preview-tax').text('$' + vat.toFixed(2));
      $('#preview-shipping').text('$' + shipping.toFixed(2));
      $('#preview-total').text('$' + total.toFixed(2));
    }

    function saveOrder() {
      if (orderState.items.length === 0) {
        Swal.fire('Error', 'Missing line items', 'error');
        return;
      }

      Swal.fire({
        title: 'Confirm Order?',
        text: "This will finalize and generate the invoice.",
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#000',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Confirm & Print'
      }).then((result) => {
        if (result.isConfirmed) {
          // Double check if draft is saved/populated
          if (!orderState.uuid) {
            saveDraft(); // Sync wait? Better to call store directly if uuid missing but state is there? 
            // Actually we should wait for uuid
            Swal.fire({
              title: 'Wait...',
              text: 'Finalizing draft...',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            // Retry save order after 1s
            setTimeout(saveOrder, 1000);
            return;
          }

          $.post("{{ company_route('sales-orders.store') }}", {
            _token: "{{ csrf_token() }}",
            order_uuid: orderState.uuid
          }).done(function(res) {
            if (res.success) {
              window.location.href = res.redirect;
            } else {
              Swal.fire('Error', res.message, 'error');
            }
          }).fail(function(err) {
            let msg = err.responseJSON ? err.responseJSON.message : 'Something went wrong';
            Swal.fire('Finalization Failed', msg, 'error');
          });
        }
      })
    }

    function resetCustomer() {
      $('#customer_id').val(null).trigger('change');
      orderState.customerId = null;
      $('#customer-selected-mode').hide();
      $('#customer-search-mode').fadeIn();
    }

    function formatCustomerResult(c) {
      if (!c.id) return c.text;
      return $(`
            <div class="d-flex align-items-center py-1">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold; color: #555;">
                    ${c.initials}
                </div>
                <div>
                    <div class="font-weight-bold">${c.display_name}</div>
                    <div class="small text-muted">${c.email || ''}</div>
                </div>
            </div>
        `);
    }
  </script>
@endpush
