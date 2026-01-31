@extends('catvara.layouts.app')

@section('title', 'Finalize Sales Order: ' . $order->order_number)

@section('content')
  <style>
    @keyframes fadeInSlide {
      from {
        opacity: 0;
        transform: translateY(10px)
      }

      to {
        opacity: 1;
        transform: translateY(0)
      }
    }

    .animate-entry {
      animation: fadeInSlide .35s ease-out forwards;
      opacity: 0;
    }

    .scrollable {
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #e2e8f0 transparent;
    }

    .scrollable::-webkit-scrollbar {
      width: 4px;
    }

    .scrollable::-webkit-scrollbar-thumb {
      background: #e2e8f0;
      border-radius: 999px;
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

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
      <div class="flex items-center gap-2">
        <a href="{{ company_route('sales-orders.edit', ['sales_order' => $order->uuid]) }}"
          class="h-9 w-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-brand-600 hover:border-brand-200 hover:shadow-sm transition-all">
          <i class="fas fa-arrow-left text-sm"></i>
        </a>

        <div class="flex flex-col">
          <div class="flex items-center gap-2">
            <span
              class="px-2 py-0.5 rounded-[6px] bg-brand-50 text-brand-700 border border-brand-100 text-[10px] font-black uppercase tracking-widest">
              Step 03 / 03
            </span>
            <span class="text-xs font-black text-slate-900">Finalize</span>
          </div>
          <div class="text-[11px] font-bold text-slate-500">
            Order: <span class="text-slate-800 font-black">{{ $order->order_number }}</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ company_route('sales-orders.create', ['edit_order' => $order->uuid]) }}"
          class="h-10 px-3 rounded-xl bg-white border border-slate-200 text-slate-600 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition flex items-center gap-2">
          <i class="fas fa-pen"></i> Edit Customer
        </a>

        <a href="{{ company_route('sales-orders.index') }}"
          class="h-10 px-3 rounded-xl bg-white border border-slate-200 text-slate-600 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition flex items-center gap-2">
          <i class="fas fa-list"></i> Orders
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 flex-1 min-h-0">

      {{-- LEFT: Final checks --}}
      <div
        class="lg:col-span-5 flex flex-col min-h-0 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="p-4 border-b border-slate-100 bg-slate-50/30">
          <div class="text-[11px] font-black text-slate-600 uppercase tracking-widest">Customer & Payment</div>
          <div class="text-[12px] font-bold text-slate-500 mt-1">Verify before confirming.</div>
        </div>

        <div class="scrollable p-4 space-y-4 min-h-0 flex-1">

          {{-- Customer chips --}}
          <div class="grid grid-cols-1 gap-2">
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/40">
              <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center">
                <i class="fas fa-receipt text-[12px]"></i>
              </div>
              <div class="min-w-0">
                <div class="text-[11px] font-black text-slate-800 truncate">
                  {{ $order->customer_name ?? ($billToCustomer->display_name ?? '-') }}</div>
                <div class="text-[10px] font-bold text-slate-500 truncate">{{ $billAddress ?: '-' }}</div>
              </div>
            </div>

            <div class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/40">
              <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                <i class="fas fa-user-tag text-[12px]"></i>
              </div>
              <div class="min-w-0">
                <div class="text-[11px] font-black text-slate-800 truncate">
                  {{ $order->shipping_customer_name ?? ($shipToCustomer->display_name ?? '-') }}</div>
                <div class="text-[10px] font-bold text-slate-500 truncate">{{ $sellAddress ?: '-' }}</div>
              </div>
            </div>
          </div>

          {{-- Notes --}}
          <div>
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Notes</label>
            <textarea id="finalNotes" rows="3"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-[12px] font-bold focus:border-brand-400 focus:ring-0 resize-none"
              placeholder="Internal / customer notes..."></textarea>
          </div>

          {{-- Payment term + due date --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Payment Terms</label>
              <select id="finalPaymentTerm"
                class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-[12px] font-black focus:ring-0 bg-white">
                <option value="">Select...</option>
              </select>
            </div>

            <div>
              <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Due Date</label>
              <input id="finalDueDate" readonly
                class="mt-1 w-full h-10 rounded-xl border border-slate-200 text-[12px] font-black bg-slate-50 text-slate-700 text-center"
                placeholder="-">
            </div>
          </div>

          {{-- Payment method (shown only if due_days=0) --}}
          <div id="finalPaymentMethodWrap" class="hidden">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Payment Method (Due
              Now)</label>
            <select id="finalPaymentMethod"
              class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-[12px] font-black focus:ring-0 bg-white">
              <option value="">Select...</option>
            </select>
            <p class="text-[11px] font-bold text-slate-400 mt-1">Required only when terms are Due Now.</p>
          </div>

          {{-- Shipping / VAT / Currency (optional to keep editable) --}}
          <div class="grid grid-cols-3 gap-3 bg-slate-50/40 p-3 rounded-xl border border-slate-200">
            <div>
              <label class="text-[10px] font-black text-slate-500 uppercase">Ship</label>
              <input type="number" id="finalShipping" min="0" step="0.01"
                class="mt-1 w-full h-10 rounded-xl border border-slate-200 bg-white text-center text-[12px] font-black">
            </div>
            <div>
              <label class="text-[10px] font-black text-slate-500 uppercase">Tax Def</label>
              <select id="finalTaxGroup"
                class="mt-1 w-full h-10 rounded-xl border border-slate-200 bg-white px-2 text-[12px] font-black">
                <option value="">No Tax</option>
                @foreach ($taxGroups as $tg)
                  <option value="{{ $tg->id }}">{{ $tg->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="text-[10px] font-black text-slate-500 uppercase">Cur</label>
              <select id="finalCurrency"
                class="mt-1 w-full h-10 rounded-xl border border-slate-200 bg-white px-2 text-[12px] font-black">
                @foreach ($enabledCurrencies as $cur)
                  <option value="{{ $cur->code }}">{{ $cur->code }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-span-1">
              <label class="text-[10px] font-black text-slate-500 uppercase">Disc %</label>
              <input type="number" id="finalGlobalDiscountPercent" min="0" max="100" step="0.01"
                class="mt-1 w-full h-10 rounded-xl border border-slate-200 bg-white text-center text-[12px] font-black">
            </div>
            <div class="col-span-2">
              <label class="text-[10px] font-black text-slate-500 uppercase">Disc Amt</label>
              <input type="number" id="finalGlobalDiscountAmount" min="0" step="0.01"
                class="mt-1 w-full h-10 rounded-xl border border-slate-200 bg-white text-center text-[12px] font-black">
            </div>
          </div>

          {{-- Validation warnings --}}
          <div id="finalWarnings" class="hidden p-3 rounded-xl border border-red-200 bg-red-50 text-red-700">
            <div class="text-[11px] font-black uppercase tracking-widest">Missing Required Info</div>
            <ul class="mt-2 text-[12px] font-bold list-disc pl-5 space-y-1" id="finalWarningsList"></ul>
          </div>

        </div>

        {{-- Action bar --}}
        <div class="p-3 border-t border-slate-100 bg-white flex items-center gap-2">
          <a href="{{ company_route('sales-orders.edit-basket', ['sales_order' => $order->uuid]) }}"
            class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
          </a>

          <button type="button" onclick="finalSaveDraft()"
            class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fas fa-save"></i> Save Draft
          </button>

          <button type="button" id="finalConfirmBtn" onclick="finalConfirmOrder()"
            class="ml-auto h-10 px-4 rounded-xl bg-brand-600 text-white text-[11px] font-black uppercase tracking-widest hover:bg-brand-700 transition flex items-center gap-2 disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed">
            <i class="fas fa-check"></i> Finalize & Confirm
          </button>
        </div>
      </div>

      {{-- RIGHT: Preview --}}
      <div
        class="lg:col-span-7 flex flex-col min-h-0 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="p-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
          <div>
            <div class="text-[11px] font-black text-slate-600 uppercase tracking-widest">Order Preview</div>
            <div class="text-[12px] font-bold text-slate-500 mt-1" id="previewLineCount">0 Lines • 0 Qty</div>
          </div>
          <button type="button" onclick="window.print()"
            class="h-10 px-3 rounded-xl bg-white border border-slate-200 text-slate-700 text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fas fa-print"></i> Print
          </button>
        </div>

        <div class="scrollable p-4 min-h-0 flex-1">
          <div class="rounded-xl border border-slate-200 overflow-hidden">
            <div
              class="grid grid-cols-12 bg-slate-50 px-3 py-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">
              <div class="col-span-5">Item</div>
              <div class="col-span-2 text-center">Qty</div>
              <div class="col-span-2 text-right">Price</div>
              <div class="col-span-1 text-center">Disc</div>
              <div class="col-span-2 text-right">Total</div>
            </div>
            <div id="previewItems"></div>
          </div>

          <div class="mt-4 bg-slate-50/40 border border-slate-200 rounded-xl p-4 space-y-2">
            <div class="flex justify-between text-[12px] font-bold text-slate-600">
              <span>Items Subtotal</span> <span id="pvSubtotal" class="text-slate-900">0.00</span>
            </div>
            <div class="flex justify-between text-[11px] font-bold text-slate-500">
              <span>Line Discounts</span> <span id="pvLineDiscount" class="text-rose-500">-0.00</span>
            </div>
            <div class="flex justify-between text-[11px] font-bold text-slate-500">
              <span>Global Discount</span> <span id="pvGlobalDiscount" class="text-rose-600">-0.00</span>
            </div>
            <div class="flex justify-between text-[12px] font-bold text-slate-600">
              <span>Shipping</span> <span id="pvShipping" class="text-slate-900">0.00</span>
            </div>
            <div class="flex justify-between text-[12px] font-bold text-slate-600">
              <span>Tax Total</span> <span id="pvTax" class="text-slate-900">0.00</span>
            </div>
            <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
              <span class="text-[12px] font-black uppercase tracking-widest text-brand-600">Grand Total</span>
              <span class="text-2xl font-black text-brand-600" id="pvGrand">0.00</span>
            </div>
          </div>

        </div>

      </div>

    </div>

    <input type="hidden" id="orderUuid" value="{{ $order->uuid }}">
  </div>
@endsection

@push('scripts')
  <script>
    const paymentTermsUrl = "{{ company_route('load-payment-terms') }}";
    const paymentMethodsUrl = "{{ company_route('load-payment-methods') }}";
    const finalizeStoreUrl = "{{ company_route('sales-orders.finalize.store', ['sales_order' => $order->uuid]) }}";
    const updateUrl = "{{ company_route('sales-orders.update', ['sales_order' => $order->uuid]) }}";

    const initialState = @json($initialState ?? null);
    const currentOrder = @json($order);

    let paymentTerms = [];
    let paymentMethods = [];
    let cart = [];

    // Build cart from draft_state (preferred) or from order items
    if (initialState && initialState.items) {
      cart = initialState.items.map(i => ({
        type: i.type || 'variant',
        variant_id: i.variant_id || i.variantId || null,
        custom_name: i.custom_name || i.customName || null,
        custom_sku: i.custom_sku || i.customSku || null,
        qty: parseFloat(i.qty || 1),
        unit_price: parseFloat(i.unit_price ?? i.unitPrice ?? 0),
        discount_percent: parseFloat(i.discount_percent ?? i.discountPercent ?? 0),
        // For preview only:
        display_name: i.display_name || i.custom_name || i.name || null,
        attrs_text: i.attrs_text || null,
      }));
    } else if (currentOrder.items && currentOrder.items.length) {
      cart = currentOrder.items.map(it => {
        const isCustom = !!it.is_custom;
        return {
          type: isCustom ? 'custom' : 'variant',
          variant_id: it.product_variant?.uuid || it.product_variant_id || null,
          custom_name: isCustom ? it.product_name : null,
          custom_sku: isCustom ? it.custom_sku : null,
          qty: parseFloat(it.quantity || 1),
          unit_price: parseFloat(it.unit_price || 0),
          discount_percent: parseFloat(it.discount_percent || 0),
          display_name: it.product_name || (it.product_variant?.product?.name) || 'Item',
          attrs_text: it.variant_description || (it.product_variant?.attrs ? Object.values(it.product_variant.attrs)
            .join(' / ') : ''),
        };
      });
    }

    $(document).ready(function() {
      // Hydrate fields from state
      $('#finalNotes').val((initialState && initialState.notes) ? initialState.notes : (currentOrder.notes || ''));

      $('#finalShipping').val((initialState && initialState.shipping != null) ? initialState.shipping : (currentOrder
        .shipping_total || 0));
      $('#finalTaxGroup').val(currentOrder.tax_group_id || '');
      $('#finalGlobalDiscountPercent').val((initialState && initialState.global_discount_percent != null) ?
        initialState.global_discount_percent : (currentOrder.global_discount_percent || 0));
      $('#finalGlobalDiscountAmount').val((initialState && initialState.global_discount_amount != null) ? initialState
        .global_discount_amount : (currentOrder.global_discount_amount || 0));

      const cur = (initialState && initialState.currency) ? initialState.currency : (currentOrder.currency?.code ||
        'AED');
      $('#finalCurrency').val(cur);
      if (!$('#finalCurrency').val()) $('#finalCurrency').val($('#finalCurrency option:first').val());

      loadPaymentTerms();
      loadPaymentMethods();

      // Re-render preview on final change
      $('#finalShipping, #finalTaxGroup, #finalCurrency, #finalGlobalDiscountPercent, #finalGlobalDiscountAmount').on(
        'input change',
        function() {
          renderPreview();
          validateFinalize();
        });

      $('#finalNotes').on('input', validateFinalize);
    });

    function loadPaymentTerms() {
      $.ajax({
        url: paymentTermsUrl,
        success: function(resp) {
          paymentTerms = resp || [];
          const sel = $('#finalPaymentTerm');
          sel.empty().append(new Option('Select...', ''));
          paymentTerms.forEach(t => sel.append(new Option(t.name, t.id)));

          const stateTerm = (initialState && initialState.payment_term_id) ? initialState.payment_term_id : (
            currentOrder.payment_term_id || '');
          if (stateTerm) sel.val(stateTerm);

          sel.on('change', function() {
            applyPaymentTermUI($(this).val());
            validateFinalize();
          });

          applyPaymentTermUI(sel.val());
          renderPreview();
          validateFinalize();
        }
      });
    }

    function loadPaymentMethods() {
      $.ajax({
        url: paymentMethodsUrl,
        success: function(resp) {
          paymentMethods = resp || [];
          const sel = $('#finalPaymentMethod');
          sel.empty().append(new Option('Select...', ''));
          paymentMethods.forEach(m => sel.append(new Option(m.name, m.id)));

          const stateMethod = (initialState && initialState.payment_method_id) ? initialState.payment_method_id : (
            currentOrder.payment_method_id || '');
          if (stateMethod) sel.val(stateMethod);

          sel.on('change', validateFinalize);

          applyPaymentTermUI($('#finalPaymentTerm').val());
          validateFinalize();
        }
      });
    }

    function applyPaymentTermUI(termId) {
      const term = paymentTerms.find(t => (t.id + '') === (termId + ''));
      if (!term) {
        $('#finalDueDate').val('-');
        $('#finalPaymentMethodWrap').addClass('hidden');
        return;
      }

      const dueDays = parseInt(term.due_days || 0);
      const dueDate = new Date();
      dueDate.setDate(dueDate.getDate() + dueDays);
      $('#finalDueDate').val(dueDate.toLocaleDateString());

      if (dueDays === 0) {
        $('#finalPaymentMethodWrap').removeClass('hidden');
      } else {
        $('#finalPaymentMethodWrap').addClass('hidden');
        $('#finalPaymentMethod').val('');
      }
    }

    function renderPreview() {
      const list = $('#previewItems');
      list.empty();

      let subtotal = 0;
      let qtyTotal = 0;

      cart.forEach((item) => {
        const qty = parseFloat(item.qty || 0);
        const unit = parseFloat(item.unit_price || 0);
        const discP = parseFloat(item.discount_percent || 0);

        const gross = qty * unit;
        const disc = gross * (discP / 100);
        const net = gross - disc;

        subtotal += net;
        qtyTotal += qty;

        const name = (item.type === 'custom') ?
          (item.custom_name || 'Custom Item') :
          (item.display_name || 'Item');

        const attrs = (item.type === 'custom') ?
          (item.custom_sku ? 'SKU: ' + item.custom_sku : 'Custom item') :
          (item.attrs_text || '');

        list.append(`
          <div class="grid grid-cols-12 px-3 py-3 border-t border-slate-200 items-center">
            <div class="col-span-5 min-w-0">
              <div class="text-[12px] font-black text-slate-900 truncate">${escapeHtml(name)}</div>
              <div class="text-[10px] font-bold text-slate-500 truncate">${escapeHtml(attrs)}</div>
            </div>
            <div class="col-span-2 text-center text-[12px] font-black text-slate-800">${qty}</div>
            <div class="col-span-2 text-right text-[12px] font-black text-slate-800">${unit.toFixed(2)}</div>
            <div class="col-span-1 text-center text-[11px] font-black text-slate-600">${discP ? discP + '%' : '-'}</div>
            <div class="col-span-2 text-right text-[12px] font-black text-slate-900">${net.toFixed(2)}</div>
          </div>
        `);
      });

      const shipping = parseFloat($('#finalShipping').val() || 0);
      const globalDiscP = parseFloat($('#finalGlobalDiscountPercent').val() || 0);
      const globalDiscExtra = parseFloat($('#finalGlobalDiscountAmount').val() || 0);

      // Estimate taxable base for global discount
      const lineDiscTotal = cart.reduce((acc, i) => acc + ((parseFloat(i.qty) * parseFloat(i.unit_price)) * (parseFloat(i
        .discount_percent) / 100)), 0);
      const itemsGross = cart.reduce((acc, i) => acc + (parseFloat(i.qty) * parseFloat(i.unit_price)), 0);
      const taxableBase = Math.max(0, itemsGross - lineDiscTotal);

      const globalDiscFromPct = taxableBase * (globalDiscP / 100);
      const totalGlobalDisc = globalDiscFromPct + globalDiscExtra;

      // Note: Full tax calculation is complex here without knowing rates per group, 
      // so we rely on the backend for exact figures on save. 
      // For preview, we show a simplified estimate if possible or just use existing totals.
      // But we'll try to estimate based on currentOrder.tax_total or similar if nothing changed.
      let taxEstimate = (currentOrder.tax_total || 0);
      // If items changed, this estimate becomes less accurate.

      const grand = Math.max(0, itemsGross - (lineDiscTotal + totalGlobalDisc) + taxEstimate + shipping);

      $('#previewLineCount').text(cart.length + ' Lines • ' + qtyTotal + ' Qty');
      $('#pvSubtotal').text(itemsGross.toFixed(2));
      $('#pvLineDiscount').text('-' + lineDiscTotal.toFixed(2));
      $('#pvGlobalDiscount').text('-' + totalGlobalDisc.toFixed(2));
      $('#pvShipping').text(shipping.toFixed(2));
      $('#pvTax').text(taxEstimate.toFixed(2));
      $('#pvGrand').text(grand.toFixed(2));
    }

    function validateFinalize() {
      const warnings = [];

      if (!cart.length) warnings.push('Basket is empty.');
      const termId = $('#finalPaymentTerm').val();
      if (!termId) warnings.push('Payment Terms is required.');

      // If due now => payment method required
      const term = paymentTerms.find(t => (t.id + '') === (termId + ''));
      if (term && parseInt(term.due_days || 0) === 0) {
        const methodId = $('#finalPaymentMethod').val();
        if (!methodId) warnings.push('Payment Method is required for Due Now terms.');
      }

      // Basic numeric sanity
      const ship = parseFloat($('#finalShipping').val());
      if (isNaN(ship) || ship < 0) warnings.push('Shipping must be 0 or more.');

      const gdp = parseFloat($('#finalGlobalDiscountPercent').val());
      if (isNaN(gdp) || gdp < 0 || gdp > 100) warnings.push('Global Discount % must be 0-100.');

      // UI
      const wrap = $('#finalWarnings');
      const ul = $('#finalWarningsList');
      ul.empty();

      if (warnings.length) {
        warnings.forEach(w => ul.append(`<li>${escapeHtml(w)}</li>`));
        wrap.removeClass('hidden');
        $('#finalConfirmBtn').prop('disabled', true);
      } else {
        wrap.addClass('hidden');
        $('#finalConfirmBtn').prop('disabled', false);
      }
    }

    function buildPayload() {
      return {
        _token: $('meta[name="csrf-token"]').attr('content'),
        currency: $('#finalCurrency').val(),
        payment_term_id: $('#finalPaymentTerm').val(),
        payment_method_id: $('#finalPaymentMethod').val(),
        shipping: $('#finalShipping').val(),
        additional: 0,
        tax_group_id: $('#finalTaxGroup').val(),
        global_discount_percent: $('#finalGlobalDiscountPercent').val(),
        global_discount_amount: $('#finalGlobalDiscountAmount').val(),
        notes: $('#finalNotes').val(),
        items: cart.map(i => ({
          type: i.type || 'variant',
          variant_id: (i.type === 'custom') ? null : i.variant_id,
          custom_name: i.custom_name || i.display_name || null,
          custom_sku: i.custom_sku || null,
          qty: i.qty,
          unit_price: i.unit_price,
          discount_percent: i.discount_percent,
          tax_group_id: i.tax_group_id || null
        }))
      };
    }

    window.finalSaveDraft = function() {
      // Save draft via update endpoint (your Step 02 style)
      const payload = buildPayload();
      payload._method = 'PUT';

      $.ajax({
        url: updateUrl,
        method: 'POST',
        data: payload,
        success: function() {
          Swal.fire({
            icon: 'success',
            title: 'Draft Saved',
            toast: true,
            position: 'top-end',
            timer: 1800,
            showConfirmButton: false
          });
        },
        error: function(xhr) {
          const msg = xhr.responseJSON?.message || 'Failed to save draft.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: msg,
            confirmButtonColor: '#ef4444'
          });
        }
      });
    }

    window.finalConfirmOrder = function() {
      validateFinalize();
      if ($('#finalConfirmBtn').prop('disabled')) return;

      Swal.fire({
        title: 'Confirm Sales Order?',
        text: 'This will finalize the order. Make sure everything is correct.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Finalize',
        confirmButtonColor: '#4f46e5'
      }).then((res) => {
        if (!res.isConfirmed) return;

        const btn = $('#finalConfirmBtn');
        const old = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Finalizing...');

        $.ajax({
          url: finalizeStoreUrl,
          method: 'POST',
          data: buildPayload(),
          success: function(resp) {
            if (resp && resp.redirect) window.location.href = resp.redirect;
            else window.location.href =
              "{{ company_route('sales-orders.show', ['sales_order' => $order->uuid]) }}";
          },
          error: function(xhr) {
            btn.prop('disabled', false).html(old);
            const msg = xhr.responseJSON?.message || 'Failed to finalize order.';
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: msg,
              confirmButtonColor: '#ef4444'
            });
          }
        });
      });
    }

    function escapeHtml(str) {
      str = (str ?? '').toString();
      return str.replace(/[&<>"']/g, (m) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [m]));
    }
  </script>
@endpush
