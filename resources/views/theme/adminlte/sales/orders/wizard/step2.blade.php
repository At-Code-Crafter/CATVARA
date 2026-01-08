@extends('theme.adminlte.sales.orders.wizard.layout')

@section('wizard-content')
  <div class="row g-4 pb-5">
    <div class="col-lg-8 col-md-7">
      <div class="panel-card mb-4">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="input-group input-group-lg border-0 mr-3 bg-light rounded-pill overflow-hidden" style="flex:1;">
            <div class="input-group-prepend">
              <span class="input-group-text bg-transparent border-0 pl-3">
                <i class="fas fa-search text-muted"></i>
              </span>
            </div>
            <input type="text" class="form-control bg-transparent border-0" id="product_search"
                   placeholder="Search products (Name, SKU, Barcode)...">
          </div>

          <select class="form-control form-control-lg border-0 bg-light rounded-pill"
                  style="max-width: 260px;" id="category_filter">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div id="product-grid" class="row">
        <div class="col-12 text-center p-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Loading catalog...</p>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-5">
      <div class="panel-card" style="position:sticky; top:20px;">
        <div class="panel-header">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h5 class="m-0 font-weight-bold">
              <i class="fas fa-shopping-basket mr-2 text-warning"></i> Current Order
            </h5>
            <span class="badge badge-primary badge-pill px-3 py-2" id="cart-count">0</span>
          </div>
          <small class="panel-subtitle">Draft: {{ $order->order_number ?? 'NEW' }}</small>
        </div>

        <div style="max-height: 55vh; overflow:auto;">
          <table class="table table-hover mb-0">
            <thead class="bg-light text-muted small sticky-top">
              <tr>
                <th class="pl-4">Item</th>
                <th class="text-center" width="70">Qty</th>
                <th class="text-right pr-4" width="90">Total</th>
                <th width="30"></th>
              </tr>
            </thead>
            <tbody id="cart-items"></tbody>
          </table>

          <div id="empty-cart-msg" class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
            <p class="mb-0">Your cart is empty.</p>
          </div>
        </div>

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
            <button type="submit" class="btn btn-primary btn-block btn-lg shadow rounded-pill font-weight-bold py-3"
                    id="btnProceed" disabled>
              Review & Finalize <i class="fas fa-arrow-right ml-2"></i>
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

  {{-- Variant Modal --}}
  <div class="modal fade" id="variantModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content shadow-lg border-0" style="height: 80vh; border-radius: 1.5rem; overflow: hidden;">
        <div class="modal-header bg-dark text-white border-0 px-4 py-3">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-cubes mr-2"></i> Product Options</h5>
          <button type="button" class="close text-white opacity-100" data-dismiss="modal">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body p-0 d-flex h-100">
          <div class="bg-light border-right p-5 d-flex flex-column align-items-center text-center" style="width: 350px;">
            <div class="p-3 bg-white rounded shadow-sm mb-4 d-flex align-items-center justify-content-center"
                 style="width:200px;height:200px;">
              <img id="modal-img" src="" style="max-width:100%;max-height:100%;object-fit:contain;">
            </div>
            <h4 id="modal-name" class="font-weight-bold mb-2"></h4>
            <div id="modal-sku" class="badge badge-secondary px-3 py-2 mb-3"></div>
            <p class="text-muted small">Select a variation to add to order.</p>
          </div>

          <div class="flex-grow-1 overflow-auto p-4" style="background:#fff;">
            <div id="variant-loader" class="text-center pt-5">
              <div class="spinner-border text-primary"></div>
              <div class="text-muted mt-2">Loading variations...</div>
            </div>
            <div class="row g-3" id="variant-list"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  const ENDPOINT_PRODUCTS = "{{ company_route('sales-orders.searchProducts') }}";
  const ENDPOINT_VARIANTS = "{{ company_route('sales-orders.getVariants', ['product' => '__ID__']) }}";
  const ENDPOINT_DRAFT = "{{ company_route('sales-orders.storeDraft') }}";

  const orderUuid = "{{ $order->uuid }}";
  const customerId = "{{ $order->customer_id }}";

  function money(n){ n = parseFloat(n || 0); return '$' + n.toFixed(2); }

  // Normalize existing order items (DB) -> orderState.items for draft endpoint
  const orderState = {
    items: @json(($order->items ?? collect())->map(function($i){
      return [
        'id' => $i->product_variant_id,
        'name' => $i->product_name,
        'sku' => $i->item_code ?? '',
        'price' => (float) $i->unit_price,
        'qty' => (int) $i->quantity,
        'discount' => (float) ($i->discount_amount ?? 0),
      ];
    })->values())
  };

  function computeTotals(){
    let subtotal = 0;
    let discount = 0;
    orderState.items.forEach(i => {
      subtotal += (parseFloat(i.price)||0) * (parseInt(i.qty)||0);
      discount += (parseFloat(i.discount)||0);
    });
    const taxable = Math.max(0, subtotal - discount);
    return { subtotal, discount, total: taxable };
  }

  function toggleProceed(){
    const disabled = orderState.items.length === 0;
    $('#btnProceed').prop('disabled', disabled);
  }

  function renderCart(){
    const $tbody = $('#cart-items');
    const totals = computeTotals();

    if(orderState.items.length === 0){
      $('#empty-cart-msg').show();
      $tbody.html('');
      $('#cart-count').text('0');
      $('#cart-subtotal').text(money(0));
      $('#cart-total').text(money(0));
      toggleProceed();
      return;
    }

    $('#empty-cart-msg').hide();

    let html = '';
    let count = 0;

    orderState.items.forEach((item, idx) => {
      count += parseInt(item.qty || 0);
      const lineTotal = Math.max(0, (item.price * item.qty) - (item.discount || 0));

      html += `
        <tr>
          <td class="pl-4 py-3">
            <div class="font-weight-bold text-truncate" style="max-width: 160px;" title="${_.escape(item.name||'')}">${_.escape(item.name||'')}</div>
            <small class="text-muted">${_.escape(item.sku||'')}</small>
          </td>
          <td class="text-center py-3">
            <div class="input-group input-group-sm rounded-pill overflow-hidden border" style="width: 92px; margin:0 auto;">
              <div class="input-group-prepend">
                <button class="btn btn-light px-2 js-qty" data-idx="${idx}" data-delta="-1">-</button>
              </div>
              <input type="text" class="form-control text-center border-0 bg-white" value="${item.qty}" readonly>
              <div class="input-group-append">
                <button class="btn btn-light px-2 js-qty" data-idx="${idx}" data-delta="1">+</button>
              </div>
            </div>
          </td>
          <td class="text-right pr-4 py-3 font-weight-bold">${money(lineTotal)}</td>
          <td class="py-3">
            <i class="fas fa-times text-danger" style="cursor:pointer;" onclick="removeItem(${idx})"></i>
          </td>
        </tr>
      `;
    });

    $tbody.html(html);
    $('#cart-count').text(count);
    $('#cart-subtotal').text(money(totals.total));
    $('#cart-total').text(money(totals.total));
    toggleProceed();
  }

  function removeItem(idx){
    orderState.items.splice(idx, 1);
    renderCart();
    saveDraft();
  }

  // --- Products grid ---
  function renderProductCard(p){
    const safe = encodeURIComponent(JSON.stringify(p));
    const img = p.image || 'https://placehold.co/300x300?text=Product';

    // NOTE: your searchProducts endpoint currently does NOT return price; keep UI as "View"
    return `
      <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
        <div class="card pos-card" style="border-radius: 16px; border: 1px solid #e2e8f0; cursor:pointer;"
             onclick="openProductConfig('${safe}')">
          <div class="product-img-container" style="height:160px;display:flex;align-items:center;justify-content:center;padding:12px;">
            <img src="${img}" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit:contain;">
          </div>
          <div class="card-body">
            <div class="text-muted small font-weight-bold" style="letter-spacing:.5px;">SKU: ${_.escape(p.sku || '')}</div>
            <h6 class="font-weight-bold mt-1 mb-0" style="min-height:40px;">${_.escape(p.name || '')}</h6>
            <div class="d-flex justify-content-between align-items-center mt-3">
              <span class="badge badge-primary px-3 py-2">View</span>
              <i class="fas fa-plus-circle text-primary fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function loadProducts(){
    const q = $('#product_search').val();
    const cat = $('#category_filter').val();

    $.get(ENDPOINT_PRODUCTS, { q, category_id: cat }, function(data){
      if(!data || data.length === 0){
        $('#product-grid').html('<div class="col-12 text-center py-5 text-muted">No products found.</div>');
        return;
      }
      $('#product-grid').html(data.map(renderProductCard).join(''));
    });
  }

  // --- Variants modal ---
  function openProductConfig(encoded){
    const p = JSON.parse(decodeURIComponent(encoded));

    $('#modal-name').text(p.name || '');
    $('#modal-sku').text(p.sku || '');
    $('#modal-img').attr('src', p.image || 'https://placehold.co/300x300?text=Product');

    $('#variant-list').html('');
    $('#variant-loader').show();
    $('#variantModal').modal('show');

    const url = ENDPOINT_VARIANTS.replace('__ID__', p.id);

    $.get(url, function(variants){
      $('#variant-loader').hide();

      if(!variants || variants.length === 0){
        $('#variant-list').html('<div class="col-12 text-muted text-center py-5">No variants available.</div>');
        return;
      }

      const html = variants.map(v => {
        const vEnc = encodeURIComponent(JSON.stringify(v));
        return `
          <div class="col-md-6 mb-3">
            <div class="variant-card" style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;cursor:pointer;"
                 onclick="selectVariant('${vEnc}')">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="font-weight-bold">${_.escape(v.name || '')}</div>
                  <div class="small text-muted">SKU: ${_.escape(v.sku || '')}</div>
                </div>
                <div class="text-right">
                  <div class="font-weight-bold text-primary">${money(v.price)}</div>
                  <div class="small text-muted">Click to add</div>
                </div>
              </div>
            </div>
          </div>
        `;
      }).join('');

      $('#variant-list').html(html);
    }).fail(function(){
      $('#variant-loader').hide();
      $('#variant-list').html('<div class="col-12 text-danger">Failed to load variants.</div>');
    });
  }

  function selectVariant(vEncoded){
    const v = JSON.parse(decodeURIComponent(vEncoded));
    if(!v || !v.id) return;

    const existing = orderState.items.find(x => String(x.id) === String(v.id));
    if(existing){
      existing.qty = (parseInt(existing.qty)||0) + 1;
    }else{
      orderState.items.push({
        id: v.id,
        name: v.name || 'Item',
        sku: v.sku || '',
        price: parseFloat(v.price) || 0,
        qty: 1,
        discount: 0
      });
    }

    $('#variantModal').modal('hide');
    renderCart();
    saveDraft();
  }

  // --- Draft sync (matches YOUR controller storeDraft()) ---
  const saveDraft = _.debounce(function(){
    $.post(ENDPOINT_DRAFT, {
      _token: "{{ csrf_token() }}",
      order_uuid: orderUuid,
      customer_id: customerId,
      items: orderState.items.map(i => ({
        variant_id: i.id,
        qty: i.qty,
        price: i.price,
        name: i.name,
        discount: i.discount || 0
      }))
    });
  }, 600);

  // Qty buttons
  $(document).on('click', '.js-qty', function(){
    const idx = parseInt($(this).data('idx'));
    const delta = parseInt($(this).data('delta'));
    if(!orderState.items[idx]) return;

    let q = (parseInt(orderState.items[idx].qty)||1) + delta;
    if(q <= 0){
      orderState.items.splice(idx, 1);
    }else{
      orderState.items[idx].qty = q;
    }
    renderCart();
    saveDraft();
  });

  $(document).ready(function(){
    loadProducts();
    renderCart();

    $('#product_search').on('input', _.debounce(loadProducts, 350));
    $('#category_filter').on('change', loadProducts);
  });
</script>
@endpush
