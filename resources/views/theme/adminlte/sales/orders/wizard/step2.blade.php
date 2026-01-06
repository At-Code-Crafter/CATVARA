@extends('theme.adminlte.sales.orders.wizard.layout')

@section('wizard-content')
  <div class="d-flex h-100">
    <!-- Main Product Area -->
    <div class="flex-grow-1 p-0 border-right bg-light" style="min-height: 70vh;">
      <!-- Toolbar -->
      <div class="glass-header d-flex justify-content-between align-items-center sticky-top" style="z-index: 10;">
        <div class="input-group input-group-lg mr-3 shadow-sm rounded-pill overflow-hidden" style="max-width: 500px;">
          <div class="input-group-prepend">
            <span class="input-group-text bg-white border-0 pl-4"><i class="fas fa-search text-muted"></i></span>
          </div>
          <input type="text" class="form-control border-0 pl-2" id="product_search" placeholder="Search products...">
        </div>

        <div class="d-flex align-items-center">
          <select class="form-control mr-2 border-0 shadow-sm" style="border-radius: 10px;" id="category_filter">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Grid -->
      <div class="p-4 overflow-auto" style="height: calc(100vh - 250px);" id="product-container">
        <div class="row g-3" id="product-grid">
          <!-- Products injected here -->
          <div class="col-12 text-center mt-5 pt-5 text-muted">
            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
            <p>Loading catalog...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar Cart -->
    <div class="d-flex flex-column bg-white shadow-lg" style="width: 380px; z-index: 20;">
      <div class="p-4 border-bottom bg-dark text-white">
        <h5 class="m-0 font-weight-bold"><i class="fas fa-shopping-basket mr-2 text-warning"></i> Current Order</h5>
        <small class="text-white-50">ORD-{{ substr($order->uuid, 0, 8) }}</small>
      </div>

      <div class="flex-grow-1 overflow-auto p-3" id="cart-items-container">
        <table class="table table-borderless table-sm mb-0">
          <tbody id="cart-table-body">
            <!-- Cart items -->
          </tbody>
        </table>
        <div id="empty-cart-msg" class="text-center text-muted mt-5">
          <i class="fas fa-ghost fa-3x mb-3 text-light"></i>
          <p>Cart is empty</p>
        </div>
      </div>

      <div class="p-4 bg-light border-top">
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Subtotal</span>
          <span class="font-weight-bold" id="cart-subtotal">$0.00</span>
        </div>
        <div class="d-flex justify-content-between mb-4">
          <span class="h5 m-0 font-weight-bold">Total</span>
          <span class="h4 m-0 font-weight-bold text-primary" id="cart-total">$0.00</span>
        </div>

        <form action="{{ route('company.sales-orders.wizard.storeStep2', $order->uuid) }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-next btn-block shadow-lg py-3">
            Review Order <i class="fas fa-check ml-2"></i>
          </button>
          <a href="{{ route('company.sales-orders.wizard.step1', $order->uuid) }}"
            class="btn btn-link btn-block text-muted btn-sm mt-2">Back to Customer</a>
        </form>
      </div>
    </div>
  </div>

  <!-- Variant Modal -->
  @include('theme.adminlte.sales.orders.wizard.variant_modal')
@endsection

