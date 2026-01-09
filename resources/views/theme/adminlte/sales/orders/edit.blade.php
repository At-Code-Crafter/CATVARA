@extends('theme.adminlte.layouts.app')

@section('content')
  <style>
    :root {
      --bg: #f6f7fb;
      --card: #ffffff;
      --border: #e6e8ef;
      --muted: #6c757d;
    }

    .pos-app {
      height: 100vh;
      overflow: hidden;
    }

    .pos-col {
      height: 100vh;
      overflow: hidden;
    }

    .pos-left-header {
      position: sticky;
      top: 0;
      z-index: 5;
      background: var(--bg);
      border-bottom: 1px solid var(--border);
      padding: 12px 0;
    }

    .scroll-area {
      height: calc(100vh - 86px);
      overflow: auto;
      padding-bottom: 16px;
    }

    .scroll-area::-webkit-scrollbar {
      width: 10px;
    }

    .scroll-area::-webkit-scrollbar-thumb {
      background: #d6d9e3;
      border-radius: 10px;
    }

    .product-card {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: var(--card);
      height: 170px;
      cursor: pointer;
      transition: transform 120ms ease, box-shadow 120ms ease;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
    }

    /* image in thumb */
    .product-thumb {
      height: 92px;
      background: linear-gradient(135deg, #f0f2f8, #e9edf6);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8px;
    }

    .product-img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      display: block;
      filter: drop-shadow(0 6px 10px rgba(0, 0, 0, 0.08));
    }

    .product-img-placeholder {
      width: 100%;
      height: 100%;
      border: 1px dashed rgba(108, 117, 125, 0.35);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      color: rgba(108, 117, 125, 0.85);
      background: rgba(255, 255, 255, 0.35);
    }

    .product-body {
      padding: 10px 12px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    /* name + category inline */
    .product-title {
      display: flex;
      align-items: baseline;
      gap: 8px;
      margin-bottom: 6px;
      line-height: 1.2;
      white-space: nowrap;
      overflow: hidden;
    }

    .product-name {
      font-weight: 700;
      font-size: 14px;
      color: #111827;
      overflow: hidden;
      text-overflow: ellipsis;
      min-width: 0;
      flex: 1;
    }

    .product-category {
      font-size: 11px;
      font-weight: 600;
      color: #9aa3af;
      background: rgba(148, 163, 184, 0.15);
      border: 1px solid rgba(148, 163, 184, 0.25);
      padding: 2px 8px;
      border-radius: 999px;
      white-space: nowrap;
      flex: 0 0 auto;
    }

    .product-meta {
      margin-top: auto;
      font-size: 12px;
      color: var(--muted);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .badge-soft {
      background: #f1f3f5;
      border: 1px solid var(--border);
      color: #343a40;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 999px;
    }

    /* RIGHT */
    .pos-right {
      background: var(--card);
      border-left: 1px solid var(--border);
      font-size: 12px;
    }

    .pos-right .help {
      font-size: 11px;
      color: var(--muted);
    }

    .pos-right-header {
      border-bottom: 1px solid var(--border);
      padding: 12px 14px;
      background: #fff;
      position: sticky;
      top: 0;
      z-index: 6;
    }

    .pos-right-header h5 {
      font-size: 15px;
      margin-bottom: 4px;
    }

    .cart-scroll {
      height: calc(100vh - 340px);
      overflow: auto;
      padding: 8px 10px;
    }

    .cart-scroll::-webkit-scrollbar {
      width: 10px;
    }

    .cart-scroll::-webkit-scrollbar-thumb {
      background: #d6d9e3;
      border-radius: 10px;
    }

    .cart-footer {
      border-top: 1px solid var(--border);
      padding: 10px 14px;
      background: #fff;
      position: sticky;
      bottom: 0;
      z-index: 6;
    }

    .table thead th {
      border-top: 0;
      border-bottom: 1px solid var(--border) !important;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #6c757d;
      background: #fbfbfd;
      position: sticky;
      top: 0;
      z-index: 2;
      padding-top: 8px;
      padding-bottom: 8px;
    }

    .table td {
      padding-top: 8px;
      padding-bottom: 8px;
      vertical-align: middle;
    }

    .cart-item-name {
      font-weight: 700;
      font-size: 12px;
      margin-bottom: 2px;
    }

    .cart-item-variant {
      font-size: 11px;
      color: var(--muted);
    }

    .input-xs {
      height: 30px;
      padding: 0 8px;
      font-size: 12px;
    }

    .btn-icon {
      width: 30px;
      height: 30px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .form-control,
    .custom-select {
      border-color: var(--border);
    }

    .form-control:focus,
    .custom-select:focus {
      box-shadow: none;
      border-color: #c6c9d6;
    }

    .btn-dark,
    .btn-secondary,
    .btn-outline-secondary {
      border-radius: 10px;
    }

    .variant-card {
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 10px 12px;
      background: #fff;
      cursor: pointer;
      transition: box-shadow 120ms ease, transform 120ms ease;
      height: 92px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .variant-card:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08);
    }

    .variant-card.active {
      border-color: #343a40;
      box-shadow: 0 12px 28px rgba(52, 58, 64, 0.18);
    }

    .variant-attrs {
      font-size: 12px;
      color: #495057;
    }

    .variant-meta {
      font-size: 12px;
      color: var(--muted);
      display: flex;
      justify-content: space-between;
    }
  </style>

  <div class="container-fluid pos-app">
    <div class="row no-gutters">
      <!-- LEFT -->
      <div class="col-lg-7 col-xl-8 pos-col">
        <div class="px-3 pos-left-header">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="help" id="customerBar">
                Sell To: {{ $sellToCustomer->legal_name ?? $sellToCustomer->display_name }}
                | Bill To: {{ $billToCustomer->legal_name ?? $billToCustomer->display_name }}
              </div>
            </div>
            <div>
              <a href="{{ company_route('sales-orders.create') }}" class="btn btn-outline-secondary btn-sm">Change
                Customer</a>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-6 mb-2 mb-md-0">
              <input id="searchInput" type="text" class="form-control" placeholder="Search products..." />
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
              <select id="categoryFilter" class="custom-select">
                <option value="">All Categories</option>
              </select>
            </div>
            <div class="col-md-3">
              <select id="brandFilter" class="custom-select">
                <option value="">All Brands</option>
              </select>
            </div>
          </div>
        </div>

        <div id="productsScroll" class="scroll-area px-3">
          <div id="productsGrid" class="row"></div>

          <div id="loadMoreIndicator" class="text-center py-4 d-none">
            <div class="spinner-border text-secondary" role="status" style="width: 2rem; height: 2rem;">
              <span class="sr-only">Loading...</span>
            </div>
            <div class="mt-2 small text-muted">Loading more products...</div>
          </div>

          <div class="text-center small text-muted pb-3">
            Scroll down to load more (demo).
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="col-lg-5 col-xl-4 pos-col pos-right">
        <div class="pos-right-header">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <h5 class="mb-1">Selected Items</h5>
              <div class="help">
                <span class="mr-3">Invoice Date: <strong id="invoiceDateText">-</strong></span>
                <span>Due Date: <strong id="dueDateText">-</strong></span>
              </div>
            </div>
            <button id="clearCartBtn" class="btn btn-outline-secondary btn-sm">Clear</button>
          </div>

          <div class="row mt-2">
            <div class="col-md-8">
              <label class="mb-1 small text-muted">Payment Terms</label>
              <select id="paymentTermSelect" class="custom-select input-xs">
                <option value="">Loading...</option>
              </select>
            </div>
            <div class="col-md-4 mt-2 mt-md-0">
              <label class="mb-1 small text-muted">Terms Days</label>
              <input id="paymentTermDays" class="form-control input-xs" value="0" readonly />
            </div>
          </div>
        </div>

        <div class="cart-scroll">
          <div class="table-responsive mb-0">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th style="min-width: 220px;">Item</th>
                  <th class="text-right" style="min-width: 85px;">Unit</th>
                  <th class="text-center" style="min-width: 80px;">Qty</th>
                  <th class="text-center" style="min-width: 90px;">Disc %</th>
                  <th class="text-right" style="min-width: 95px;">Total</th>
                  <th class="text-center" style="width: 42px;">&nbsp;</th>
                </tr>
              </thead>
              <tbody id="cartBody">
                <tr id="emptyCartRow">
                  <td colspan="6" class="text-center text-muted py-4">
                    No items selected. Click a product card to add.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>


        </div>

        <div class="cart-footer">
          <div class="row">
            <div class="col-12">
              <label class="mb-1 small text-muted">Additional Comments</label>
              <textarea id="commentsInput" class="form-control" rows="2" placeholder="Notes for this invoice..."></textarea>
            </div>
          </div>
          <div class="row bg-light mt-2">
            <div class="col-6">
              <div class="form-group mb-2">
                <label class="mb-1 small text-muted">Shipping Charges</label>
                <input id="shippingInput" type="number" class="form-control input-xs" value="0" min="0"
                  step="0.01" />
              </div>
            </div>
            <div class="col-6">
              <div class="form-group mb-2">
                <label class="mb-1 small text-muted">Additional Charges</label>
                <input id="additionalInput" type="number" class="form-control input-xs" value="0" min="0"
                  step="0.01" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-6">
              <div class="form-group mb-2">
                <label class="mb-1 small text-muted">VAT %</label>
                <input id="vatRateInput" type="number" class="form-control input-xs" value="5" min="0"
                  step="0.01" />
              </div>
            </div>
            <div class="col-6">
              <div class="form-group mb-2">
                <label class="mb-1 small text-muted">Currency</label>
                <select class="custom-select input-xs" id="currencySelect">
                  <option value="AED" selected>AED</option>
                  <option value="USD">USD</option>
                  <option value="GBP">GBP</option>
                </select>
              </div>
            </div>
          </div>

          <div class="border-top pt-2">
            <div class="d-flex justify-content-between">
              <div class="text-muted">Sub Total</div>
              <div class="font-weight-bold" id="subTotalText">0.00</div>
            </div>
            <div class="d-flex justify-content-between">
              <div class="text-muted">VAT Amount</div>
              <div class="font-weight-bold" id="vatText">0.00</div>
            </div>
            <div class="d-flex justify-content-between">
              <div class="text-muted">Total</div>
              <div class="h5 mb-0" id="grandTotalText">0.00</div>
            </div>

            <div class="row mt-3">
              <div class="col-6">
                <button class="btn btn-outline-secondary btn-block" id="saveDraftBtn">Save Draft</button>
              </div>
              <div class="col-6" id="generateOrderContainer">
                <button class="btn btn-dark btn-block" id="generateOrderBtn">Generate Order</button>
              </div>
              <div class="col-6 d-none" id="postGenerateContainer">
                <div class="dropdown">
                  <button class="btn btn-dark btn-block dropdown-toggle" type="button" data-toggle="dropdown">
                    Actions
                  </button>
                  <div class="dropdown-menu dropdown-menu-right w-100">
                    <a class="dropdown-item" href="#" id="downloadPdfBtn">
                      <i class="fas fa-file-pdf mr-2"></i> Download PDF
                    </a>
                    <a class="dropdown-item" href="#" id="createInvoiceBtn">
                      <i class="fas fa-file-invoice mr-2"></i> Generate Invoice
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Variant Modal -->
  <div class="modal fade" id="variantModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content" style="border-radius: 14px; overflow:hidden;">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-1">Select Variant</h5>
            <div class="small text-muted" id="variantModalSubtitle">-</div>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div id="variantGrid" class="row"></div>
        </div>

        <div class="modal-footer d-flex justify-content-between">
          <div class="small text-muted" id="variantSelectionHint">No variant selected.</div>
          <div>
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-dark" id="addVariantBtn" disabled>Add to Cart</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Invoice Preview Modal (kept for later wiring) -->
  <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content" style="border-radius:14px; overflow:hidden;">
        <div class="modal-header">
          <div>
            <h6 class="mb-0">Invoice Preview</h6>
            <div class="small text-muted" id="invoiceMetaLine">-</div>
          </div>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" id="invoicePreviewBody"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-dark" id="invoicePrintBtn">Print Invoice</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    const CUSTOMERS_URL = "{{ company_route('load-customers') }}";
    const LOAD_PRODUCTS_URL = "{{ company_route('load-products') }}";
    const PAYMENT_TERMS_URL = "{{ company_route('load-payment-terms') }}";

    // default term
    const SELECTED_PAYMENT_TERM_ID = "{{ $billToCustomer->payment_term_id }}";

    // edit hydration + update endpoint
    const INITIAL_STATE = @json($initialState ?? null);
    const UPDATE_URL = "{{ isset($order) ? company_route('sales-orders.update', ['sales_order' => $order->uuid]) : '' }}";
    const PRINT_URL = "{{ isset($order) ? company_route('sales-orders.print', ['sales_order' => $order->uuid]) : '' }}";
  </script>

  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="{{ asset('pos/assets/js/pos.js') }}"></script>
@endpush
