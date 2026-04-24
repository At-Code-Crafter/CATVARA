@extends('catvara.layouts.app')

@section('title', 'Edit Invoice ' . $invoice->invoice_number)

@section('content')
    <div class="space-y-8 animate-fade-in">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Edit Invoice #{{ $invoice->invoice_number }}
                    </h2>
                    <span class="badge badge-warning">Draft</span>
                </div>
                <p class="text-slate-400 text-sm mt-1 font-medium italic">
                    Editing invoice for {{ $invoice->customer->display_name ?? 'N/A' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ company_route('accounting.invoices.show', ['invoice' => $invoice->uuid]) }}"
                    class="btn btn-white">
                    <i class="fas fa-arrow-left mr-2 text-slate-500"></i> Back to Invoice
                </a>
            </div>
        </div>

        <form id="editInvoiceForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Items -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Invoice Details Card -->
                    <div class="card border-none shadow-sm overflow-hidden bg-white">
                        <div class="bg-linear-to-r from-slate-50 to-slate-100/50 px-6 py-4 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                                <i class="fas fa-info-circle mr-3 text-brand-500"></i> Invoice Details
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Issue
                                        Date</label>
                                    <input type="date" name="issued_at"
                                        value="{{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : now()->format('Y-m-d') }}"
                                        class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Due
                                        Date</label>
                                    <input type="date" name="due_date"
                                        value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}"
                                        class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Payment
                                        Term</label>
                                    <select name="payment_term_id"
                                        class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10">
                                        <option value="">None</option>
                                        @foreach ($paymentTerms as $term)
                                            <option value="{{ $term->id }}"
                                                {{ $invoice->payment_term_id == $term->id ? 'selected' : '' }}>
                                                {{ $term->name }} ({{ $term->due_days }} days)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="card border-none shadow-sm overflow-hidden bg-white">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-3">
                                <i class="fas fa-list text-brand-500"></i> Invoice Items
                            </h3>
                            <span class="badge badge-secondary">{{ $invoice->items->count() }} Items</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm" id="itemsTable">
                                <thead
                                    class="bg-slate-50/50 text-slate-500 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left w-[30%]">Product</th>
                                        <th class="px-4 py-3 text-center w-[10%]">Qty</th>
                                        <th class="px-4 py-3 text-right w-[14%]">Unit Price</th>
                                        <th class="px-4 py-3 text-center w-[12%]">Disc %</th>
                                        <th class="px-4 py-3 text-left w-[18%]">Tax Group</th>
                                        <th class="px-4 py-3 text-right w-[16%]">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($invoice->items as $index => $item)
                                        <tr class="item-row hover:bg-slate-50/50 transition-colors"
                                            data-index="{{ $index }}">
                                            <td class="px-4 py-3">
                                                <input type="hidden" name="items[{{ $index }}][id]"
                                                    value="{{ $item->id }}">
                                                <div class="flex flex-col">
                                                    <span
                                                        class="font-bold text-slate-800 text-xs">{{ $item->product_name }}</span>
                                                    @if ($item->variant_description)
                                                        <span
                                                            class="text-[10px] text-slate-400">{{ $item->variant_description }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1">
                                                    <button type="button"
                                                        class="qty-minus flex-shrink-0 w-7 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm transition-all flex items-center justify-center"
                                                        data-index="{{ $index }}">
                                                        <i class="fas fa-minus text-[10px]"></i>
                                                    </button>
                                                    <input type="number" name="items[{{ $index }}][quantity]"
                                                        value="{{ (int) $item->quantity }}" step="1" min="1"
                                                        class="item-qty w-14 h-9 rounded-lg border-slate-200 text-sm font-semibold text-center focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                                    <button type="button"
                                                        class="qty-plus flex-shrink-0 w-7 h-9 rounded-lg bg-slate-100 hover:bg-brand-100 text-slate-600 hover:text-brand-600 font-bold text-sm transition-all flex items-center justify-center"
                                                        data-index="{{ $index }}">
                                                        <i class="fas fa-plus text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" name="items[{{ $index }}][unit_price]"
                                                    value="{{ (float) $item->unit_price }}" step="any" min="0"
                                                    class="item-price w-full h-9 rounded-lg border-slate-200 text-sm font-semibold text-right focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" name="items[{{ $index }}][discount_percent]"
                                                    value="{{ (float) $item->discount_percent }}" step="0.01"
                                                    min="0" max="100"
                                                    class="item-discount w-full h-9 rounded-lg border-slate-200 text-sm font-semibold text-center focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10">
                                            </td>
                                            <td class="px-4 py-3">
                                                <select name="items[{{ $index }}][tax_group_id]"
                                                    class="item-tax w-full h-9 rounded-lg border-slate-200 text-[11px] font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10">
                                                    <option value="" data-rate="0">No Tax</option>
                                                    @foreach ($taxGroups as $tg)
                                                        <option value="{{ $tg->id }}"
                                                            data-rate="{{ $tg->activeRateSum() }}"
                                                            {{ $item->tax_group_id == $tg->id ? 'selected' : '' }}>
                                                            {{ $tg->name }} ({{ $tg->activeRateSum() }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="item-line-total font-bold text-slate-900 text-sm">0.00</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="card border-none shadow-sm overflow-hidden bg-white">
                        <div class="bg-linear-to-r from-slate-50 to-slate-100/50 px-6 py-4 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                                <i class="fas fa-sticky-note mr-3 text-amber-500"></i> Internal Notes
                            </h3>
                        </div>
                        <div class="p-6">
                            <textarea name="notes" rows="3"
                                class="w-full rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
                                placeholder="Internal notes for this invoice...">{{ $invoice->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Totals & Actions -->
                <div class="space-y-6">

                    <!-- Adjustments Card -->
                    <div class="card border-none shadow-sm overflow-hidden bg-white sticky top-6">
                        <div class="bg-linear-to-r from-slate-50 to-slate-100/50 px-6 py-4 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                                <i class="fas fa-sliders-h mr-3 text-brand-500"></i> Adjustments
                            </h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Global
                                    Discount (%)</label>
                                <input type="number" name="global_discount_percent" id="globalDiscountPercent"
                                    value="{{ (float) $invoice->global_discount_percent }}" step="0.01"
                                    min="0" max="100"
                                    class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Shipping
                                    Total</label>
                                <input type="number" name="shipping_total" id="shippingTotal"
                                    value="{{ (float) $invoice->shipping_total }}" step="0.01" min="0"
                                    class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10">
                            </div>
                        </div>
                    </div>

                    <!-- Totals Card -->
                    <div class="card border-none shadow-xl bg-slate-900 text-white overflow-hidden relative">
                        <div class="absolute inset-0 bg-linear-to-br from-brand-600/20 to-transparent pointer-events-none">
                        </div>
                        <div class="p-6 relative z-10">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] mb-6 text-brand-400">Live Totals</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-slate-400">
                                    <span class="text-xs font-bold uppercase tracking-widest">Subtotal</span>
                                    <span class="text-base font-bold" id="displaySubtotal">0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-emerald-400">
                                    <span class="text-xs font-bold uppercase tracking-widest">Discount</span>
                                    <span class="text-base font-bold" id="displayDiscount">-0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-400">
                                    <span class="text-xs font-bold uppercase tracking-widest">Shipping</span>
                                    <span class="text-base font-bold" id="displayShipping">0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-400">
                                    <span class="text-xs font-bold uppercase tracking-widest">Tax</span>
                                    <span class="text-base font-bold" id="displayTax">0.00</span>
                                </div>
                                <div class="pt-4 mt-4 border-t border-slate-800">
                                    <div class="flex justify-between items-end">
                                        <span
                                            class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-400">Grand
                                            Total</span>
                                        <span class="text-3xl font-black tracking-tight text-brand-500"
                                            id="displayGrandTotal">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <button type="submit" id="saveInvoiceBtn"
                        class="w-full btn bg-brand-500 hover:bg-brand-600 text-white border-0 py-4 h-auto shadow-lg shadow-brand-500/25 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                        <span class="font-bold flex items-center justify-center gap-2 text-base">
                            <i class="fas fa-save"></i> Save Changes
                        </span>
                    </button>

                    <!-- Cancel Link -->
                    <a href="{{ company_route('accounting.invoices.show', ['invoice' => $invoice->uuid]) }}"
                        class="w-full flex items-center justify-center py-3 text-xs font-black text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest">
                        Cancel & Discard Changes
                    </a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const currencyCode = "{{ $invoice->currency->code ?? 'GBP' }}";

            function formatMoney(amount) {
                return parseFloat(amount || 0).toFixed(2);
            }

            function recalculate() {
                let subtotal = 0;
                let itemDiscountTotal = 0;
                let taxTotal = 0;

                document.querySelectorAll('.item-row').forEach(function(row) {
                    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const discPct = parseFloat(row.querySelector('.item-discount').value) || 0;
                    const taxSelect = row.querySelector('.item-tax');
                    const taxRate = parseFloat(taxSelect.selectedOptions[0]?.dataset.rate) || 0;

                    const lineSubtotal = qty * price;
                    const lineDiscount = lineSubtotal * (discPct / 100);
                    const taxable = lineSubtotal - lineDiscount;
                    const lineTax = taxable * (taxRate / 100);
                    const lineTotal = taxable + lineTax;

                    row.querySelector('.item-line-total').textContent = formatMoney(lineTotal);

                    subtotal += lineSubtotal;
                    itemDiscountTotal += lineDiscount;
                    taxTotal += lineTax;
                });

                const globalDiscPct = parseFloat(document.getElementById('globalDiscountPercent').value) || 0;
                const globalDiscAmt = subtotal * (globalDiscPct / 100);
                const totalDiscount = itemDiscountTotal + globalDiscAmt;

                const shipping = parseFloat(document.getElementById('shippingTotal').value) || 0;
                const grandTotal = subtotal - totalDiscount + shipping + taxTotal;

                document.getElementById('displaySubtotal').textContent = formatMoney(subtotal);
                document.getElementById('displayDiscount').textContent = '-' + formatMoney(totalDiscount);
                document.getElementById('displayShipping').textContent = formatMoney(shipping);
                document.getElementById('displayTax').textContent = formatMoney(taxTotal);
                document.getElementById('displayGrandTotal').textContent = formatMoney(grandTotal);
            }

            // Bind recalculate to all editable fields
            document.querySelectorAll('.item-qty, .item-price, .item-discount, .item-tax').forEach(function(el) {
                el.addEventListener('input', recalculate);
                el.addEventListener('change', recalculate);
            });

            // Quantity +/- buttons
            document.querySelectorAll('.qty-minus').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const row = this.closest('.item-row');
                    const input = row.querySelector('.item-qty');
                    let val = parseInt(input.value) || 1;
                    if (val > 1) {
                        input.value = val - 1;
                        input.dispatchEvent(new Event('input'));
                    }
                });
            });

            document.querySelectorAll('.qty-plus').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const row = this.closest('.item-row');
                    const input = row.querySelector('.item-qty');
                    let val = parseInt(input.value) || 0;
                    input.value = val + 1;
                    input.dispatchEvent(new Event('input'));
                });
            });

            document.getElementById('globalDiscountPercent').addEventListener('input', recalculate);
            document.getElementById('shippingTotal').addEventListener('input', recalculate);

            // Initial calculation
            recalculate();

            // Form Submit
            document.getElementById('editInvoiceForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const btn = document.getElementById('saveInvoiceBtn');
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

                const formData = new FormData(this);
                const data = {};

                // Parse form data into structured object
                data._token = formData.get('_token');
                data._method = 'PUT';
                data.issued_at = formData.get('issued_at');
                data.due_date = formData.get('due_date');
                data.payment_term_id = formData.get('payment_term_id');
                data.notes = formData.get('notes');
                data.global_discount_percent = formData.get('global_discount_percent');
                data.shipping_total = formData.get('shipping_total');
                data.items = [];

                document.querySelectorAll('.item-row').forEach(function(row, idx) {
                    data.items.push({
                        id: formData.get('items[' + idx + '][id]'),
                        quantity: formData.get('items[' + idx + '][quantity]'),
                        unit_price: formData.get('items[' + idx + '][unit_price]'),
                        discount_percent: formData.get('items[' + idx + '][discount_percent]'),
                        tax_group_id: formData.get('items[' + idx + '][tax_group_id]'),
                    });
                });

                fetch("{{ company_route('accounting.invoices.update', ['invoice' => $invoice->uuid]) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Invoice Updated!',
                                text: result.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = result.redirect_url;
                            });
                        } else {
                            Swal.fire('Error', result.message || 'Failed to update invoice.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'An error occurred while saving the invoice.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    });
            });
        </script>
    @endpush
@endsection
