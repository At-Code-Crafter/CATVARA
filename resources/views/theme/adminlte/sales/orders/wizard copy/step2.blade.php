@extends('theme.adminlte.sales.orders.wizard.layout')

@section('css')
  <style>
    /* Ultra Enterprise POS Theme (Copied from create.blade.php) */
    :root {
      --primary-color: #4f46e5;
      --primary-dark: #4338ca;
      --secondary-color: #64748b;
      --bg-surface: #f8fafc;
      --border-color: #e2e8f0;
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --radius-lg: 1rem;
      --radius-xl: 1.5rem;
    }

    /* Grid & Cards */
    .pos-card {
      height: 100%;
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      cursor: pointer;
      display: flex;
      flex-direction: column;
      transition: all 0.2s ease;
    }

    .pos-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary-color);
    }

    .product-img-container {
      height: 160px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      padding: 1rem;
    }

    .brand-label {
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      color: #94a3b8;
    }

    .product-name {
      font-weight: 700;
      color: #1e293b;
      font-size: 1rem;
      line-height: 1.4;
      margin-bottom: auto;
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

    /* Sticky Sidebar */
    .sticky-panel {
      position: sticky;
      top: 20px;
      height: calc(100vh - 40px);
      overflow: hidden;
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
      background: #fff;
      display: flex;
      /* Flex container for header/body/footer */
      flex-direction: column;
    }

    .cart-scroll-area {
      flex: 1;
      overflow-y: auto;
      background: #fff;
    }

    /* Modal */
    .variant-card {
      border: 1px solid var(--border-color);
      border-radius: 12px;
      cursor: pointer;
      padding: 1rem;
      background: #fff;
      transition: 0.2s;
    }

    .variant-card:hover {
      border-color: var(--primary-color);
      background: #f8fafc;
    }
  </style>
@endsection

@section('wizard-content')
  <div class="row g-4 pb-5">

    <!-- LEFT: PRODUCT GRID -->
    <div class="col-lg-8 col-md-7">

      <!-- Search & Filter Bar -->
      <div class="card border-0 shadow-sm mb-4" style="border-radius: 1rem;">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="input-group input-group-lg border-0 mr-3 bg-light rounded-pill overflow-hidden">
            <div class="input-group-prepend">
              <span class="input-group-text bg-transparent border-0 pl-3"><i class="fas fa-search text-muted"></i></span>
            </div>
            <input type="text" class="form-control bg-transparent border-0" id="product_search"
              placeholder="Search products (Name, SKU, Barcode)...">
          </div>

          <select class="form-control form-control-lg border-0 bg-light rounded-pill" style="max-width: 250px;"
            id="category_filter">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Scrollable Grid Area -->
      <div id="product-grid" class="row">
        <!-- Products Injected Here -->
        <div class="col-12 text-center p-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Loading catalog...</p>
        </div>
      </div>
    </div>

    <!-- RIGHT: STICKY CART -->
    <div class="col-lg-4 col-md-5">
      <div class="sticky-panel">
        <!-- Header -->
        <div class="p-4 bg-dark text-white">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h5 class="m-0 font-weight-bold"><i class="fas fa-shopping-basket mr-2 text-warning"></i> Current Order</h5>
            <span class="badge badge-primary badge-pill" id="cart-count">0</span>
          </div>
          <small class="text-white-50">Draft: {{ $order->order_number ?? 'NEW' }}</small>
        </div>

        <!-- Scrollable Items -->
        <div class="cart-scroll-area p-0">
          <table class="table table-hover mb-0">
            <thead class="bg-light text-muted small sticky-top">
              <tr>
                <th class="pl-4">Item</th>
                <th class="text-center" width="70">Qty</th>
                <th class="text-right pr-4">Price</th>
                <th width="30"></th>
              </tr>
            </thead>
            <tbody id="cart-table-body">
              <!-- Cart Items -->
            </tbody>
          </table>

          <div id="empty-cart-msg" class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
            <p>Your cart is empty.</p>
          </div>
        </div>

        <!-- Footer: Totals & Actions -->
        <div class="p-4 bg-light border-top">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal</span>
            <span class="font-weight-bold text-dark" id="cart-subtotal">$0.00</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="h5 m-0 font-weight-bold">Total</span>
            <span class="h3 m-0 font-weight-bold text-primary" id="cart-total">$0.00</span>
          </div>

          <form action="{{ route('company.sales-orders.wizard.storeStep2', $order->uuid) }}" method="POST">
            @csrf
            <!-- Hidden input for JSON items if needed, but we rely on server session/saving via AJAX -->
            <button type="submit" class="btn btn-primary btn-block btn-lg shadow rounded-pill font-weight-bold py-3">
              Review & Pay <i class="fas fa-arrow-right ml-2"></i>
            </button>
            <a href="{{ route('company.sales-orders.wizard.step1', $order->uuid) }}"
              class="btn btn-link btn-block text-muted btn-sm mt-2">
              Back to Customer
            </a>
          </form>
        </div>
      </div>
    </div>

  </div>

  <!-- INLINE VARIANT MODAL (Fixed Scroll) -->
  <div class="modal fade" id="variantModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content shadow-lg border-0" style="height: 80vh; border-radius: 1.5rem; overflow: hidden;">
        <!-- Header -->
        <div class="modal-header bg-dark text-white border-0 px-4 py-3">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-cubes mr-2"></i> Product Options</h5>
          <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Body split -->
        <div class="modal-body p-0 d-flex h-100">
          <!-- Left: Static Info -->
          <div class="bg-light border-right p-5 d-flex flex-column align-items-center text-center" style="width: 350px;">
            <div class="p-3 bg-white rounded shadow-sm mb-4 d-flex align-items-center justify-content-center"
              style="width: 200px; height: 200px;">
              <img id="modal-img" src="" style="max-width: 100%; max-height: 100%;">
            </div>
            <h4 id="modal-name" class="font-weight-bold mb-2">Product Name</h4>
            <div id="modal-sku" class="badge badge-secondary px-3 py-2 mb-3">SKU-123</div>
            <p class="text-muted small">Select a variation to add to order.</p>
          </div>

          <!-- Right: Scrollable List -->
          <div class="flex-grow-1 overflow-auto p-4" id="variant-list-container">
            <div id="variant-loader" class="text-center pt-5">
              <div class="spinner-border text-primary"></div>
            </div>
            <div class="row g-3" id="variant-list"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('js')
  <script>
    const ENDPOINT_PRODUCTS = "{{ company_route('sales-orders.searchProducts') }}";
    const ENDPOINT_VARIANTS = "{{ company_route('sales-orders.getVariants', ['product' => ':id']) }}";
    const ENDPOINT_DRAFT = "{{ company_route('sales-orders.storeDraft') }}";

    let orderUuid = "{{ $order->uuid }}";
    // Persistence: Load cart from server data
    let cart = @json($order->items ?? []);

    $(document).ready(function() {
      loadProducts();
      renderCart();

      $('#product_search').on('input', _.debounce(loadProducts, 500));
      $('#category_filter').on('change', loadProducts);
    });

    // --- PRODUCT SEARCH ---
    function loadProducts() {
      let q = $('#product_search').val();
      let cat = $('#category_filter').val();

      $.get(ENDPOINT_PRODUCTS, {
        q: q,
        category_id: cat
      }, function(data) {
        let html = '';
        if (!data || data.length === 0) {
          $('#product-grid').html('<div class="col-12 text-center py-5 text-muted">No products found.</div>');
          return;
        }
        data.forEach(p => {
          html += renderProductCard(p);
        });
        $('#product-grid').html(html);
      });
    }

    function renderProductCard(item) {
      // Use single quotes for HTML attributes to avoid conflict with JSON double quotes
      // Escape item for click handler
      let safeItem = encodeURIComponent(JSON.stringify(item));
      let img = item.image || 'https://via.placeholder.com/150';

      return `
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="pos-card" onclick="openProductConfig('${safeItem}')">
                    <div class="product-img-container">
                        <img src="${img}" class="img-fluid" style="max-height: 100%; max-width: 100%;">
                    </div>
                    <div class="card-body">
                        <div class="brand-label">${item.sku || 'N/A'}</div>
                        <h6 class="product-name" title="${_.escape(item.name)}">${_.escape(item.name)}</h6>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="price-tag">$${parseFloat(item.price).toFixed(2)}</span>
                            <i class="fas fa-plus-circle text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // --- VARIANT MODAL logic ---
    function openProductConfig(encodedItem) {
      let item = JSON.parse(decodeURIComponent(encodedItem));

      // Reset Modal
      $('#modal-name').text(item.name);
      $('#modal-sku').text(item.sku);
      $('#modal-img').attr('src', item.image || 'https://via.placeholder.com/150');
      $('#variant-list').html('');
      $('#variant-loader').show();
      $('#variantModal').modal('show');

      // Fetch variants
      let url = ENDPOINT_VARIANTS.replace(':id', item.id);
      $.get(url, function(variants) {
        $('#variant-loader').hide();
        renderVariants(variants, item);
      });
    }

    function renderVariants(variants, parentProduct) {
      let html = '';
      if (!variants || variants.length === 0) {
        // Implicit single variant
        // We could just add to cart directly, but for now show "Default" option
        html =
          `<div class="col-12"><button class="btn btn-primary btn-block" onclick='addToCartAndClose(${JSON.stringify(parentProduct)}, null)'>Add Item to Order</button></div>`;
      } else {
        variants.forEach(v => {
          let vStr = encodeURIComponent(JSON.stringify(v));
          let pStr = encodeURIComponent(JSON.stringify(parentProduct));
          html += `
                    <div class="col-md-6">
                        <div class="variant-card h-100 d-flex flex-column" onclick="selectVariant('${pStr}', '${vStr}')">
                             <h6 class="font-weight-bold mb-1">${v.name}</h6>
                             <small class="text-muted d-block mb-auto">SKU: ${v.sku}</small>
                             <div class="d-flex justify-content-between align-items-center mt-3">
                                 <span class="font-weight-bold text-primary">$${parseFloat(v.price).toFixed(2)}</span>
                                 <button class="btn btn-sm btn-outline-primary rounded-pill">Add <i class="fas fa-plus"></i></button>
                             </div>
                        </div>
                    </div>
                `;
        });
      }
      $('#variant-list').html(html);
    }

    function selectVariant(encodedParent, encodedVariant) {
      let parent = JSON.parse(decodeURIComponent(encodedParent));
      let variant = JSON.parse(decodeURIComponent(encodedVariant));

      // Construct Cart Item
      let cartItem = {
        item_id: variant.id, // Ensure this maps to created_order_items.variant_id or similar
        product_id: parent.id,
        item_name: parent.name + (variant.name && variant.name !== parent.name ? ' - ' + variant.name : ''),
        item_code: variant.sku,
        quantity: 1,
        unit_price: parseFloat(variant.price),
        sub_total: parseFloat(variant.price)
      };

      // Check duplicate
      let existing = cart.find(x => x.item_id == cartItem.item_id); // Simplified check
      if (existing) {
        existing.quantity++;
        existing.sub_total = existing.quantity * existing.unit_price;
      } else {
        cart.push(cartItem);
      }

      renderCart();
      saveDraft();
      $('#variantModal').modal('hide');
      if (window.toastr) toastr.success('Added to cart');
    }

    // --- CART LOGIC ---
    function renderCart() {
      let html = '';
      let subtotal = 0;
      let count = 0;

      if (cart.length === 0) {
        $('#empty-cart-msg').show();
        $('#cart-table-body').html('');
      } else {
        $('#empty-cart-msg').hide();
        cart.forEach((item, index) => {
          subtotal += parseFloat(item.sub_total);
          count += parseInt(item.quantity);
          html += `
                    <tr>
                        <td class="pl-4 py-3">
                            <div class="font-weight-bold text-truncate" style="max-width: 140px;" title="${item.item_name}">${item.item_name}</div>
                            <small class="text-muted">${item.item_code}</small>
                        </td>
                        <td class="text-center py-3">
                            <div class="input-group input-group-sm rounded-pill overflow-hidden border">
                                <div class="input-group-prepend"><button class="btn btn-light px-2" onclick="updateQty(${index}, -1)">-</button></div>
                                <input type="text" class="form-control text-center border-0 bg-white" value="${item.quantity}" readonly style="width: 30px; height: 30px;">
                                <div class="input-group-append"><button class="btn btn-light px-2" onclick="updateQty(${index}, 1)">+</button></div>
                            </div>
                        </td>
                        <td class="text-right pr-4 py-3 font-weight-bold">$${parseFloat(item.sub_total).toFixed(2)}</td>
                        <td class="py-3"><i class="fas fa-times text-danger cursor-pointer" onclick="removeCartItem(${index})"></i></td>
                    </tr>
                `;
        });
        $('#cart-table-body').html(html);
      }

      $('#cart-subtotal').text('$' + subtotal.toFixed(2));
      $('#cart-total').text('$' + subtotal.toFixed(2));
      $('#cart-count').text(count);
    }

    function updateQty(index, delta) {
      let item = cart[index];
      item.quantity = parseInt(item.quantity) + delta;

      if (item.quantity <= 0) {
        cart.splice(index, 1);
      } else {
        item.sub_total = item.quantity * item.unit_price;
      }
      renderCart();
      saveDraft();
    }

    function removeCartItem(index) {
      cart.splice(index, 1);
      renderCart();
      saveDraft();
    }

    // --- BACKEND SYNC ---
    const saveDraft = _.debounce(function() {
      $.post(ENDPOINT_DRAFT, {
        _token: "{{ csrf_token() }}",
        uuid: orderUuid,
        items: cart
      }).done(function() {
        console.log('Draft auto-saved');
      });
    }, 1000);
  </script>
@endsection
