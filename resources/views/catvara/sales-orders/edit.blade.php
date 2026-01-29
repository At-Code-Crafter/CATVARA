@extends('catvara.layouts.app')

@section('title', 'Sales Order: ' . $order->order_number)

@section('content')
  <style>
    @keyframes fadeInSlide {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-entry {
      animation: fadeInSlide 0.35s ease-out forwards;
      opacity: 0;
    }

    .pos-screen-container {
      flex: 1;
      height: 100%;
      overflow: hidden;
    }

    .scrollable-content {
      flex: 1;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #e2e8f0 transparent;
    }

    .scrollable-content::-webkit-scrollbar {
      width: 4px;
    }

    .scrollable-content::-webkit-scrollbar-track {
      background: transparent;
    }

    .scrollable-content::-webkit-scrollbar-thumb {
      background-color: #e2e8f0;
      border-radius: 10px;
    }

    .cart-item-selected {
      border-color: #6366f1 !important;
      background-color: #f5f3ff !important;
      box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
      transform: translateX(4px);
    }

    .cart-item-selected .item-name {
      color: #4f46e5 !important;
    }
  </style>

  @php
    $billAddress = implode(
        ', ',
        array_filter([
            $billToCustomer->address_line_1 ?? ($billToCustomer->address1 ?? null),
            $billToCustomer->address_line_2 ?? ($billToCustomer->address2 ?? null),
            $billToCustomer->city ?? null,
            $billToCustomer->state ?? null,
            $billToCustomer->postal_code ?? ($billToCustomer->zip ?? null),
            $billToCustomer->country ?? null,
        ]),
    );

    $sellAddress = implode(
        ', ',
        array_filter([
            $shipToCustomer->address_line_1 ?? ($shipToCustomer->address1 ?? null),
            $shipToCustomer->address_line_2 ?? ($shipToCustomer->address2 ?? null),
            $shipToCustomer->city ?? null,
            $shipToCustomer->state ?? null,
            $shipToCustomer->postal_code ?? ($shipToCustomer->zip ?? null),
            $shipToCustomer->country ?? null,
        ]),
    );
  @endphp

  <div class="w-full px-3 sm:px-4 pb-0 h-[100svh] flex flex-col overflow-hidden">

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <a href="{{ company_route('sales-orders.index') }}"
            class="h-7 w-7 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-brand-600 hover:border-brand-200 hover:shadow-sm transition-all duration-300">
            <i class="fas fa-arrow-left text-xs"></i>
          </a>
          <span
            class="px-2 py-0.5 rounded-[4px] bg-brand-50 text-brand-700 border border-brand-100 text-[10px] font-black uppercase tracking-widest">
            Step 02 / 03
          </span>
        </div>
      </div>

      {{-- Progress --}}
      <div class="hidden md:flex items-center gap-3 bg-white px-4 py-2.5 rounded-xl shadow-sm border border-slate-100">
        <button type="button" onclick="openCustomItemModal()"
          class="h-10 px-3 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition flex items-center gap-2 shrink-0">
          <i class="fas fa-plus text-[10px]"></i>
          <span class="hidden sm:inline">Custom Item</span>
          <span class="sm:hidden">Custom</span>
        </button>

        <div class="flex items-center gap-2">
          <div
            class="w-6 h-6 rounded bg-brand-600 text-white flex items-center justify-center font-black text-[10px] shadow-lg shadow-brand-500/20 ring-2 ring-brand-100">
            01
          </div>
          <span class="text-xs font-bold text-brand-700 uppercase tracking-wide">Customer</span>
        </div>

        <div class="w-8 h-0.5 bg-brand-100"></div>

        <div class="flex items-center gap-2">
          <div
            class="w-6 h-6 rounded bg-brand-600 text-white flex items-center justify-center font-black text-[10px] shadow-lg shadow-brand-500/20 ring-2 ring-brand-100">
            02
          </div>
          <span class="text-xs font-bold text-brand-700 uppercase tracking-wide">Basket</span>
        </div>

        <div class="w-8 h-0.5 bg-slate-100"></div>

        <div class="flex items-center gap-2 opacity-40 grayscale">
          <div
            class="w-6 h-6 rounded bg-slate-100 text-slate-400 flex items-center justify-center font-black text-[10px]">03
          </div>
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Finalize</span>
        </div>
      </div>
    </div>

    <div class="pos-screen-container">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 h-full items-stretch">

        {{-- LEFT: Product side --}}
        <div
          class="lg:col-span-6 flex flex-col min-h-0 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

          {{-- Controls --}}
          <div class="shrink-0 z-10 bg-white border-b border-slate-100 p-2 space-y-2">

            {{-- Customer chips --}}
            <div class="flex items-center gap-2 w-full overflow-x-auto no-scrollbar">
              <div
                class="flex items-center gap-2 px-2 py-1.5 rounded-xl border border-slate-200 bg-slate-50/50 shrink-0 max-w-[75%]">
                <div
                  class="w-6 h-6 rounded bg-brand-100 text-brand-700 flex items-center justify-center text-[10px] font-black shrink-0">
                  <i class="fas fa-receipt text-[10px]"></i>
                </div>
                <div class="min-w-0">
                  <div class="text-[10px] font-black text-slate-800 truncate">
                    {{ $order->customer_name ?? $billToCustomer->display_name }}</div>
                  <div class="text-[9px] font-bold text-slate-500 truncate">{{ $billAddress ?: '-' }}</div>
                </div>
              </div>

              <div
                class="flex items-center gap-2 px-2 py-1.5 rounded-xl border border-slate-200 bg-slate-50/50 shrink-0 max-w-[75%]">
                <div
                  class="w-6 h-6 rounded bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-black shrink-0">
                  <i class="fas fa-user-tag text-[10px]"></i>
                </div>
                <div class="min-w-0">
                  <div class="text-[10px] font-black text-slate-800 truncate">
                    {{ $order->shipping_customer_name ?? $shipToCustomer->display_name }}</div>
                  <div class="text-[9px] font-bold text-slate-500 truncate">{{ $sellAddress ?: '-' }}</div>
                </div>
              </div>

              <a href="{{ company_route('sales-orders.create', ['edit_order' => $order->uuid]) }}"
                class="ml-1 w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-brand-600 hover:border-brand-300 transition-colors shadow-sm shrink-0">
                <i class="fas fa-edit text-[12px]"></i>
              </a>
            </div>

            {{-- Notes + Payment Terms + Due Date --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <div class="sm:col-span-1">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Notes</label>
                <textarea id="commentsInput" rows="1"
                  class="w-full rounded-xl border border-slate-200 text-[11px] font-bold focus:border-brand-400 focus:ring-0 resize-none placeholder:text-slate-400 px-2 py-2 bg-white shadow-inner h-10 focus:h-20 transition-all duration-200"
                  placeholder="Internal notes..."></textarea>
              </div>

              <div class="sm:col-span-1">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Payment
                  Terms</label>
                <select id="paymentTermSelect"
                  class="w-full h-10 rounded-xl border border-slate-200 text-[11px] font-black text-slate-700 focus:ring-0 bg-white">
                  <option value="">Select...</option>
                </select>
              </div>

              <div class="sm:col-span-1">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Due Date</label>
                <input id="paymentDueDate" type="text"
                  class="w-full h-10 rounded-xl border border-slate-200 text-[11px] font-black bg-white text-slate-600 text-center"
                  readonly placeholder="-" />
              </div>
            </div>

            {{-- Payment Method --}}
            <div id="paymentMethodWrapper" class="hidden">
              <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Payment
                Method</label>
              <select id="paymentMethodSelect"
                class="w-full h-10 rounded-xl border border-slate-200 text-[11px] font-black text-slate-700 focus:ring-0 bg-white">
                <option value="">Select...</option>
              </select>
            </div>

            {{-- Search + Category --}}
            <div class="flex items-center gap-2">
              <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px]"></i>
                <input type="text" id="productSearch"
                  class="w-full pl-9 h-10 rounded-xl border border-slate-200 text-[11px] font-black focus:border-brand-400 focus:ring-0 placeholder:text-slate-400 bg-slate-50/30"
                  placeholder="Scan SKU or Search...">
              </div>

              <div class="w-40 shrink-0 hidden sm:block">
                <select id="categoryFilter"
                  class="w-full h-10 rounded-xl border border-slate-200 text-[10px] font-black text-slate-600 focus:border-brand-400 focus:ring-0 bg-slate-50/30">
                  <option value="">All Categories</option>
                </select>
              </div>
            </div>

            {{-- Mobile category filter --}}
            <div class="sm:hidden">
              <select id="categoryFilterMobile"
                class="w-full h-10 rounded-xl border border-slate-200 text-[10px] font-black text-slate-600 focus:border-brand-400 focus:ring-0 bg-slate-50/30">
                <option value="">All Categories</option>
              </select>
            </div>
          </div>

          {{-- Product Grid --}}
          <div class="scrollable-content min-h-0 flex-1 p-2 bg-slate-50/50">
            <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-2 pb-2">
              {{-- injected --}}
            </div>
          </div>
        </div>

        {{-- RIGHT: Basket side --}}
        <div
          class="lg:col-span-6 flex flex-col min-h-0 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

          {{-- Cart Header --}}
          <div class="p-4 border-b border-slate-100 bg-slate-50/20 shrink-0 flex items-center justify-between">
            <span
              class="text-[10px] font-black text-slate-400 bg-white px-2 py-0.5 rounded border border-slate-100 shadow-sm"
              id="itemCountLabel">0 Lines • 0 Qty</span>
          </div>

          {{-- Global Discount --}}
          <div class="px-4 py-2.5 bg-brand-50/30 border-b border-slate-100 shrink-0">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" id="applyCustomerDiscount"
                class="form-checkbox w-3.5 h-3.5 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
              <span class="text-[10px] font-black text-slate-600 uppercase">
                Apply Standard Discount ({{ $customerDiscount }}%)
              </span>
            </label>
          </div>

          {{-- Basket Items --}}
          <div class="scrollable-content min-h-0 flex-1 p-2 space-y-1 bg-white" id="cartItemsContainer"></div>

          {{-- Selected Item Editor --}}
          <div class="shrink-0 border-t border-slate-100 overflow-hidden" id="posActionPanel">
            <div class="px-3 py-2 bg-slate-800 text-white flex items-center justify-between gap-2">
              <div class="flex items-center gap-2 min-w-0">
                <span class="text-[9px] font-black uppercase tracking-widest whitespace-nowrap">Selected</span>
                <span id="selectedItemLabel" class="text-[9px] font-bold text-slate-400 italic truncate">No
                  Selection</span>
              </div>

              <button type="button" onclick="posRemoveItem()" id="removeSelectedBtn"
                class="h-7 px-2 rounded-lg bg-red-500/10 text-red-200 border border-red-500/20 text-[9px] font-black uppercase tracking-widest
                       flex items-center gap-2 transition-all duration-200
                       opacity-0 translate-x-3 pointer-events-none">
                <i class="fas fa-trash text-[9px]"></i> Remove
              </button>
            </div>

            <div id="actionControls" class="p-3 opacity-40 grayscale pointer-events-none transition-all duration-300">
              <div class="flex flex-wrap items-center gap-2">
                {{-- Qty --}}
                <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-1.5 py-1">
                  <button type="button" onclick="posUpdateQty(-1)"
                    class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center hover:bg-slate-200 active:scale-95 transition-all text-slate-700">
                    <i class="fas fa-minus text-[10px]"></i>
                  </button>

                  <div
                    class="min-w-[56px] h-8 px-2 rounded-lg bg-slate-50 border border-slate-200 shadow-inner flex items-center justify-center">
                    <span class="text-[11px] font-black text-slate-800" id="posQtyDisplay">1</span>
                  </div>

                  <button type="button" onclick="posUpdateQty(1)"
                    class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center hover:bg-slate-200 active:scale-95 transition-all text-slate-700">
                    <i class="fas fa-plus text-[10px]"></i>
                  </button>
                </div>

                {{-- Price override --}}
                <button type="button" onclick="posPromptPrice()"
                  class="h-10 px-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 transition-all
                         font-black text-[11px] flex items-center gap-2">
                  <i class="fas fa-tag text-[10px]"></i>
                  <span id="posPriceDisplay">0.00</span>
                </button>

                {{-- Quick discounts --}}
                <button type="button" onclick="posApplyDisc(5)"
                  class="h-10 px-3 rounded-xl bg-white border border-slate-200 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 transition-all text-[11px] font-black">
                  5%
                </button>

                <button type="button" onclick="posApplyDisc(10)"
                  class="h-10 px-3 rounded-xl bg-white border border-slate-200 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 transition-all text-[11px] font-black">
                  10%
                </button>

                {{-- Custom discount --}}
                <button type="button" onclick="posPromptDisc()"
                  class="h-10 px-3 rounded-xl border border-dashed border-slate-300 bg-white hover:bg-slate-50 transition-all
                         text-[10px] font-black text-slate-600 uppercase tracking-wider">
                  Custom %
                </button>
              </div>
            </div>
          </div>

          {{-- Totals --}}
          <div class="p-2 border-t border-slate-100 bg-slate-50/40 shrink-0 space-y-2">
            <div class="grid grid-cols-3 gap-3 bg-white p-2 rounded-xl border border-slate-200 shadow-sm mb-3">
              <div>
                <label class="flex items-center gap-1 text-[9px] font-black text-slate-400 uppercase mb-1">
                  <i class="fas fa-truck text-[9px]"></i> Shipping
                </label>
                <input type="number" id="shippingInput"
                  class="w-full h-9 rounded-lg border-slate-200 text-[11px] font-bold text-center bg-slate-50 focus:bg-white focus:ring-brand-500"
                  value="0">
              </div>
              <div>
                <label class="flex items-center gap-1 text-[9px] font-black text-slate-400 uppercase mb-1">
                  <i class="fas fa-percent text-[9px]"></i> VAT %
                </label>
                <input type="number" id="vatRateInput"
                  class="w-full h-9 rounded-lg border-slate-200 text-[11px] font-bold text-center bg-slate-50 focus:bg-white focus:ring-brand-500"
                  value="5">
              </div>
              <div>
                <label class="flex items-center gap-1 text-[9px] font-black text-slate-400 uppercase mb-1">
                  <i class="fas fa-money-bill text-[9px]"></i> Cur
                </label>
                <select id="currencySelect"
                  class="w-full h-9 rounded-lg border-slate-200 text-[10px] font-bold bg-slate-50 focus:bg-white px-1">
                  <option value="AED">AED</option>
                  <option value="USD">USD</option>
                </select>
              </div>
            </div>

            <div class="space-y-1.5 px-2 mb-3">
              <div class="flex justify-between items-center text-[11px] text-slate-500 font-bold">
                <span>Subtotal</span> <span id="cartSubtotal" class="text-slate-800">0.00</span>
              </div>
              <div class="flex justify-between items-center text-[11px] text-slate-500 font-bold">
                <span>Shipping</span> <span id="cartShippingCost" class="text-slate-800">0.00</span>
              </div>
              <div class="flex justify-between items-center text-[11px] text-slate-500 font-bold">
                <span>Tax (VAT)</span> <span id="cartTaxAmount" class="text-slate-800">0.00</span>
              </div>
              <div class="flex justify-between items-center text-slate-900 pt-2 border-t border-slate-200 mt-1">
                <span class="text-[12px] font-black uppercase tracking-widest text-brand-600">Grand Total</span>
                <span class="text-2xl font-black text-brand-600" id="cartGrandTotal">0.00</span>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-1">
              <button type="button" onclick="saveOrder('draft')"
                class="col-span-1 h-10 rounded-lg bg-white border border-slate-300 text-slate-600 text-[11px] font-black uppercase hover:bg-slate-50 hover:text-slate-800 transition-colors">
                Save Draft
              </button>
              <button type="button" id="nextStepBtn"
                class="col-span-2 h-10 rounded-lg bg-brand-600 text-white text-[11px] font-black uppercase tracking-widest hover:bg-brand-700 shadow-lg shadow-brand-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                Finalize Order <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- Variant Selection Modal --}}
    <div id="variantModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
      aria-modal="true">
      <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
        id="variantModalBackdrop"></div>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div
            class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            id="variantModalPanel">

            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 pb-0">
              <div class="flex items-start gap-4">
                <div
                  class="flex-shrink-0 w-16 h-16 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden border border-slate-200">
                  <img id="modalImg" src="" class="w-full h-full object-cover hidden" alt="">
                  <i id="modalIcon" class="fas fa-box text-slate-300 text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="text-base font-bold text-slate-900 leading-6" id="modalProductName">Product Name</h3>
                  <p class="text-xs text-slate-500 mt-1" id="modalProductCategory">Category</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-500" onclick="closeVariantModal()">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <div class="mt-6">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-3">Select Variant</label>
                <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2" id="modalVariantsList"></div>
              </div>
            </div>

            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100 mt-6">
              <button type="button" disabled id="modalAddBtn"
                class="inline-flex w-full justify-center rounded-lg bg-brand-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-brand-500 sm:ml-3 sm:w-auto disabled:opacity-50 disabled:grayscale transition-all">
                Add to Cart
              </button>
              <button type="button"
                class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-all"
                onclick="closeVariantModal()">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Custom Item Modal --}}
    <div id="customItemModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
      <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity" id="customBackdrop">
      </div>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div
            class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            id="customPanel">

            <div class="bg-white px-4 pt-5 sm:p-6">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h3 class="text-base font-black text-slate-900">Add Custom Item</h3>
                  <p class="text-xs text-slate-500 mt-1">Use when item is not in catalog.</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-500" onclick="closeCustomItemModal()">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <div class="mt-4 grid grid-cols-1 gap-3">
                <div>
                  <label class="text-[10px] font-black text-slate-600 uppercase">Item Name <span
                      class="text-red-500">*</span></label>
                  <input id="customName" type="text"
                    class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold focus:border-brand-400 focus:ring-0"
                    placeholder="e.g. Extra service / Special part">
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="text-[10px] font-black text-slate-600 uppercase">SKU (optional)</label>
                    <input id="customSku" type="text"
                      class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold focus:border-brand-400 focus:ring-0"
                      placeholder="e.g. CUST-001">
                  </div>
                  <div>
                    <label class="text-[10px] font-black text-slate-600 uppercase">Qty <span
                        class="text-red-500">*</span></label>
                    <input id="customQty" type="number" min="1" value="1"
                      class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold focus:border-brand-400 focus:ring-0 text-center">
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="text-[10px] font-black text-slate-600 uppercase">Unit Price <span
                        class="text-red-500">*</span></label>
                    <input id="customPrice" type="number" min="0" step="0.01"
                      class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold focus:border-brand-400 focus:ring-0 text-center"
                      placeholder="0.00">
                  </div>
                  <div>
                    <label class="text-[10px] font-black text-slate-600 uppercase">Discount %</label>
                    <input id="customDisc" type="number" min="0" max="100" step="0.1"
                      value="0"
                      class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold focus:border-brand-400 focus:ring-0 text-center">
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-slate-50 px-4 py-3 border-t border-slate-100 sm:px-6 flex gap-2 justify-end">
              <button type="button" onclick="closeCustomItemModal()"
                class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-black hover:bg-slate-50 transition">
                Cancel
              </button>
              <button type="button" onclick="addCustomItemToCart()"
                class="h-10 px-4 rounded-xl bg-brand-600 text-white text-sm font-black hover:bg-brand-700 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Add
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>

    {{-- Hidden Inputs --}}
    <input type="hidden" id="orderUuid" value="{{ $order->uuid }}">
    <input type="hidden" id="orderCurrency" value="{{ $order->currency->code ?? 'AED' }}">
    <input type="hidden" id="additionalInput" value="{{ $initialState['additional'] ?? 0 }}">
  </div>
