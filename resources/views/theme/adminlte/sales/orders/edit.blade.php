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
      height: 92vh;
      min-height: 550px;
      overflow: hidden;
    }

    .pos-col {
      height: 92vh;
      position: sticky;
      min-height: 92vh;
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
      height: calc(100vh - 170px);
      /* UPDATED: little more header space for top cards */
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

    /* ====== TOP CUSTOMER CARDS (compact) ====== */
    .customer-cards {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    @media (max-width: 992px) {
      .customer-cards {
        grid-template-columns: 1fr;
      }
    }

    .customer-mini-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 10px 12px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
      min-height: 66px;
    }

    .customer-mini-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 4px;
      line-height: 1.1;
    }

    .customer-mini-title .label {
      font-size: 11px;
      color: #6b7280;
      letter-spacing: .02em;
      font-weight: 700;
      text-transform: uppercase;
    }

    .customer-mini-title .chip {
      font-size: 10px;
      font-weight: 700;
      color: #6b7280;
      background: rgba(107, 114, 128, .10);
      border: 1px solid rgba(107, 114, 128, .18);
      padding: 2px 8px;
      border-radius: 999px;
      white-space: nowrap;
    }

    .btn-mini-icon {
      width: 26px;
      height: 26px;
      padding: 0;
      border-radius: 9px;
      border: 1px solid rgba(17, 24, 39, 0.10);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.9);
    }

    .btn-mini-icon i {
      font-size: 12px;
      color: #374151;
    }

    .btn-mini-icon:hover {
      background: #fff;
      border-color: rgba(17, 24, 39, 0.18);
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
    }


    .customer-mini-name {
      font-size: 13px;
      font-weight: 800;
      color: #111827;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 3px;
    }

    .customer-mini-line {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      font-size: 11px;
      color: #6b7280;
      line-height: 1.2;
    }

    .customer-mini-line span {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 220px;
      display: inline-block;
    }

    /* ====== PRODUCT CARD ====== */
    .product-card {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: var(--card);
      height: 200px;
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
      height: 120px;
      background: linear-gradient(135deg, #f0f2f8, #e9edf6);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8px;
      position: relative;
      /* IMPORTANT for badge overlay */
    }

    /* CATEGORY BADGE OVER IMAGE */
    .product-cat-badge {
      position: absolute;
      top: 8px;
      right: 8px; /* Changed from left: 8px */
      z-index: 2;
      font-size: 10px;
      font-weight: 800;
      color: #111827;
      background: rgba(255, 255, 255, 0.88);
      border: 1px solid rgba(17, 24, 39, 0.12);
      padding: 3px 8px;
      border-radius: 999px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
      backdrop-filter: blur(6px);
      max-width: calc(100% - 16px);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
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
      font-weight: 400;
      font-size: 12px;
      color: #111827;
      /* overflow: hidden; */
      /* text-overflow: ellipsis; */
      min-width: 0;
      flex: 1;
      /* Allow wrapping */
      white-space: normal;
      line-height: 1.3;
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

    /* Resizer */
    .pos-resizer {
      /* width: 2px; */
      padding: 0;
      margin: 0;
      background: var(--border);
      cursor: col-resize;
      z-index: 10;
      flex: 0 0 2px;
      transition: background 0.2s;
      position: relative;
      user-select: none;
      touch-action: none;
    }

    .pos-resizer:hover,
    .pos-resizer.dragging {
      background: #cbd5e0;
    }

    .pos-resizer::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      height: 20px;
      width: 2px;
      background: #afb2bd;
      border-radius: 2px;
    }
  </style>

  <div class="container-fluid pos-app">
    <div class="row no-gutters" id="posContainer" style="flex-wrap: nowrap;">
      <!-- LEFT -->
      <div class="col-lg-7 col-xl-8 pos-col" id="posLeftCol" style="min-width: 300px;">
        <div class="px-3 pos-left-header">

          <!-- NEW: SELL TO / BILL TO CARDS -->
          <div class="customer-cards">
            <div class="customer-mini-card">
              <div class="customer-mini-title">
<div class="chip">Sell To</div>
                <div class="d-flex align-items-center" style="gap:6px;">
                  <button type="button" class="btn btn-light btn-mini-icon" id="changeSellToBtn" title="Change Sell To"
                    aria-label="Change Sell To" data-type="sell_to">
                    <i class="fas fa-user-edit"></i>
                  </button>

                  
                </div>
              </div>


              <div class="customer-mini-name" title="{{ $sellToCustomer->legal_name ?? $sellToCustomer->display_name }}">
                {{ $sellToCustomer->legal_name ?? $sellToCustomer->display_name }}
              </div>

              <div class="customer-mini-line">
                @if(!empty($sellToCustomer->phone))
                  <span title="{{ $sellToCustomer->phone }}"><i
                      class="fas fa-phone-alt mr-1"></i>{{ $sellToCustomer->phone }}</span>
                @endif
                @if(!empty($sellToCustomer->email))
                  <span title="{{ $sellToCustomer->email }}"><i
                      class="fas fa-envelope mr-1"></i>{{ $sellToCustomer->email }}</span>
                @endif
              </div>
            </div>

            <div class="customer-mini-card">
              <div class="customer-mini-title">
                  <div class="chip">Bill To</div>

                <div class="d-flex align-items-center" style="gap:6px;">
                  <button type="button" class="btn btn-light btn-mini-icon" id="changeBillToBtn" title="Change Bill To"
                    aria-label="Change Bill To" data-type="bill_to">
                    <i class="fas fa-user-edit"></i>
                  </button>

                </div>
              </div>


              <div class="customer-mini-name" title="{{ $billToCustomer->legal_name ?? $billToCustomer->display_name }}">
                {{ $billToCustomer->legal_name ?? $billToCustomer->display_name }}
              </div>

              <div class="customer-mini-line">
                @if(!empty($billToCustomer->phone))
                  <span title="{{ $billToCustomer->phone }}"><i
                      class="fas fa-phone-alt mr-1"></i>{{ $billToCustomer->phone }}</span>
                @endif
                @if(!empty($billToCustomer->email))
                  <span title="{{ $billToCustomer->email }}"><i
                      class="fas fa-envelope mr-1"></i>{{ $billToCustomer->email }}</span>
                @endif
              </div>
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
          <div id="productsGrid" class="row mt-2"></div>

          <div id="loadMoreIndicator" class="text-center py-4 d-none">
            <div class="spinner-border text-secondary" role="status" style="width: 2rem; height: 2rem;">
              <span class="sr-only">Loading...</span>
            </div>
            <div class="mt-2 small text-muted">Loading more products...</div>
          </div>
        </div>
      </div>

      <!-- RESIZER -->
      <div class="pos-resizer" id="posResizer"></div>

      <!-- RIGHT -->
      <div class="col-lg-5 col-xl-4 pos-col pos-right" id="posRightCol" style="min-width: 350px;">
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
              <textarea id="commentsInput" class="form-control" rows="2"
                placeholder="Notes for this invoice..."></textarea>
            </div>
          </div>

          <div class="row bg-light mt-2">
            <div class="col-6">
              <div class="form-group mb-2">
                <label class="mb-1 small text-muted">Shipping Charges</label>
                <input id="shippingInput" type="number" class="form-control input-xs" value="0" min="0" step="0.01" />
              </div>
            </div>
            <div class="col-6">
              <div class="form-group mb-2">
                <label class="mb-1 small text-muted">Additional Charges</label>
                <input id="additionalInput" type="number" class="form-control input-xs" value="0" min="0" step="0.01" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-6">
              <div class="form-group mb-2">
                <label class="mb-1 small text-muted">VAT %</label>
                <input id="vatRateInput" type="number" class="form-control input-xs" value="5" min="0" step="0.01" />
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

    const SELECTED_PAYMENT_TERM_ID = "{{ $billToCustomer->payment_term_id }}";

    const INITIAL_STATE = @json($initialState ?? null);
    const UPDATE_URL = "{{ isset($order) ? company_route('sales-orders.update', ['sales_order' => $order->uuid]) : '' }}";
    const PRINT_URL = "{{ isset($order) ? company_route('sales-orders.print', ['sales_order' => $order->uuid]) : '' }}";
  </script>

  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="{{ asset('pos/assets/js/pos.js') }}"></script>

  <script>
    /**
     * NOTE: Category badge overlay is rendered in product cards in JS (pos.js).
     * You must add `product-cat-badge` element inside `.product-thumb` when building card HTML.
     *
     * Example in pos.js (where you build product card):
     *   <div class="product-thumb">
     *     <div class="product-cat-badge">${escapeHtml(p.category_name ?? 'Uncategorized')}</div>
     *     ...
     *   </div>
     */

    document.addEventListener('DOMContentLoaded', () => {
      const resizer = document.getElementById('posResizer');
      const leftCol = document.getElementById('posLeftCol');
      const rightCol = document.getElementById('posRightCol');
      const container = document.getElementById('posContainer');

      if (!resizer || !leftCol || !rightCol || !container) return;

      const RESIZER_WIDTH = 2; // IMPORTANT: match .pos-resizer width now (2px)
      const STORAGE_KEY = 'pos_split_left_px';

      const LEFT_MIN = 300;
      const RIGHT_MIN = 350;

      let isResizing = false;
      let rafId = null;
      let latestX = 0;

      function clamp(n, min, max) {
        return Math.max(min, Math.min(max, n));
      }

      function setWidths(leftPx, containerWidth) {
        const rightPx = containerWidth - leftPx - RESIZER_WIDTH;

        leftCol.style.flex = `0 0 ${leftPx}px`;
        leftCol.style.maxWidth = `${leftPx}px`;
        leftCol.style.width = `${leftPx}px`;

        rightCol.style.flex = `0 0 ${rightPx}px`;
        rightCol.style.maxWidth = `${rightPx}px`;
        rightCol.style.width = `${rightPx}px`;
      }

      function applyFromStorage() {
        const saved = parseFloat(localStorage.getItem(STORAGE_KEY));
        if (!Number.isFinite(saved)) return;

        const rect = container.getBoundingClientRect();
        const containerWidth = rect.width;

        const leftMax = containerWidth - RIGHT_MIN - RESIZER_WIDTH;
        const leftPx = clamp(saved, LEFT_MIN, leftMax);

        setWidths(leftPx, containerWidth);
      }

      function startResize(e) {
        if (e.pointerType === 'mouse' && e.button !== 0) return;

        isResizing = true;
        latestX = e.clientX;

        resizer.classList.add('dragging');
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';

        try { resizer.setPointerCapture(e.pointerId); } catch (err) { }

        e.preventDefault();
      }

      function moveResize(e) {
        if (!isResizing) return;

        latestX = e.clientX;

        if (rafId) return;
        rafId = requestAnimationFrame(() => {
          rafId = null;

          const rect = container.getBoundingClientRect();
          const containerLeft = rect.left;
          const containerWidth = rect.width;

          let leftPx = latestX - containerLeft;

          const leftMax = containerWidth - RIGHT_MIN - RESIZER_WIDTH;
          leftPx = clamp(leftPx, LEFT_MIN, leftMax);

          setWidths(leftPx, containerWidth);
          localStorage.setItem(STORAGE_KEY, String(leftPx));
        });
      }

      function endResize(e) {
        if (!isResizing) return;

        isResizing = false;
        resizer.classList.remove('dragging');
        document.body.style.cursor = '';
        document.body.style.userSelect = '';

        try { resizer.releasePointerCapture(e.pointerId); } catch (err) { }
      }

      applyFromStorage();

      resizer.addEventListener('pointerdown', startResize);
      window.addEventListener('pointermove', moveResize);
      window.addEventListener('pointerup', endResize);

      window.addEventListener('resize', () => {
        applyFromStorage();
      });
    });
  </script>
@endpush