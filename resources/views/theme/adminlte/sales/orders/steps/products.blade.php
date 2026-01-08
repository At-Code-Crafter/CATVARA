@extends('theme.adminlte.layouts.app')

@section('title', 'New Sales Order - Products')

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8">
      <h1 class="m-0">
        <i class="fas fa-file-invoice mr-2 text-primary"></i> New Sales Order
      </h1>
      <div class="text-muted">Step 2: Add Products</div>
    </div>
  </div>
@endsection

@section('content')
  <div class="container-fluid pos-shell">

    <div class="card ent-card">
      {{-- WIZARD HEADER --}}
      <div class="card-header p-0 pt-3 border-bottom-0">
        <ul class="nav nav-tabs pos-steps" role="tablist">
          <li class="nav-item">
            <a class="nav-link" href="{{ company_route('sales.orders.create') }}">
              <i class="fas fa-user mr-2"></i> 1. Customer
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="#">
              <i class="fas fa-cubes mr-2"></i> 2. Products
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="#">
              <i class="fas fa-file-invoice-dollar mr-2"></i> 3. Terms
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="#">
              <i class="fas fa-check-circle mr-2"></i> 4. Preview
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body p-4">
        <div class="row">

          {{-- PRODUCT SECTION --}}
          <div class="col-md-8">
            <div class="card ent-card shadow-sm">
              <div class="card-body">
                {{-- FILTERS --}}
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
                      {{-- Categories loading --}}
                    </select>
                  </div>
                </div>

                {{-- GRID --}}
                <div id="product-grid" class="row pos-grid"
                  style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                  <div class="col-12 text-center py-5"><span class="spinner-border text-primary"></span></div>
                </div>
              </div>
            </div>
          </div>

          {{-- CART SECTION --}}
          <div class="col-md-4">
            <div class="sticky-cart">
              <div class="card cart-panel">
                <div class="cart-header d-flex justify-content-between align-items-center">
                  <div>
                    <div class="font-weight-bold">
                      <i class="fas fa-shopping-basket mr-2 text-warning"></i> Current Order
                    </div>
                    <small class="text-white-50">Draft #{{ $order->order_number }}</small>
                  </div>
                  <span class="badge badge-primary badge-pill px-3 py-2" id="cart-count">{{ $order->items->count() }}
                    Items</span>
                </div>

                <div class="card-body p-0">
                  <div class="cart-table-wrapper" style="height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                      <thead class="bg-light sticky-top" style="z-index: 5;">
                        <tr class="text-secondary small font-weight-bold text-uppercase">
                          <th class="pl-3">Item</th>
                          <th class="text-center" style="width: 60px;">Qty</th>
                          <th class="text-right pr-3">Total</th>
                          <th style="width: 30px;"></th>
                        </tr>
                      </thead>
                      <tbody id="cart-items">
                        {{-- Initial Cart Load --}}
                        @include('theme.adminlte.sales.orders.partials.cart_items', ['order' => $order])
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="cart-summary p-3 bg-light border-top">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="font-weight-bold text-dark"
                      id="cart-subtotal">{{ number_format($order->subtotal, 2) }}</span>
                  </div>
                  <div class="d-flex justify-content-between h4 mb-3 align-items-center">
                    <span class="font-weight-bold text-dark">Total</span>
                    <span class="text-primary font-weight-bolder"
                      id="cart-total">{{ number_format($order->total, 2) }}</span>
                  </div>

                  <a href="{{ company_route('sales.orders.billing', ['order' => $order->uuid]) }}"
                    class="btn btn-primary btn-ent btn-block py-3">
                    Proceed to Billing <i class="fas fa-arrow-right ml-2"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  {{-- VARIANT MODAL --}}
  <div class="modal fade" id="variantModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Select Options</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-5 text-center">
              <div id="modal-product-image" class="bg-light rounded d-flex align-items-center justify-content-center"
                style="height:200px;">
                <i class="fas fa-image fa-3x text-muted"></i>
              </div>
              <h4 class="mt-3 font-weight-bold" id="modal-product-name">Product Name</h4>
              <p class="text-muted" id="modal-product-sku">SKU: 12345</p>
            </div>
            <div class="col-md-7">
              <form id="add-to-cart-form">
                <input type="hidden" id="modal-product-id" name="product_id">
                <div class="form-group">
                  <label class="font-weight-bold">Variant</label>
                  <select class="form-control" id="modal-variant-select" name="variant_id"></select>
                </div>

                <div class="form-group">
                  <label class="font-weight-bold">Price</label>
                  <input type="number" class="form-control" id="modal-price" name="price" step="0.01">
                </div>

                <div class="form-group">
                  <label class="font-weight-bold">Quantity</label>
                  <div class="input-group" style="width: 150px;">
                    <div class="input-group-prepend">
                      <button class="btn btn-outline-secondary" type="button" onclick="adjQty(-1)">-</button>
                    </div>
                    <input type="number" class="form-control text-center" id="modal-qty" name="quantity"
                      value="1" min="1">
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" onclick="adjQty(1)">+</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary px-4" id="btn-add-confirm">
            <i class="fas fa-cart-plus mr-1"></i> Add to Order
          </button>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    var orderUuid = "{{ $order->uuid }}";
    var cartAddUrl = "{{ company_route('sales.orders.cart.add', ['order' => $order->uuid]) }}";
    var searchUrl = "{{ company_route('sales.orders.search.products') }}";

    $(document).ready(function() {
      loadProducts();

      $('#product_search').on('keyup', _.debounce(function() {
        loadProducts($(this).val());
      }, 500));

      // Add to Cart Logic
      $('#btn-add-confirm').click(function() {
        var data = {
          product_id: $('#modal-product-id').val(),
          variant_id: $('#modal-variant-select').val(),
          price: $('#modal-price').val(),
          quantity: $('#modal-qty').val(),
          _token: "{{ csrf_token() }}"
        };

        $.post(cartAddUrl, data, function(res) {
          if (res.status == 'success') {
            $('#variantModal').modal('hide');
            updateCartUI(res);
            toastr.success('Item added to cart');
          }
        });
      });

      // Remove Item
      $(document).on('click', '.remove-item', function() {
        if (!confirm('Remove item?')) return;
        var lineId = $(this).data('id');
        var url =
          "{{ company_route('sales.orders.cart.remove', ['order' => $order->uuid, 'lineId' => ':id']) }}";
        url = url.replace(':id', lineId);

        $.post(url, {
          _token: "{{ csrf_token() }}"
        }, function(res) {
          updateCartUI(res);
        });
      });

      // Update Qty
      $(document).on('change', '.cart-qty', function() {
        var lineId = $(this).data('id');
        var qty = $(this).val();
        if (qty < 1) return;

        var url =
        "{{ company_route('sales.orders.cart.update', ['order' => $order->uuid, 'lineId' => ':id']) }}";
        url = url.replace(':id', lineId);

        $.post(url, {
          quantity: qty,
          _token: "{{ csrf_token() }}"
        }, function(res) {
          updateCartUI(res);
        });
      });
    });

    function loadProducts(term = '') {
      $.get(searchUrl, {
        term: term
      }, function(res) {
        $('#product-grid').html(res.view);
      });
    }

    function openProductModal(id, name, sku, variants) {
      $('#modal-product-id').val(id);
      $('#modal-product-name').text(name);
      $('#modal-product-sku').text(sku || '');
      $('#modal-qty').val(1);

      var select = $('#modal-variant-select');
      select.empty();

      // In real app, variants should be passed or loaded. 
      // Assuming variants is an array
      if (variants && variants.length) {
        variants.forEach(function(v) {
          select.append(new Option(v.name + ' - ' + v.price, v.id, false, false));
          // Store price in data
          select.find('option:last').data('price', v.price);
        });
        $('#modal-price').val(variants[0].price);
      }

      $('#variantModal').modal('show');
    }

    // On Variant Change, update price
    $('#modal-variant-select').change(function() {
      var price = $(this).find(':selected').data('price');
      $('#modal-price').val(price);
    });

    function adjQty(delta) {
      var v = parseInt($('#modal-qty').val()) || 0;
      v += delta;
      if (v < 1) v = 1;
      $('#modal-qty').val(v);
    }

    function updateCartUI(res) {
      $('#cart-items').html(res.cart_html);
      $('#cart-subtotal').text(res.subtotal);
      $('#cart-total').text(res.total);
      $('#cart-count').text(res.count);
    }
  </script>
@endpush