@endsection

@push('scripts')
  <script>
    const productsUrl = "{{ company_route('load-products') }}";
    const paymentTermsUrl = "{{ company_route('load-payment-terms') }}";
    const paymentMethodsUrl = "{{ company_route('load-payment-methods') }}";
    const orderUpdateUrl = "{{ company_route('sales-orders.update', ['sales_order' => $order->uuid]) }}";

    const initialState = @json($initialState ?? null);
    const currentOrder = @json($order);
    const customerDiscountPercent = {{ $customerDiscount ?? 0 }};

    let allProducts = [];
    let paymentTerms = [];
    let paymentMethods = [];

    let cart = [];
    let selectedCartIndex = -1;

    // ---------------------------
    // CART INIT (UUID-safe)
    // ---------------------------
    if (initialState && initialState.items) {
      cart = initialState.items.map(item => ({
        type: item.type || 'variant',
        variant_id: item.variant_id || item.variantId || null, // accept both keys
        custom_name: item.custom_name || item.customName || null,
        custom_sku: item.custom_sku || item.customSku || null,
        qty: parseFloat(item.qty),
        unit_price: parseFloat(item.unit_price ?? item.unitPrice),
        discount_percent: parseFloat(item.discount_percent ?? item.discountPercent ?? 0),
        temp_init: true
      }));

      $('#shippingInput').val(initialState.shipping || 0);
      $('#additionalInput').val(initialState.additional || 0);
      $('#vatRateInput').val(initialState.vat_rate || 5);
      $('#commentsInput').val(initialState.notes || '');
      $('#currencySelect').val(initialState.currency || 'AED');
      if (!$('#currencySelect').val()) {
        const firstOpt = $('#currencySelect option:first').val();
        if (firstOpt) $('#currencySelect').val(firstOpt);
      }
    } else if (currentOrder.items && currentOrder.items.length > 0) {
      cart = currentOrder.items.map(item => ({
        type: 'variant',
        // IMPORTANT: prefer uuid if present
        variant_id: item.product_variant?.uuid || item.product_variant_uuid || item.product_variant_id || null,
        qty: parseFloat(item.quantity),
        unit_price: parseFloat(item.unit_price),
        discount_percent: parseFloat(item.discount_percent || 0),
        temp_init: true
      }));
    }

    $(document).ready(function() {
      loadProducts();
      loadPaymentTerms();
      loadPaymentMethods();

      $('#productSearch').on('input', function() {
        renderProducts(this.value, $('#categoryFilter').val() || $('#categoryFilterMobile').val());
      });

      $('#categoryFilter').on('change', function() {
        renderProducts($('#productSearch').val(), this.value);
      });

      $('#categoryFilterMobile').on('change', function() {
        renderProducts($('#productSearch').val(), this.value);
      });

      $('#shippingInput, #additionalInput, #vatRateInput').on('input', renderCart);

      $('#modalAddBtn').on('click', function() {
        const variantUuid = $('input[name="selected_variant"]:checked').val();
        if (!variantUuid) return;

        let selectedVariant = null;
        let parentProduct = null;

        for (let p of allProducts) {
          const found = (p.variants || []).find(v => (v.uuid + '') === (variantUuid + '') || (v.id + '') === (
            variantUuid + ''));
          if (found) {
            selectedVariant = found;
            parentProduct = p;
            break;
          }
        }

        if (selectedVariant) {
          addToCart(parentProduct, selectedVariant);
          closeVariantModal();
        }
      });

      $('#nextStepBtn').on('click', function() {
        saveOrder('next_step');
      });

      $('#paymentTermSelect').on('change', function() {
        const termId = $(this).val();
        const term = paymentTerms.find(t => (t.id + '') === (termId + ''));

        if (term) {
          const dueDays = parseInt(term.due_days) || 0;
          const dueDate = new Date();
          dueDate.setDate(dueDate.getDate() + dueDays);

          // stable dd/mm/yyyy style by locale
          $('#paymentDueDate').val(dueDate.toLocaleDateString());

          if (dueDays === 0) {
            $('#paymentMethodWrapper').removeClass('hidden');
          } else {
            $('#paymentMethodWrapper').addClass('hidden');
            $('#paymentMethodSelect').val('');
          }
        } else {
          $('#paymentDueDate').val('-');
          $('#paymentMethodWrapper').addClass('hidden');
          $('#paymentMethodSelect').val('');
        }
      });

      $('#applyCustomerDiscount').on('change', function() {
        const apply = $(this).is(':checked');
        const percent = apply ? customerDiscountPercent : 0;
        cart.forEach(item => item.discount_percent = percent);
        renderCart();
      });

      // initial totals
      renderCart();
    });

    function loadPaymentTerms() {
      $.ajax({
        url: paymentTermsUrl,
        success: function(resp) {
          paymentTerms = resp || [];
          const sel = $('#paymentTermSelect');
          sel.empty().append(new Option('Select...', ''));
          paymentTerms.forEach(t => sel.append(new Option(t.name, t.id)));

          if (initialState && initialState.payment_term_id) {
            sel.val(initialState.payment_term_id).trigger('change');
          }
        }
      });
    }

    function loadPaymentMethods() {
      $.ajax({
        url: paymentMethodsUrl,
        success: function(resp) {
          paymentMethods = resp || [];
          const sel = $('#paymentMethodSelect');
          sel.empty().append(new Option('Select...', ''));
          paymentMethods.forEach(m => sel.append(new Option(m.name, m.id)));

          if (initialState && initialState.payment_method_id) {
            sel.val(initialState.payment_method_id);
          }
        }
      });
    }

    function loadProducts() {
      $.ajax({
        url: productsUrl,
        method: 'GET',
        success: function(response) {
          allProducts = response || [];
          populateCategories();
          renderProducts();
          hydrateCart();
        },
        error: function() {
          $('#productGrid').html(
            '<div class="col-span-full text-center text-red-500">Failed to load catalog.</div>');
        }
      });
    }

    function populateCategories() {
      const categories = [...new Set(allProducts.map(p => p.category).filter(Boolean))].sort();

      const selectDesktop = $('#categoryFilter');
      selectDesktop.empty().append(new Option('All Categories', ''));
      categories.forEach(cat => selectDesktop.append(new Option(cat, cat)));

      const selectMobile = $('#categoryFilterMobile');
      selectMobile.empty().append(new Option('All Categories', ''));
      categories.forEach(cat => selectMobile.append(new Option(cat, cat)));
    }

    // SKU search helper (safe if sku not present)
    function productMatchesSearch(p, lower, raw) {
      if ((p.name || '').toLowerCase().includes(lower)) return true;
      if ((p.brand || '').toLowerCase().includes(lower)) return true;
      if ((p.sku || '').toLowerCase().includes(lower)) return true;

      // variant sku scan (if API provides)
      if (p.variants && p.variants.length) {
        return p.variants.some(v =>
          ((v.sku || '').toLowerCase().includes(lower)) ||
          ((v.barcode || '').toLowerCase().includes(lower)) ||
          ((v.uuid || v.id || '') + '').toLowerCase().includes(lower)
        );
      }

      // exact scan fallback: if user typed raw (case-sensitive)
      return false;
    }

    function renderProducts(search = '', category = '') {
      const container = $('#productGrid');
      container.empty();

      let filtered = allProducts;

      if (search) {
        const lower = search.toLowerCase();
        filtered = filtered.filter(p => productMatchesSearch(p, lower, search));
      }

      if (category) {
        filtered = filtered.filter(p => p.category === category);
      }

      if (!filtered.length) {
        container.html(`
          <div class="col-span-full text-center py-10 text-slate-400">
            <i class="fas fa-box-open text-3xl mb-2 opacity-30"></i>
            <p class="text-sm">No products found.</p>
          </div>
        `);
        return;
      }

      filtered.forEach((p) => {
        const firstVar = (p.variants && p.variants.length > 0) ? p.variants[0] : null;
        const price = firstVar ? parseFloat(firstVar.price || 0).toFixed(2) : '0.00';

        const card = `
          <div class="group bg-white border border-slate-200 rounded-xl px-2 py-2 hover:shadow-sm hover:border-brand-300 transition cursor-pointer"
               onclick="handleProductClick('${p.id}')">
            <div class="flex items-center gap-2">
              <div class="w-9 h-9 rounded-lg bg-slate-50 shrink-0 flex items-center justify-center border border-slate-100 overflow-hidden">
                ${p.image_url
                  ? `<img src="${p.image_url}" class="w-full h-full object-cover">`
                  : `<i class="fas fa-box text-slate-300 text-[11px]"></i>`
                }
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="text-[10px] font-black text-slate-700 leading-tight line-clamp-2 group-hover:text-brand-600">
                  ${p.name || ''}
                </h4>
                <div class="mt-1 flex items-center justify-between">
                  <span class="text-[10px] font-black text-slate-900 font-mono">${price}</span>
                  <span class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-brand-600 group-hover:text-white transition">
                    <i class="fas fa-plus text-[10px]"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        `;
        container.append(card);
      });
    }

    function handleProductClick(productId) {
      const product = allProducts.find(p => (p.id + '') === (productId + ''));
      if (!product) return;

      if ((product.variants || []).length > 1) {
        openVariantModal(product);
      } else if ((product.variants || []).length === 1) {
        addToCart(product, product.variants[0]);
      }
    }

    function openVariantModal(product) {
      const modal = $('#variantModal');
      const backdrop = $('#variantModalBackdrop');
      const panel = $('#variantModalPanel');

      $('#modalProductName').text(product.name || '');
      $('#modalProductCategory').text(product.category || '');

      if (product.image_url) {
        $('#modalImg').attr('src', product.image_url).removeClass('hidden');
        $('#modalIcon').addClass('hidden');
      } else {
        $('#modalImg').addClass('hidden');
        $('#modalIcon').removeClass('hidden');
      }

      const list = $('#modalVariantsList');
      list.empty();

      (product.variants || []).forEach(v => {
        const attrStr = v.attrs ? Object.values(v.attrs).join(' / ') : 'Default';
        const price = parseFloat(v.price || 0).toFixed(2);

        // IMPORTANT: choose uuid when present; fallback to id
        const variantKey = v.uuid || v.id;

        const item = `
          <label class="relative flex cursor-pointer rounded-lg border border-slate-200 bg-white p-3 hover:border-brand-300 hover:bg-brand-50/10 transition-all">
            <input type="radio" name="selected_variant" value="${variantKey}" class="sr-only"
              onchange="$('#modalAddBtn').prop('disabled', false)">
            <span class="flex flex-1">
              <span class="flex flex-col">
                <span class="block text-sm font-bold text-slate-900">${attrStr}</span>
                <span class="mt-1 flex items-center text-xs text-slate-500 font-medium">Stock: ${v.stock ?? '-'}</span>
              </span>
            </span>
            <span class="flex flex-col text-right">
              <span class="text-sm font-black text-slate-900">${price}</span>
              <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $order->currency->code ?? 'AED' }}</span>
            </span>
          </label>
        `;
        list.append(item);
      });

      $('#modalAddBtn').prop('disabled', true);

      modal.removeClass('hidden');
      setTimeout(() => {
        backdrop.removeClass('opacity-0');
        panel.removeClass('opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95');
      }, 10);
    }

    function closeVariantModal() {
      const modal = $('#variantModal');
      const backdrop = $('#variantModalBackdrop');
      const panel = $('#variantModalPanel');

      backdrop.addClass('opacity-0');
      panel.addClass('opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95');

      setTimeout(() => modal.addClass('hidden'), 250);
    }

    // ---- Custom Item Modal
    window.openCustomItemModal = function() {
      const modal = $('#customItemModal');
      const backdrop = $('#customBackdrop');
      const panel = $('#customPanel');

      $('#customName').val('');
      $('#customSku').val('');
      $('#customQty').val(1);
      $('#customPrice').val('');
      $('#customDisc').val(0);

      modal.removeClass('hidden');
      setTimeout(() => {
        backdrop.removeClass('opacity-0');
        panel.removeClass('opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95');
      }, 10);
    }

    window.closeCustomItemModal = function() {
      const modal = $('#customItemModal');
      const backdrop = $('#customBackdrop');
      const panel = $('#customPanel');

      backdrop.addClass('opacity-0');
      panel.addClass('opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95');

      setTimeout(() => modal.addClass('hidden'), 250);
    }

    window.addCustomItemToCart = function() {
      const name = ($('#customName').val() || '').trim();
      const sku = ($('#customSku').val() || '').trim();
      const qty = parseFloat($('#customQty').val());
      const price = parseFloat($('#customPrice').val());
      const disc = parseFloat($('#customDisc').val()) || 0;

      if (!name) {
        Swal.fire('Required', 'Custom item name is required.', 'warning');
        return;
      }
      if (isNaN(qty) || qty <= 0) {
        Swal.fire('Invalid', 'Quantity must be at least 1.', 'warning');
        return;
      }
      if (isNaN(price) || price < 0) {
        Swal.fire('Invalid', 'Unit price is required.', 'warning');
        return;
      }

      cart.push({
        type: 'custom',
        variant_id: null,
        custom_name: name,
        custom_sku: sku || null,
        qty: qty,
        unit_price: price,
        discount_percent: Math.min(Math.max(disc, 0), 100),
      });

      closeCustomItemModal();
      renderCart();

      Swal.fire({
        icon: 'success',
        title: 'Custom item added',
        toast: true,
        position: 'bottom-end',
        timer: 1600,
        showConfirmButton: false
      });
    }

    function addToCart(product, variant) {
      const variantKey = variant.uuid || variant.id;

      const existing = cart.find(i =>
        i.type !== 'custom' &&
        (i.variant_id + '') === (variantKey + '')
      );

      const autoDisc = $('#applyCustomerDiscount').is(':checked') ? customerDiscountPercent : 0;

      if (existing) {
        existing.qty = (parseFloat(existing.qty) || 0) + 1;
      } else {
        cart.push({
          type: 'variant',
          variant_id: variantKey, // UUID-safe
          qty: 1,
          product: product,
          variant: variant,
          unit_price: parseFloat(variant.price || 0),
          discount_percent: autoDisc
        });
      }

      renderCart();

      Swal.mixin({
          toast: true,
          position: 'bottom-end',
          showConfirmButton: false,
          timer: 1500
        })
        .fire({
          icon: 'success',
          title: 'Added to basket'
        });
    }

    function hydrateCart() {
      cart.forEach(item => {
        if (item.temp_init && item.type !== 'custom') {
          for (let p of allProducts) {
            const foundVar = (p.variants || []).find(v => ((v.uuid || v.id) + '') === (item.variant_id + ''));
            if (foundVar) {
              item.product = p;
              item.variant = foundVar;
              delete item.temp_init;
              break;
            }
          }
        }
      });
      renderCart();
    }

    function renderCart() {
      const container = $('#cartItemsContainer');
      container.empty();

      // reset totals
      let subtotal = 0;
      let countQty = 0;

      if (!cart.length) {
        container.html(`
          <div class="flex flex-col items-center justify-center h-full text-center text-slate-300 py-10">
            <i class="fas fa-basket-shopping text-3xl mb-2 opacity-30"></i>
            <span class="text-xs font-medium">Basket is empty</span>
          </div>
        `);

        $('#cartSubtotal').text('0.00');
        $('#cartShippingCost').text('0.00');
        $('#cartTaxAmount').text('0.00');
        $('#cartGrandTotal').text('0.00');
        $('#itemCountLabel').text('0 Lines • 0 Qty');

        $('#nextStepBtn').prop('disabled', true).addClass('opacity-60 cursor-not-allowed');
        resetPosPanel();
        return;
      }

      cart.forEach((item, index) => {
        const isSelected = (index === selectedCartIndex);
        const activeClass = isSelected ? 'cart-item-selected' : '';

        const qty = parseFloat(item.qty || 0);
        const unit = parseFloat(item.unit_price || 0);
        const discP = parseFloat(item.discount_percent || 0);

        const lineGross = qty * unit;
        const lineDisc = lineGross * (discP / 100);
        const lineNet = lineGross - lineDisc;

        subtotal += lineNet;
        countQty += qty;

        const isCustom = item.type === 'custom';
        const name = isCustom ? item.custom_name : (item.product?.name || 'Item');
        const attrStr = isCustom ?
          (item.custom_sku ? `SKU: ${item.custom_sku}` : 'Custom item') :
          (item.variant?.attrs ? Object.values(item.variant.attrs).join(' / ') : '');

        const img = (!isCustom && item.product?.image_url) ?
          `<img src="${item.product.image_url}" class="w-full h-full object-cover">` :
          `<div class="w-full h-full flex items-center justify-center text-slate-400"><i class="fas ${isCustom ? 'fa-pen-ruler' : 'fa-box'} text-[10px]"></i></div>`;

        const row = `
          <div onclick="selectCartItem(${index})"
            class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-100 hover:border-brand-200 hover:shadow-sm transition-all group mb-1.5 cursor-pointer ${activeClass}">
            <div class="w-9 h-9 rounded-lg bg-slate-50 flex-shrink-0 overflow-hidden border border-slate-200">
              ${img}
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-center gap-2">
                <div class="min-w-0">
                  <h5 class="text-[11px] font-black text-slate-800 truncate item-name">${name}</h5>
                  <p class="text-[9px] text-slate-400 truncate font-bold">${attrStr}</p>
                </div>
                <div class="text-right">
                  <span class="text-[11px] font-black text-slate-900">${lineNet.toFixed(2)}</span>
                </div>
              </div>

              <div class="flex items-center gap-2 mt-1">
                <span class="text-[10px] font-bold text-slate-500">${qty} x ${unit.toFixed(2)}</span>
                ${discP > 0 ? `<span class="text-[9px] font-black text-brand-700 bg-brand-50 px-1.5 py-0.5 rounded-lg">(-${discP}%)</span>` : ''}
                ${isCustom ? `<span class="text-[9px] font-black text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded-lg">CUSTOM</span>` : ''}
              </div>
            </div>
          </div>
        `;
        container.append(row);
      });

      // POS panel sync
      if (selectedCartIndex !== -1 && cart[selectedCartIndex]) {
        syncPosPanel(cart[selectedCartIndex]);
      } else {
        resetPosPanel();
      }

      const shipping = parseFloat($('#shippingInput').val()) || 0;
      const vatRate = parseFloat($('#vatRateInput').val()) || 0;

      const tax = subtotal * (vatRate / 100);
      const grand = subtotal + tax + shipping;

      $('#cartSubtotal').text(subtotal.toFixed(2));
      $('#cartShippingCost').text(shipping.toFixed(2));
      $('#cartTaxAmount').text(tax.toFixed(2));
      $('#cartGrandTotal').text(grand.toFixed(2));

      $('#itemCountLabel').text(cart.length + ' Lines • ' + countQty + ' Qty');

      $('#nextStepBtn').prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
    }

    window.selectCartItem = function(index) {
      selectedCartIndex = index;
      renderCart();
    };

    function syncPosPanel(item) {
      const label = (item.type === 'custom') ? item.custom_name : (item.product?.name || 'Item');

      $('#selectedItemLabel').text(label).removeClass('italic text-slate-400').addClass('text-brand-200');

      $('#actionControls').removeClass('opacity-40 grayscale pointer-events-none');
      $('#posQtyDisplay').text(item.qty);
      $('#posPriceDisplay').text(parseFloat(item.unit_price || 0).toFixed(2));

      $('#removeSelectedBtn')
        .removeClass('opacity-0 translate-x-3 pointer-events-none')
        .addClass('opacity-100 translate-x-0');
    }

    function resetPosPanel() {
      $('#selectedItemLabel').text('No Selection').addClass('italic text-slate-400').removeClass('text-brand-200');

      $('#actionControls').addClass('opacity-40 grayscale pointer-events-none');
      $('#posQtyDisplay').text('1');
      $('#posPriceDisplay').text('0.00');

      $('#removeSelectedBtn')
        .addClass('opacity-0 translate-x-3 pointer-events-none')
        .removeClass('opacity-100 translate-x-0');
    }

    window.posUpdateQty = function(change) {
      if (selectedCartIndex !== -1 && cart[selectedCartIndex]) {
        cart[selectedCartIndex].qty = (parseFloat(cart[selectedCartIndex].qty) || 0) + change;

        if (cart[selectedCartIndex].qty <= 0) {
          cart.splice(selectedCartIndex, 1);
          selectedCartIndex = -1;
        }
        renderCart();
      }
    };

    window.posApplyDisc = function(percent) {
      if (selectedCartIndex !== -1 && cart[selectedCartIndex]) {
        cart[selectedCartIndex].discount_percent = percent;
        renderCart();
      }
    };

    window.posPromptDisc = function() {
      if (selectedCartIndex === -1 || !cart[selectedCartIndex]) return;

      Swal.fire({
        title: 'Custom Discount (%)',
        input: 'number',
        inputAttributes: {
          min: 0,
          max: 100,
          step: 0.1
        },
        inputValue: cart[selectedCartIndex].discount_percent || 0,
        showCancelButton: true,
        confirmButtonText: 'Apply',
        confirmButtonColor: '#4f46e5'
      }).then((result) => {
        if (result.isConfirmed) {
          const v = parseFloat(result.value) || 0;
          cart[selectedCartIndex].discount_percent = Math.min(Math.max(v, 0), 100);
          renderCart();
        }
      });
    };

    window.posPromptPrice = function() {
      if (selectedCartIndex === -1 || !cart[selectedCartIndex]) return;

      Swal.fire({
        title: 'Override Selling Price',
        input: 'number',
        inputAttributes: {
          min: 0,
          step: 0.01
        },
        inputValue: cart[selectedCartIndex].unit_price,
        showCancelButton: true,
        confirmButtonText: 'Override',
        confirmButtonColor: '#10b981'
      }).then((result) => {
        if (result.isConfirmed) {
          const val = parseFloat(result.value);
          if (!isNaN(val)) {
            cart[selectedCartIndex].unit_price = val;
            renderCart();
          }
        }
      });
    };

    window.posRemoveItem = function() {
      if (selectedCartIndex !== -1 && cart[selectedCartIndex]) {
        cart.splice(selectedCartIndex, 1);
        selectedCartIndex = -1;
        renderCart();
      }
    };

    function saveOrder(action) {
      const btn = $('#nextStepBtn');
      const originalHtml = btn.html();

      if (action === 'next_step') {
        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Processing...');
      }

      const payload = {
        _method: 'PUT',
        _token: $('meta[name="csrf-token"]').attr('content'),
        currency: $('#currencySelect').val(),
        payment_term_id: $('#paymentTermSelect').val(),
        payment_method_id: $('#paymentMethodSelect').val(),
        shipping: $('#shippingInput').val(),
        additional: $('#additionalInput').val(),
        vat_rate: $('#vatRateInput').val(),
        notes: $('#commentsInput').val(),
        items: cart.map(i => ({
          type: i.type || 'variant',
          variant_id: (i.type === 'custom') ? null : i.variant_id,
          custom_name: (i.type === 'custom') ? (i.custom_name || null) : null,
          custom_sku: (i.type === 'custom') ? (i.custom_sku || null) : null,
          qty: i.qty,
          unit_price: i.unit_price,
          discount_percent: i.discount_percent
        }))
      };

      $.ajax({
        url: orderUpdateUrl,
        method: 'POST',
        data: payload,
        success: function() {
          if (action === 'next_step') {
            window.location.href = "{{ company_route('sales-orders.finalize', ['sales_order' => $order->uuid]) }}";
          } else {
            Swal.fire({
              icon: 'success',
              title: 'Draft Saved',
              toast: true,
              position: 'top-end',
              timer: 2000,
              showConfirmButton: false
            });
          }
          btn.prop('disabled', false).html(originalHtml);
        },
        error: function(xhr) {
          btn.prop('disabled', false).html(originalHtml);
          console.error(xhr);

          let msg = 'Failed to save order.';
          if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

          if (xhr.responseJSON && xhr.responseJSON.errors) {
            const details = Object.values(xhr.responseJSON.errors).map(e => e[0]).join('\n');
            msg += '\n' + details;
          }

          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: msg,
            confirmButtonColor: '#ef4444'
          });
        }
      });
    }
  </script>
@endpush
