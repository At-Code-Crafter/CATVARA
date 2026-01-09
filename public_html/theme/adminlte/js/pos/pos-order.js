(function () {
  const money = (n) => '$' + (parseFloat(n || 0).toFixed(2));

  function subtotalGross() {
    let s = 0;
    cart.forEach(i => s += (parseFloat(i.unit_price) * parseInt(i.quantity || 0)));
    return s;
  }

  function discountTotal() {
    let d = 0;
    cart.forEach(i => d += parseFloat(i.discount_amount || 0));
    return d;
  }

  function additionalTotal() {
    let a = 0;
    (additionalCharges || []).forEach(c => a += parseFloat(c.amount || 0));
    return a;
  }

  function recalc() {
    const sub = subtotalGross();
    const disc = discountTotal();
    const ship = parseFloat($('#shipping_total').val() || 0);
    const add = additionalTotal();

    const total = Math.max(0, (sub - disc) + ship + add);

    $('#cart-subtotal').text(money(sub));
    $('#cart-discount').text(money(disc));
    $('#cart-shipping').text(money(ship));
    $('#cart-additional').text(money(add));
    $('#cart-total').text(money(total));
  }

  function renderCharges() {
    let html = '';
    (additionalCharges || []).forEach((c, idx) => {
      html += `
        <div class="d-flex align-items-center mb-2">
          <input class="form-control form-control-sm mr-2" placeholder="Label"
                 value="${_.escape(c.label || '')}"
                 oninput="window.POS.updateChargeLabel(${idx}, this.value)">
          <input class="form-control form-control-sm mr-2" style="width:120px;" type="number" step="0.01"
                 value="${parseFloat(c.amount||0)}"
                 oninput="window.POS.updateChargeAmount(${idx}, this.value)">
          <button class="btn btn-sm btn-outline-danger" type="button" onclick="window.POS.removeCharge(${idx})">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      `;
    });
    $('#charge-list').html(html || '<div class="text-muted pos-mini">No additional charges.</div>');
    recalc();
  }

  function renderCart() {
    let html = '';
    let count = 0;

    if (!cart.length) {
      $('#empty-cart-msg').show();
      $('#cart-table-body').html('');
      $('#cart-count').text('0');
      recalc();
      return;
    }

    $('#empty-cart-msg').hide();

    cart.forEach((item, idx) => {
      count += parseInt(item.quantity || 0);

      html += `
        <tr>
          <td class="pl-3 py-2">
            <div class="font-weight-bold text-truncate" style="max-width:150px;" title="${_.escape(item.product_name)}">
              ${_.escape(item.product_name)}
            </div>
            <div class="text-muted pos-mini text-truncate" style="max-width:150px;">
              ${_.escape(item.variant_description || '')}
            </div>

            <div class="d-flex align-items-center mt-1">
              <small class="text-muted mr-2">Disc</small>
              <input type="number" class="form-control form-control-sm" style="width:110px;"
                     value="${parseFloat(item.discount_amount||0)}"
                     step="0.01"
                     oninput="window.POS.updateDiscount(${idx}, this.value)">
            </div>
          </td>

          <td class="text-center py-2">
            <div class="input-group input-group-sm rounded-pill overflow-hidden border">
              <div class="input-group-prepend">
                <button class="btn btn-light px-2" onclick="window.POS.updateQty(${idx}, -1)">-</button>
              </div>
              <input class="form-control text-center border-0" value="${item.quantity}" readonly style="width:40px;">
              <div class="input-group-append">
                <button class="btn btn-light px-2" onclick="window.POS.updateQty(${idx}, 1)">+</button>
              </div>
            </div>
          </td>

          <td class="text-right py-2 pr-2 font-weight-bold">
            ${money((item.unit_price * item.quantity) - (item.discount_amount || 0))}
          </td>

          <td class="py-2 pr-2">
            <i class="fas fa-times text-danger cursor-pointer" onclick="window.POS.removeItem(${idx})"></i>
          </td>
        </tr>
      `;
    });

    $('#cart-table-body').html(html);
    $('#cart-count').text(count);
    recalc();
  }

  const draftSync = _.debounce(function () {
    const payload = {
      _token: CSRF,
      items: cart,
      shipping_total: $('#shipping_total').val(),
      notes: $('#order_notes').val(),
      additional_charges: additionalCharges,
      payment_term_id: $('#payment_term_id').val() || null,
    };

    $.post(ENDPOINT_DRAFT_SYNC, payload)
      .done(function (res) {
        if (res?.order?.due_date) $('#due-date-label').text(res.order.due_date);
      })
      .fail(function (xhr) {
        console.error('Draft sync failed', xhr.responseText || xhr.statusText);
      });
  }, 700);

  // Catalog
  function loadProducts() {
    const q = $('#product_search').val();

    $.get(ENDPOINT_PRODUCTS, { q }, function (data) {
      if (!data || !data.length) {
        $('#product-grid').html('<div class="col-12 text-center py-5 text-muted">No products found.</div>');
        return;
      }

      let html = '';
      data.forEach(p => html += renderProductCard(p));
      $('#product-grid').html(html);
    });
  }

  function renderProductCard(item) {
    const safeItem = encodeURIComponent(JSON.stringify(item));
    const img = item.image || 'https://via.placeholder.com/150';

    return `
      <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
        <div class="pos-product p-2 h-100" onclick="window.POS.openProduct('${safeItem}')">
          <div class="pos-product-img">
            <img src="${img}" class="img-fluid" style="max-height:100%; max-width:100%;">
          </div>
          <div class="px-2 pb-2">
            <div class="text-muted pos-mini font-weight-bold">${_.escape(item.sku || 'N/A')}</div>
            <div class="font-weight-bold" style="min-height:40px; line-height:1.3;">
              ${_.escape(item.name || '')}
            </div>
            <div class="d-flex align-items-center justify-content-between mt-2">
              <span class="badge badge-light border">${money(item.price)}</span>
              <i class="fas fa-plus-circle text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // Variant selection
  function openProduct(encodedItem) {
    const item = JSON.parse(decodeURIComponent(encodedItem));

    $('#modal-name').text(item.name || '');
    $('#modal-sku').text(item.sku || '');
    $('#modal-img').attr('src', item.image || 'https://via.placeholder.com/150');

    $('#variant-list').html('');
    $('#variant-loader').show();
    $('#variantModal').modal('show');

    const url = ENDPOINT_VARIANTS.replace(':id', item.id);

    $.get(url, function (variants) {
      $('#variant-loader').hide();

      if (!variants || !variants.length) {
        addVariantToCart({ id: item.id, sku: item.sku, name: item.name, price: item.price }, item);
        $('#variantModal').modal('hide');
        return;
      }

      let html = '';
      variants.forEach(v => {
        const vStr = encodeURIComponent(JSON.stringify(v));
        const pStr = encodeURIComponent(JSON.stringify(item));

        html += `
          <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100 cursor-pointer"
                 onclick="window.POS.selectVariant('${pStr}','${vStr}')"
                 style="transition:.15s;">
              <div class="font-weight-bold">${_.escape(v.name || item.name || '')}</div>
              <div class="text-muted pos-mini">SKU: ${_.escape(v.sku || '')}</div>
              <div class="d-flex align-items-center justify-content-between mt-2">
                <div class="font-weight-bold text-primary">${money(v.price)}</div>
                <button class="btn btn-sm btn-outline-primary">Add</button>
              </div>
            </div>
          </div>
        `;
      });

      $('#variant-list').html(html);
    });
  }

  function addVariantToCart(variant, parent) {
    const variantId = parseInt(variant.id);
    const unit = parseFloat(variant.price || 0);

    const existing = cart.find(x => parseInt(x.product_variant_id) === variantId);

    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({
        product_variant_id: variantId,
        product_name: parent.name || variant.name || 'Item',
        variant_description: (variant.name && variant.name !== parent.name) ? variant.name : '',
        unit_price: unit,
        quantity: 1,
        discount_amount: 0,
        tax_rate: 0
      });
    }

    renderCart();
    draftSync();
  }

  // Customer picker
  function openCustomerPicker(mode) {
    $('#customerModal').data('mode', mode).modal('show');
    $('#customer_search').val('');
    $('#customer-results').html('');
  }

  function searchCustomers() {
    const q = $('#customer_search').val();

    $.get(ENDPOINT_CUSTOMERS, { q }, function (rows) {
      let html = '';

      (rows || []).forEach(c => {
        const name = c.display_name || c.name || '';
        html += `
          <button type="button" class="list-group-item list-group-item-action"
                  onclick="window.POS.pickCustomer(${c.id})">
            <div class="font-weight-bold">${_.escape(name)}</div>
            <div class="text-muted pos-mini">${_.escape(c.email || '')}</div>
          </button>
        `;
      });

      $('#customer-results').html(html || '<div class="text-muted text-center p-3">No customers found.</div>');
    });
  }

  // Public API
  window.POS = {
    openProduct,

    selectVariant: function (encodedParent, encodedVariant) {
      const parent = JSON.parse(decodeURIComponent(encodedParent));
      const variant = JSON.parse(decodeURIComponent(encodedVariant));

      addVariantToCart(variant, parent);
      $('#variantModal').modal('hide');
      if (window.toastr) toastr.success('Added to cart');
    },

    updateQty: function (index, delta) {
      const item = cart[index];
      item.quantity = parseInt(item.quantity || 0) + delta;

      if (item.quantity <= 0) cart.splice(index, 1);

      renderCart();
      draftSync();
    },

    removeItem: function (index) {
      cart.splice(index, 1);
      renderCart();
      draftSync();
    },

    updateDiscount: function (index, val) {
      const item = cart[index];
      item.discount_amount = Math.max(0, parseFloat(val || 0));
      renderCart();
      draftSync();
    },

    // Charges
    addCharge: function () {
      additionalCharges = additionalCharges || [];
      additionalCharges.push({ label: 'Charge', amount: 0 });
      renderCharges();
      draftSync();
    },

    updateChargeLabel: function (idx, val) {
      additionalCharges[idx].label = val;
      renderCharges();
      draftSync();
    },

    updateChargeAmount: function (idx, val) {
      additionalCharges[idx].amount = Math.max(0, parseFloat(val || 0));
      renderCharges();
      draftSync();
    },

    removeCharge: function (idx) {
      additionalCharges.splice(idx, 1);
      renderCharges();
      draftSync();
    },

    pickCustomer: function (customerId) {
      const mode = $('#customerModal').data('mode');

      if (mode === 'sell_to') {
        $.post(ENDPOINT_DRAFT_SELL_TO, { _token: CSRF, customer_id: customerId })
          .done(function (res) {
            if (res?.due_date) $('#due-date-label').text(res.due_date);
            if (window.toastr) toastr.success('Sell To updated');
            $('#customerModal').modal('hide');
            draftSync();
          });
      } else {
        $.post(ENDPOINT_DRAFT_BILL_TO, { _token: CSRF, bill_to_customer_id: customerId })
          .done(function () {
            if (window.toastr) toastr.success('Bill To updated');
            $('#customerModal').modal('hide');
            draftSync();
          });
      }
    }
  };

  // Bindings
  $(document).ready(function () {
    loadProducts();
    renderCart();
    renderCharges();

    $('#btnReloadProducts').on('click', loadProducts);
    $('#product_search').on('input', _.debounce(loadProducts, 350));

    $('#shipping_total').on('input', _.debounce(function () {
      recalc();
      draftSync();
    }, 350));

    $('#order_notes').on('input', _.debounce(draftSync, 600));

    $('#payment_term_id').on('change', function () {
      $.post(ENDPOINT_DRAFT_TERM, { _token: CSRF, payment_term_id: $(this).val() || null })
        .done(function (res) {
          if (res?.due_date) $('#due-date-label').text(res.due_date);
          draftSync();
        });
    });

    $('#btnPickSellTo').on('click', function () { openCustomerPicker('sell_to'); });
    $('#btnPickBillTo').on('click', function () { openCustomerPicker('bill_to'); });

    $('#customer_search').on('input', _.debounce(searchCustomers, 250));

    $('#btnAddCharge').on('click', function () {
      window.POS.addCharge();
    });
  });

})();
