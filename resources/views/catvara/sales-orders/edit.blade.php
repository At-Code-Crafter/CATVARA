@extends('catvara.layouts.app')

@section('title', 'Sales Order - Edit')

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
      animation: fadeInSlide 0.4s ease-out forwards;
      opacity: 0;
    }

    /* Scrollbar for cart */
    .cart-scroll::-webkit-scrollbar {
      width: 4px;
    }

    .cart-scroll::-webkit-scrollbar-track {
      background: #f1f5f9;
    }

    .cart-scroll::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }

    .cart-scroll::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
  </style>

  <div class="w-full px-8 pb-20 animate-fade-in">
    {{-- Header --}}
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
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Add Items for {{ $order->order_number }}</h1>
      </div>

      {{-- Progress --}}
      <div class="hidden md:flex items-center gap-3 bg-white px-4 py-2.5 rounded-xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-2 opacity-60">
          <div
            class="w-6 h-6 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center font-black text-[10px]">
            <i class="fas fa-check"></i>
          </div>
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Customer</span>
        </div>
        <div class="w-8 h-0.5 bg-emerald-500"></div>
        <div class="flex items-center gap-2">
          <div
            class="w-6 h-6 rounded bg-brand-600 text-white flex items-center justify-center font-black text-[10px] shadow-lg shadow-brand-500/20 ring-2 ring-brand-100">
            02
          </div>
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Basket</span>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

      {{-- LEFT: Product Grid --}}
      <div class="lg:col-span-8 space-y-4">

        <!-- Filter Bar -->
        <div class="card bg-white border-slate-100 shadow-soft p-4 sticky top-20 z-10 transition-all duration-300"
          id="filterBar">
          <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
              <div class="input-icon-group group/input">
                <i
                  class="fas fa-search text-slate-400 group-focus-within/input:text-brand-400 transition-colors duration-300"></i>
                <input type="text" id="productSearch"
                  class="w-full pl-9 h-[40px] rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all duration-300 placeholder:text-slate-400"
                  placeholder="Search products by name, brand or SKU...">
              </div>
            </div>
            <div class="md:w-56">
              <select id="categoryFilter"
                class="w-full h-[40px] rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all duration-300 text-slate-600">
                <option value="">All Categories</option>
                {{-- Populated via JS --}}
              </select>
            </div>
          </div>
        </div>

        <!-- Product Grid -->
        <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 min-h-[400px]">
          <div class="col-span-full py-20 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-50 mb-3 animate-pulse">
              <i class="fas fa-circle-notch fa-spin text-brand-400 text-lg"></i>
            </div>
            <p class="text-slate-400 font-medium text-xs">Loading catalog...</p>
          </div>
        </div>
      </div>

      {{-- RIGHT: Cart Sidebar --}}
      <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-6">

        <!-- Cart Card -->
        <div class="card bg-white border-slate-100 shadow-xl overflow-hidden flex flex-col h-[calc(100vh-120px)]">
          <div class="p-4 border-b border-slate-50 bg-slate-50/50">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-shopping-basket text-brand-500"></i> Current Draft
              </h3>
              <span class="text-[10px] font-bold text-slate-400" id="itemCountLabel">0 Items</span>
            </div>

            {{-- Payment Terms & Discount --}}
            <div class="space-y-3">
              <div class="grid grid-cols-1 gap-2">
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Pay Terms</label>
                    <select id="paymentTermSelect"
                      class="w-full h-8 rounded border-slate-200 text-xs font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 text-slate-700">
                      <option value="">Select...</option>
                    </select>
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Due Date</label>
                    <input id="paymentDueDate" type="text"
                      class="w-full h-8 rounded border-slate-200 text-xs font-bold bg-slate-50 text-slate-500 text-center"
                      readonly placeholder="-">
                  </div>
                </div>

                {{-- Payment Method (Hidden by default) --}}
                <div id="paymentMethodWrapper" class="hidden">
                  <label class="text-[10px] font-bold text-slate-500 uppercase">Payment Method</label>
                  <select id="paymentMethodSelect"
                    class="w-full h-8 rounded border-slate-200 text-xs font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 text-slate-700">
                    <option value="">Select Method...</option>
                  </select>
                </div>
              </div>

              <div class="flex items-center justify-between bg-slate-50 p-2 rounded border border-slate-100">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" id="applyCustomerDiscount"
                    class="form-checkbox w-3 h-3 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                  <span class="text-[10px] font-bold text-slate-600 uppercase">Apply Customer Disc
                    ({{ $customerDiscount }}%)</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Items List -->
          <div class="flex-1 overflow-y-auto cart-scroll p-2 space-y-2" id="cartItemsContainer">
            <div class="flex flex-col items-center justify-center h-full text-center text-slate-300 py-10">
              <i class="fas fa-basket-shopping text-3xl mb-2 opacity-30"></i>
              <span class="text-xs font-medium">Basket is empty</span>
            </div>
          </div>

          <!-- Footer -->
          <div class="p-4 bg-slate-50 border-t border-slate-100 space-y-3">
            {{-- Restored Inputs --}}
            <div>
              <textarea id="commentsInput" rows="2"
                class="w-full rounded-lg border-slate-200 text-xs focus:border-brand-400 focus:ring-0 resize-none placeholder:text-slate-400"
                placeholder="Additional notes..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-[9px] font-bold text-slate-400 uppercase">Shipping</label>
                <input type="number" id="shippingInput"
                  class="w-full h-8 rounded border-slate-200 text-xs font-bold text-right" value="0" min="0"
                  step="0.01">
              </div>
              <div>
                <label class="text-[9px] font-bold text-slate-400 uppercase">Additional</label>
                <input type="number" id="additionalInput"
                  class="w-full h-8 rounded border-slate-200 text-xs font-bold text-right" value="0" min="0"
                  step="0.01">
              </div>
              <div>
                <label class="text-[9px] font-bold text-slate-400 uppercase">VAT %</label>
                <input type="number" id="vatRateInput"
                  class="w-full h-8 rounded border-slate-200 text-xs font-bold text-right" value="5"
                  min="0" step="0.01">
              </div>
              <div>
                <label class="text-[9px] font-bold text-slate-400 uppercase">Currency</label>
                <select id="currencySelect"
                  class="w-full h-8 rounded border-slate-200 text-xs font-bold text-slate-700">
                  <option value="AED">AED</option>
                  <option value="USD">USD</option>
                  <option value="GBP">GBP</option>
                </select>
              </div>
            </div>

            <div class="border-t border-slate-200 pt-3 space-y-1">
              <div class="flex justify-between items-center text-xs text-slate-500">
                <span>Subtotal</span>
                <span class="font-bold" id="cartSubtotal">0.00</span>
              </div>
              <div class="flex justify-between items-center text-xs text-slate-500">
                <span>Tax / VAT</span>
                <span class="font-bold" id="cartTax">0.00</span>
              </div>
              <div class="flex justify-between items-center text-xs text-slate-500">
                <span>Shipping</span>
                <span class="font-bold" id="cartShipping">0.00</span>
              </div>
              <div class="flex justify-between items-center text-sm font-black text-slate-800 pt-2">
                <span>Total</span>
                <span id="cartGrandTotal">0.00</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
              <button type="button"
                class="btn btn-white w-full border-slate-200 text-slate-600 text-xs font-bold py-2.5">
                Save Draft
              </button>
              <button type="button" id="nextStepBtn"
                class="btn bg-brand-600 hover:bg-brand-500 text-white w-full border-0 text-xs font-bold py-2.5 shadow-lg shadow-brand-500/20 disabled:opacity-50 disabled:grayscale transition-all duration-300">
                Finalize <i class="fas fa-arrow-right ml-1"></i>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Variant Selection Modal --}}
  <div id="variantModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="variantModalBackdrop">
    </div>

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
              <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scroll" id="modalVariantsList">
                {{-- Variants injected here --}}
              </div>
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

  {{-- Hidden Input for Order ID --}}
  <input type="hidden" id="orderUuid" value="{{ $order->uuid }}">
  <input type="hidden" id="orderCurrency" value="{{ $order->currency->code ?? 'AED' }}">