@section('js')
  <script>
    const ENDPOINT_PRODUCTS = "{{ company_route('sales-orders.searchProducts') }}";

    let orderUuid = "{{ $order->uuid }}";
    let cart = @json($order->items ?? []);

    $(document).ready(function() {
      loadProducts();
      renderCart();

      $('#product_search').on('input', _.debounce(loadProducts, 400));
      $('#category_filter').on('change', loadProducts);
    });

    function loadProducts() {
      let q = $('#product_search').val();
      let cat = $('#category_filter').val();

      $.get(ENDPOINT_PRODUCTS, {
        q: q,
        category_id: cat
      }, function(data) {
        let html = '';
        if (!data || data.length === 0) {
          $('#product-grid').html('<div class="col-12 text-center p-5 text-muted">No products found</div>');
          return;
        }
        data.forEach(p => {
          html += renderProductCard(p);
        });
        $('#product-grid').html(html);
      });
    }

    function renderProductCard(item) {
      // Safe quote
      let safeName = item.name.replace(/'/g, "\\'");
      let safeItem = JSON.stringify(item).replace(/'/g, "&#39;");

      return `
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden position-relative product-card" 
                     onclick='addToCart(${safeItem})'
                     style="cursor: pointer; transition: all 0.2s;">
                    <div class="card-img-top d-flex align-items-center justify-content-center bg-white p-4" style="height: 160px;">
                        <img src="${item.image}" class="img-fluid" style="max-height: 100%; max-width: 100%;">
                    </div>
                    <div class="card-body p-3 bg-white">
                        <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.7rem;">${item.sku}</small>
                        <h6 class="card-title font-weight-bold text-dark text-truncate mb-0" style="font-size: 0.95rem;">${item.name}</h6>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                             <span class="font-weight-bold text-primary">$${parseFloat(item.price).toFixed(2)}</span>
                             <i class="fas fa-plus-circle text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Simplified Add to Cart
    function addToCart(item) {
      // Find by item_id (from DB) or product_id (from Search)
      // Adjust based on your API response structure
      let productId = item.id;

      let existing = cart.find(i => i.item_id == productId || i.product_id == productId);

      if (existing) {
        existing.quantity++;
        existing.sub_total = existing.quantity * existing.unit_price;
      } else {
        cart.push({
          item_id: productId, // For backend
          product_id: productId,
          item_name: item.name,
          item_code: item.sku,
          quantity: 1,
          unit_price: parseFloat(item.price),
          sub_total: parseFloat(item.price)
        });
      }

      renderCart();
      saveCartToBackend();

      // Toast
      if (window.toastr) toastr.success('Added ' + item.name);
    }

    function removeFromCart(index) {
      cart.splice(index, 1);
      renderCart();
      saveCartToBackend();
    }

    function updateQty(index, delta) {
      let item = cart[index];
      item.quantity += delta;
      if (item.quantity <= 0) {
        removeFromCart(index);
        return;
      }
      item.sub_total = item.quantity * item.unit_price;
      renderCart();
      saveCartToBackend();
    }

    function renderCart() {
      let html = '';
      let subtotal = 0;

      if (cart.length === 0) {
        $('#empty-cart-msg').show();
        $('#cart-table-body').html('');
      } else {
        $('#empty-cart-msg').hide();
        cart.forEach((item, index) => {
          subtotal += parseFloat(item.sub_total);
          html += `
                    <tr class="border-bottom">
                        <td class="py-2 pl-0">
                            <div class="font-weight-bold text-truncate" style="max-width: 150px;">${item.item_name}</div>
                            <small class="text-muted">${item.item_code}</small>
                        </td>
                        <td class="py-2 text-center" style="width: 80px;">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary px-1" type="button" onclick="updateQty(${index}, -1)">-</button>
                                </div>
                                <input type="text" class="form-control text-center px-0" value="${item.quantity}" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary px-1" type="button" onclick="updateQty(${index}, 1)">+</button>
                                </div>
                            </div>
                        </td>
                        <td class="py-2 text-right pr-0 font-weight-bold">
                            $${parseFloat(item.sub_total).toFixed(2)}
                        </td>
                         <td class="py-2 text-right pr-0" style="width: 20px;">
                            <i class="fas fa-times text-danger cursor-pointer" onclick="removeFromCart(${index})"></i>
                        </td>
                    </tr>
                `;
        });
        $('#cart-table-body').html(html);
      }

      $('#cart-subtotal').text('$' + subtotal.toFixed(2));
      $('#cart-total').text('$' + subtotal.toFixed(2));
    }

    function saveCartToBackend() {
      $.post("{{ route('company.sales-orders.storeDraft') }}", {
        _token: "{{ csrf_token() }}",
        uuid: orderUuid,
        items: cart
      });
    }
  </script>
@endsection