@endsection

@push('scripts')
  <script>
    const productsUrl = "{{ company_route('load-products') }}";
    const paymentTermsUrl = "{{ company_route('load-payment-terms') }}";
    const paymentMethodsUrl = "{{ company_route('load-payment-methods') }}";
    const orderUpdateUrl = "{{ company_route('sales-orders.update', ['sales_order' => $order->uuid]) }}";

    // Initial State & Context
    const initialState = @json($initialState ?? null);
    const currentOrder = @json($order);
    const customerDiscountPercent = {{ $customerDiscount ?? 0 }};

    let allProducts = [];
    let paymentTerms = [];
    let paymentMethods = [];
    let cart = [];

    // Init Cart logic based on initialState
    if (initialState && initialState.items) {
      cart = initialState.items.map(item => ({
        variant_id: item.variantId,
        qty: parseFloat(item.qty),
        unit_price: parseFloat(item.unitPrice),
        discount_percent: parseFloat(item.discountPercent || 0),
        temp_init: true
      }));

      // Populate inputs
      $('#shippingInput').val(initialState.shipping || 0);
      $('#additionalInput').val(initialState.additional || 0);
      $('#vatRateInput').val(initialState.vat_rate || 5);
      $('#commentsInput').val(initialState.notes || '');
      $('#currencySelect').val(initialState.currency || 'AED');
    } else if (currentOrder.items && currentOrder.items.length > 0) {
      cart = currentOrder.items.map(item => ({
        variant_id: item.product_variant.id,
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
        renderProducts(this.value, $('#categoryFilter').val());
      });

      $('#categoryFilter').on('change', function() {
        renderProducts($('#productSearch').val(), this.value);
      });

      // Recalc totals on input change
      $('#shippingInput, #additionalInput, #vatRateInput').on('input', renderCart);

      // Modal Events
      $('#modalAddBtn').on('click', function() {
        const variantId = $('input[name="selected_variant"]:checked').val();
        if (!variantId) return;

        // Find variant details
        let selectedVariant = null;
        let parentProduct = null;

        // Search all products
        for (let p of allProducts) {
          const found = p.variants.find(v => v.id == variantId);
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
        const term = paymentTerms.find(t => t.id == termId);

        if (term) {
          // Calculate Due Date
          const dueDays = parseInt(term.due_days) || 0;
          const dueDate = new Date();
          dueDate.setDate(dueDate.getDate() + dueDays);
          $('#paymentDueDate').val(dueDate.toLocaleDateString());

          // Show Payment Method if Immediate (0 days)
          if (dueDays === 0) {
            $('#paymentMethodWrapper').removeClass('hidden');
          } else {
            $('#paymentMethodWrapper').addClass('hidden');
            $('#paymentMethodSelect').val('');
          }
        } else {
          $('#paymentDueDate').val('-');
          $('#paymentMethodWrapper').addClass('hidden');
        }
      });

      $('#applyCustomerDiscount').on('change', function() {
        const apply = $(this).is(':checked');
        const percent = apply ? customerDiscountPercent : 0;

        cart.forEach(item => {
          item.discount_percent = percent;
        });
        renderCart();
      });
    });

    function loadPaymentTerms() {
      $.ajax({
        url: paymentTermsUrl,
        success: function(resp) {
          paymentTerms = resp;
          const sel = $('#paymentTermSelect');
          paymentTerms.forEach(t => {
            sel.append(new Option(t.name, t.id));
          });

          // Set selected if exists
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
          paymentMethods = resp;
          const sel = $('#paymentMethodSelect');
          paymentMethods.forEach(m => {
            sel.append(new Option(m.name, m.id));
          });
          // TODO: Set initial payment method if it exists in state
        }
      });
    }

    function loadProducts() {
      $.ajax({
        url: productsUrl,
        method: 'GET',
        success: function(response) {
          allProducts = response;
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
      const select = $('#categoryFilter');
      categories.forEach(cat => {
        select.append(new Option(cat, cat));
      });
    }

    function renderProducts(search = '', category = '') {
      const container = $('#productGrid');
      container.empty();

      let filtered = allProducts;

      if (search) {
        const lower = search.toLowerCase();
        filtered = filtered.filter(p =>
          p.name.toLowerCase().includes(lower) ||
          (p.brand && p.brand.toLowerCase().includes(lower))
        );
      }

      if (category) {
        filtered = filtered.filter(p => p.category === category);
      }

      if (filtered.length === 0) {
        container.html(`
                <div class="col-span-full text-center py-10 text-slate-400">
                    <i class="fas fa-box-open text-3xl mb-2 opacity-30"></i>
                    <p class="text-sm">No products found.</p>
                </div>
            `);
        return;
      }

      filtered.forEach((p, index) => {
        const hasVariants = p.variants.length > 1;
        const price = p.variants.length > 0 ? parseFloat(p.variants[0].price).toFixed(2) : '0.00';
        const stock = p.variants.reduce((acc, v) => acc + v.stock, 0);

        // Stagger animation
        const delay = Math.min(index * 0.05, 1);

        const card = `
                <div class="card bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-brand-200 transition-all duration-300 group cursor-pointer animate-entry"
                     style="animation-delay: ${delay}s"
                     onclick="handleProductClick('${p.id}')">
                    <div class="relative aspect-[4/3] bg-slate-50 overflow-hidden border-b border-slate-50">
                        ${p.image_url 
                           ? `<img src="${p.image_url}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">`
                           : `<div class="w-full h-full flex items-center justify-center text-slate-200"><i class="fas fa-box text-3xl"></i></div>`
                        }
                        
                        <div class="absolute top-2 right-2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                             <span class="bg-white/90 backdrop-blur text-slate-700 text-[10px] font-black px-2 py-1 rounded shadow-sm">${stock} In Stock</span>
                        </div>
                    </div>
                    
                    <div class="p-3">
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <h4 class="text-sm font-bold text-slate-800 line-clamp-2 leading-tight group-hover:text-brand-600 transition-colors">${p.name}</h4>
                        </div>
                        <p class="text-[10px] text-slate-400 mb-3">${p.category || 'Uncategorized'}</p>
                        
                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-sm font-black text-slate-800">${price} <span class="text-[9px] text-slate-400 font-bold uppercase ml-0.5">{{ $order->currency->code ?? 'AED' }}</span></span>
                            
                            <button class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-brand-500 hover:text-white flex items-center justify-center transition-colors">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
             `;
        container.append(card);
      });
    }

    function handleProductClick(productId) {
      const product = allProducts.find(p => p.id == productId);
      if (!product) return;

      if (product.variants.length > 1) {
        openVariantModal(product);
      } else if (product.variants.length === 1) {
        addToCart(product, product.variants[0]);
      }
    }

    function openVariantModal(product) {
      const modal = $('#variantModal');
      const backdrop = $('#variantModalBackdrop');
      const panel = $('#variantModalPanel');

      // Populate
      $('#modalProductName').text(product.name);
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

      product.variants.forEach(v => {
        // Create attribute string
        const attrStr = Object.entries(v.attrs).map(([k, val]) => `${val}`).join(' / ') || 'Default';
        const price = parseFloat(v.price).toFixed(2);

        const item = `
                <label class="relative flex cursor-pointer rounded-lg border border-slate-200 bg-white p-3 hover:border-brand-300 hover:bg-brand-50/10 transition-all focus-within:ring-2 focus-within:ring-brand-500 focus-within:ring-offset-2">
                    <input type="radio" name="selected_variant" value="${v.id}" class="sr-only" onchange="$('#modalAddBtn').prop('disabled', false)">
                    <span class="flex flex-1">
                      <span class="flex flex-col">
                        <span class="block text-sm font-bold text-slate-900">${attrStr}</span>
                        <span class="mt-1 flex items-center text-xs text-slate-500 font-medium">Stock: ${v.stock}</span>
                      </span>
                    </span>
                    <span class="flex flex-col text-right">
                       <span class="text-sm font-black text-slate-900">${price}</span>
                       <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $order->currency->code ?? 'AED' }}</span>
                    </span>
                    <span class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-brand-500" aria-hidden="true"></span>
                </label>
             `;
        list.append(item);
      });

      $('#modalAddBtn').prop('disabled', true);

      // Show
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

      setTimeout(() => {
        modal.addClass('hidden');
      }, 300);
    }

    function addToCart(product, variant) {
      // Check if exists
      const existing = cart.find(i => i.variant_id == variant.id);

      // Check auto discount
      const autoDisc = $('#applyCustomerDiscount').is(':checked') ? customerDiscountPercent : 0;

      if (existing) {
        existing.qty++;
        // Maintain existing discount unless overriden by logic elsewhere, 
        // but user might want new items to inherit global status? 
        // For now, let's leave existing discount as is, unless user explicitly toggles the box.
      } else {
        cart.push({
          variant_id: variant.id,
          qty: 1,
          product: product,
          variant: variant,
          unit_price: parseFloat(variant.price),
          discount_percent: autoDisc
        });
      }

      renderCart();

      // Show Toast
      const toast = Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: false,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      });
      toast.fire({
        icon: 'success',
        title: 'Added to basket',
        customClass: {
          popup: 'colored-toast'
        }
      });
    }

    function hydrateCart() {
      cart.forEach(item => {
        if (item.temp_init) {
          for (let p of allProducts) {
            const foundVar = p.variants.find(v => v.id == item.variant_id);
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

      if (cart.length === 0) {
        container.html(`
              <div class="flex flex-col items-center justify-center h-full text-center text-slate-300 py-10">
                  <i class="fas fa-basket-shopping text-3xl mb-2 opacity-30"></i>
                  <span class="text-xs font-medium">Basket is empty</span>
              </div>
            `);
        $('#cartSubtotal').text('0.00');
        $('#itemCountLabel').text('0 Items');
        $('#nextStepBtn').prop('disabled', true);
        return;
      }

      let subtotal = 0;
      let count = 0;

      cart.forEach((item, index) => {
        if (!item.product || !item.variant) return;

        const total = item.qty * item.unit_price;
        const rowDiscount = total * ((item.discount_percent || 0) / 100);
        const finalTotal = total - rowDiscount;

        subtotal += finalTotal;
        count += item.qty;

        const attrStr = Object.values(item.variant.attrs).join(' / ');

        const row = `
                <div class="flex items-start gap-2 p-3 rounded-lg bg-white border border-slate-100 hover:border-brand-200 hover:shadow-sm transition-all group mb-2">
                    <div class="w-12 h-12 rounded bg-slate-50 flex-shrink-0 overflow-hidden border border-slate-200">
                        ${item.product.image_url 
                          ? `<img src="${item.product.image_url}" class="w-full h-full object-cover">`
                          : `<div class="w-full h-full flex items-center justify-center text-slate-400"><i class="fas fa-box text-sm"></i></div>`
                        }
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <!-- Top Row: Name & Remove -->
                        <div class="flex justify-between items-start mb-1">
                            <div>
                                <h5 class="text-xs font-bold text-slate-800 leading-tight">${item.product.name}</h5>
                                <p class="text-[10px] text-slate-500 truncate">${attrStr}</p>
                            </div>
                            <button onclick="removeFromCart(${index})" class="w-5 h-5 flex items-center justify-center rounded-full text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        </div>
                        
                        <!-- Middle Row: Qty & Price -->
                        <div class="flex items-center justify-between mt-2">
                            <!-- Qty Control -->
                            <div class="flex items-center gap-1 bg-slate-50 rounded p-0.5 border border-slate-200">
                                <button onclick="updateQty(${index}, -1)" class="w-5 h-5 rounded bg-white text-slate-600 hover:text-brand-600 shadow-sm flex items-center justify-center transition-all">
                                    <i class="fas fa-minus text-[8px]"></i>
                                </button>
                                <span class="text-xs font-bold text-slate-700 w-5 text-center">${item.qty}</span>
                                <button onclick="updateQty(${index}, 1)" class="w-5 h-5 rounded bg-white text-slate-600 hover:text-brand-600 shadow-sm flex items-center justify-center transition-all">
                                    <i class="fas fa-plus text-[8px]"></i>
                                </button>
                            </div>

                            <!-- Discount Input -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Disc%</span>
                                <input type="number" 
                                    onchange="updateLineDiscount(${index}, this.value)" 
                                    value="${item.discount_percent || 0}" 
                                    class="w-14 h-7 text-center text-xs font-bold border-slate-200 rounded focus:border-brand-400 focus:ring-1 focus:ring-brand-500 text-brand-600 bg-brand-50/50">
                            </div>
                        </div>

                        <!-- Bottom Row: Totals -->
                        <div class="mt-2 text-right">
                            ${rowDiscount > 0 
                                ? `<span class="text-[10px] text-slate-400 line-through mr-1">${total.toFixed(2)}</span>`
                                : ''
                            }
                            <span class="text-sm font-black text-slate-800">${finalTotal.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
        container.append(row);
      });

      // Calc Totals
      const shipping = parseFloat($('#shippingInput').val()) || 0;
      const additional = parseFloat($('#additionalInput').val()) || 0;
      const vatRate = parseFloat($('#vatRateInput').val()) || 0;

      // Simplified tax calculation for UI (Server does real calc)
      const taxable = subtotal; // discount logic to be added if implemented
      const tax = taxable * (vatRate / 100);
      const grand = subtotal + tax + shipping + additional;

      $('#cartSubtotal').text(subtotal.toFixed(2));
      $('#cartTax').text(tax.toFixed(2));
      $('#cartShipping').text((shipping + additional).toFixed(2));
      $('#cartGrandTotal').text(grand.toFixed(2));

      $('#itemCountLabel').text(cart.length + ' Items');
      $('#nextStepBtn').prop('disabled', false);
    }

    window.updateQty = function(index, change) {
      if (cart[index]) {
        cart[index].qty += change;
        if (cart[index].qty <= 0) {
          cart.splice(index, 1);
        }
        renderCart();
      }
    };

    window.updateLineDiscount = function(index, val) {
      if (cart[index]) {
        let v = parseFloat(val);
        if (isNaN(v) || v < 0) v = 0;
        if (v > 100) v = 100;
        cart[index].discount_percent = v;
        renderCart();
      }
    };

    window.removeFromCart = function(index) {
      cart.splice(index, 1);
      renderCart();
    };

    function saveOrder(action) {
      const btn = $('#nextStepBtn');
      const originalHtml = btn.html();
      btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Processing...');

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
          variant_id: i.variant_id,
          qty: i.qty,
          unit_price: i.unit_price,
          discount_percent: i.discount_percent
        }))
      };

      $.ajax({
        url: orderUpdateUrl,
        method: 'POST',
        data: payload,
        success: function(response) {
          if (action === 'next_step') {
            // Navigate to Step 3. For now, since Step 3 isn't clearly defined, we'll go to Show or reload.
            window.location.href = "{{ company_route('sales-orders.show', ['sales_order' => $order->id]) }}";
          } else {
            btn.prop('disabled', false).html(originalHtml);
            Swal.fire({
              icon: 'success',
              title: 'Draft Saved',
              toast: true,
              position: 'top-end',
              timer: 2000,
              showConfirmButton: false
            });
          }
        },
        error: function(xhr) {
          btn.prop('disabled', false).html(originalHtml);
          console.error(xhr);
          Swal.fire('Error', 'Failed to save order.', 'error');
        }
      });
    }
  </script>
@endpush
