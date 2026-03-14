@extends('catvara.layouts.app')

@section('title', 'Create Delivery Note — ' . $order->order_number)

@section('content')
    <div class="space-y-6 animate-fade-in">

        {{-- Breadcrumbs --}}
        <nav class="flex items-center text-sm text-slate-400 font-medium">
            <a href="{{ company_route('sales-orders.index') }}" class="hover:text-brand-400 transition-colors">Sales
                Orders</a>
            <i class="fas fa-chevron-right text-[10px] mx-3 text-slate-300"></i>
            <a href="{{ company_route('sales-orders.show', ['sales_order' => $order->uuid]) }}"
                class="hover:text-brand-400 transition-colors">{{ $order->order_number }}</a>
            <i class="fas fa-chevron-right text-[10px] mx-3 text-slate-300"></i>
            <span class="text-slate-600">Create Delivery Note</span>
        </nav>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">
                    <i class="fas fa-truck text-brand-400 mr-2"></i> Create Delivery Note
                </h2>
                <p class="text-slate-400 text-sm mt-1 font-medium">
                    Order {{ $order->order_number }} &bull; {{ $order->created_at->format('M d, Y') }}
                    @if ($order->customer)
                        &bull; {{ $order->customer->display_name }}
                    @endif
                </p>
            </div>
            <a href="{{ company_route('sales-orders.show', ['sales_order' => $order->uuid]) }}" class="btn btn-white">
                <i class="fas fa-arrow-left mr-2"></i> Back to Order
            </a>
        </div>

        {{-- Delivery Details Card --}}
        <div class="card bg-white border-slate-100 shadow-soft p-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-cog text-brand-400"></i> Delivery Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dispatch From <span
                            class="text-red-400">*</span></label>
                    <select id="dn-location"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden">
                        @foreach ($locations as $loc)
                            <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reference No.</label>
                    <input type="text" id="dn-reference"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden"
                        placeholder="LPO / Job No.">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Vehicle No.</label>
                    <input type="text" id="dn-vehicle"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden"
                        placeholder="AE-12345">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Notes</label>
                    <input type="text" id="dn-notes"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden"
                        placeholder="Driver instructions...">
                </div>
            </div>
        </div>

        {{-- Order Items Reference --}}
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-list text-brand-400"></i> Order Items
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/80">
                            <th class="px-5 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Item</th>
                            <th
                                class="px-5 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                                Ordered</th>
                            <th
                                class="px-5 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                                Already Shipped</th>
                            <th
                                class="px-5 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                                Remaining</th>
                            <th
                                class="px-5 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                                Assigned to Boxes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($order->items as $item)
                            @php
                                $remaining = (float) $item->quantity - (float) $item->fulfilled_quantity;
                            @endphp
                            <tr class="{{ $remaining <= 0 ? 'opacity-40' : '' }}" data-item-id="{{ $item->id }}">
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-700">{{ $item->product_name }}</div>
                                    @if ($item->variant_description)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $item->variant_description }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center font-bold text-slate-600">{{ (float) $item->quantity }}
                                </td>
                                <td class="px-5 py-3 text-center text-slate-500">{{ (float) $item->fulfilled_quantity }}
                                </td>
                                <td
                                    class="px-5 py-3 text-center font-bold {{ $remaining > 0 ? 'text-indigo-600' : 'text-slate-400' }}">
                                    {{ $remaining }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="item-assigned-qty font-bold text-emerald-600"
                                        data-item-id="{{ $item->id }}">0</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Box Assignments --}}
        <div class="card bg-white border-slate-100 shadow-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-boxes-stacked text-brand-400"></i> Box Assignments
                </h3>
                <button type="button" onclick="addBox()"
                    class="btn bg-brand-400 hover:bg-brand-500 text-white text-xs px-4 py-2 rounded-lg font-bold shadow-sm">
                    <i class="fas fa-plus mr-1.5"></i> Add Box
                </button>
            </div>

            <div id="boxes-container">
                {{-- Boxes will be rendered here by JS --}}
            </div>

            <div id="no-boxes-msg" class="text-center py-12 text-slate-400">
                <i class="fas fa-box-open text-4xl mb-3 block"></i>
                <p class="font-bold">No boxes yet</p>
                <p class="text-sm mt-1">Click "Add Box" to start assigning items.</p>
            </div>
        </div>

        {{-- Summary & Save --}}
        <div class="card bg-white border-slate-100 shadow-soft p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-6 text-sm">
                    <div>
                        <span class="text-slate-400 font-medium">Total Boxes:</span>
                        <span class="font-bold text-slate-800 ml-1" id="total-boxes">0</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Items Assigned:</span>
                        <span class="font-bold text-slate-800 ml-1" id="total-assigned">0</span>
                        <span class="text-slate-400">/
                            {{ $order->items->sum(fn($i) => max(0, (float) $i->quantity - (float) $i->fulfilled_quantity)) }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="autoAssign()"
                        class="btn btn-white text-xs px-4 py-2.5 rounded-lg font-bold">
                        <i class="fas fa-magic mr-1.5"></i> Auto-Assign (1 Item = 1 Box)
                    </button>
                    <button type="button" onclick="saveDeliveryNote()" id="save-btn"
                        class="btn bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-6 py-2.5 rounded-lg font-bold shadow-sm">
                        <i class="fas fa-check mr-2"></i> Save & Generate Delivery Note
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        $orderItems = $order->items
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'variant' => $item->variant_description,
                    'sku' => $item->productVariant?->sku,
                    'ordered' => (float) $item->quantity,
                    'fulfilled' => (float) $item->fulfilled_quantity,
                    'remaining' => max(0, (float) $item->quantity - (float) $item->fulfilled_quantity),
                    'unit_price' => (float) $item->unit_price,
                ];
            })
            ->values()
            ->toArray();
    @endphp

    <script>
        const ORDER_ITEMS = @json($orderItems);
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const SAVE_URL = "{{ company_route('sales-orders.delivery-note.generate', ['sales_order' => $order->uuid]) }}";
        const REDIRECT_URL = "{{ company_route('sales-orders.show', ['sales_order' => $order->uuid]) }}";

        let boxes = []; // [{box_number, items: [{order_item_id, quantity}]}]
        let nextBoxNumber = 1;

        function addBox() {
            boxes.push({
                box_number: nextBoxNumber++,
                items: []
            });
            renderBoxes();
        }

        function removeBox(boxIdx) {
            boxes.splice(boxIdx, 1);
            renderBoxes();
        }

        function addItemToBox(boxIdx) {
            // Find first item with remaining capacity
            const available = ORDER_ITEMS.filter(i => i.remaining > 0);
            if (available.length === 0) return;
            boxes[boxIdx].items.push({
                order_item_id: available[0].id,
                quantity: ''
            });
            renderBoxes();
        }

        function removeItemFromBox(boxIdx, itemIdx) {
            boxes[boxIdx].items.splice(itemIdx, 1);
            renderBoxes();
        }

        function updateBoxItemId(boxIdx, itemIdx, value) {
            boxes[boxIdx].items[itemIdx].order_item_id = parseInt(value);
            renderBoxes();
        }

        function updateBoxItemQty(boxIdx, itemIdx, value) {
            boxes[boxIdx].items[itemIdx].quantity = value;
            updateSummary();
        }

        function getAssignedQtyForItem(itemId) {
            let total = 0;
            boxes.forEach(box => {
                box.items.forEach(bi => {
                    if (bi.order_item_id === itemId && bi.quantity !== '') {
                        total += parseFloat(bi.quantity) || 0;
                    }
                });
            });
            return total;
        }

        function updateSummary() {
            let totalAssigned = 0;
            ORDER_ITEMS.forEach(item => {
                const assigned = getAssignedQtyForItem(item.id);
                totalAssigned += assigned;
                document.querySelectorAll(`.item-assigned-qty[data-item-id="${item.id}"]`).forEach(el => {
                    el.textContent = assigned;
                    el.className = 'item-assigned-qty font-bold ' + (assigned >= item.remaining && item
                        .remaining > 0 ? 'text-emerald-600' : assigned > 0 ? 'text-amber-500' :
                        'text-slate-400');
                });
            });

            document.getElementById('total-boxes').textContent = boxes.length;
            document.getElementById('total-assigned').textContent = totalAssigned;
            document.getElementById('no-boxes-msg').style.display = boxes.length === 0 ? 'block' : 'none';
        }

        function renderBoxes() {
            const container = document.getElementById('boxes-container');
            container.innerHTML = '';

            boxes.forEach((box, boxIdx) => {
                const boxEl = document.createElement('div');
                boxEl.className = 'mb-4 rounded-2xl border border-slate-200 overflow-hidden bg-slate-50/30';
                boxEl.innerHTML = `
                    <div class="px-5 py-3 bg-slate-100/60 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-black uppercase tracking-wider">
                                Box ${box.box_number}
                            </span>
                            <span class="text-[10px] text-slate-400 font-bold">${box.items.length} item(s)</span>
                        </div>
                        <button type="button" onclick="removeBox(${boxIdx})" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Remove Box">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <table class="w-full text-sm mb-3">
                            <thead>
                                <tr>
                                    <th class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest pb-2 w-[55%]">Item</th>
                                    <th class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest pb-2 w-[15%]">Available</th>
                                    <th class="text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest pb-2 w-[20%]">Qty in Box</th>
                                    <th class="pb-2 w-[10%]"></th>
                                </tr>
                            </thead>
                            <tbody id="box-${boxIdx}-items">
                                ${box.items.map((bi, itemIdx) => renderBoxItemRow(boxIdx, itemIdx, bi)).join('')}
                            </tbody>
                        </table>
                        <button type="button" onclick="addItemToBox(${boxIdx})" class="text-[10px] font-bold text-indigo-500 hover:text-indigo-700 uppercase tracking-widest transition-colors">
                            <i class="fas fa-plus mr-1"></i> Add Item to Box
                        </button>
                    </div>
                `;
                container.appendChild(boxEl);
            });

            updateSummary();
        }

        function renderBoxItemRow(boxIdx, itemIdx, bi) {
            const selectedItem = ORDER_ITEMS.find(i => i.id === bi.order_item_id);
            const maxQty = selectedItem ? selectedItem.remaining : 0;

            let optionsHtml = ORDER_ITEMS.filter(i => i.remaining > 0).map(item => {
                const label = item.name + (item.variant ? ' — ' + item.variant : '') + (item.sku ? ' [' + item.sku +
                    ']' : '');
                const selected = item.id === bi.order_item_id ? 'selected' : '';
                return `<option value="${item.id}" ${selected}>${label}</option>`;
            }).join('');

            return `
                <tr class="border-b border-slate-100 last:border-0">
                    <td class="py-2 pr-3">
                        <select onchange="updateBoxItemId(${boxIdx}, ${itemIdx}, this.value)"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs font-medium text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden bg-white">
                            ${optionsHtml}
                        </select>
                    </td>
                    <td class="py-2 text-center text-xs font-bold text-slate-500">${maxQty}</td>
                    <td class="py-2 px-2">
                        <input type="number"
                            value="${bi.quantity}"
                            min="0"
                            step="1"
                            oninput="updateBoxItemQty(${boxIdx}, ${itemIdx}, this.value)"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 text-left text-xs font-bold text-indigo-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-hidden">
                    </td>
                    <td class="py-2 text-center">
                        <button type="button" onclick="removeItemFromBox(${boxIdx}, ${itemIdx})" class="text-red-300 hover:text-red-500 transition-colors p-1">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </td>
                </tr>
            `;
        }

        function autoAssign() {
            boxes = [];
            nextBoxNumber = 1;
            ORDER_ITEMS.forEach(item => {
                if (item.remaining > 0) {
                    boxes.push({
                        box_number: nextBoxNumber++,
                        items: [{
                            order_item_id: item.id,
                            quantity: item.remaining
                        }]
                    });
                }
            });
            renderBoxes();
        }

        function saveDeliveryNote() {
            if (boxes.length === 0) {
                Swal.fire('No Boxes', 'Please add at least one box with items.', 'warning');
                return;
            }

            // Validate all boxes have items with quantities
            for (let i = 0; i < boxes.length; i++) {
                const box = boxes[i];
                if (box.items.length === 0) {
                    Swal.fire('Empty Box', `Box ${box.box_number} has no items. Please add items or remove the box.`,
                        'warning');
                    return;
                }
                for (let j = 0; j < box.items.length; j++) {
                    const bi = box.items[j];
                    const qty = parseFloat(bi.quantity) || 0;
                    if (qty <= 0) {
                        Swal.fire('Invalid Quantity', `Box ${box.box_number} has an item with zero or empty quantity.`,
                            'warning');
                        return;
                    }
                }
            }

            // Validate total assigned per item doesn't exceed remaining
            for (const item of ORDER_ITEMS) {
                const assigned = getAssignedQtyForItem(item.id);
                if (assigned > item.remaining) {
                    Swal.fire('Over-assigned',
                        `"${item.name}" has ${assigned} units assigned across boxes but only ${item.remaining} remaining.`,
                        'warning');
                    return;
                }
            }

            const btn = document.getElementById('save-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

            $.ajax({
                url: SAVE_URL,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    _token: CSRF_TOKEN,
                    inventory_location_id: document.getElementById('dn-location').value,
                    reference_number: document.getElementById('dn-reference').value || null,
                    vehicle_number: document.getElementById('dn-vehicle').value || null,
                    notes: document.getElementById('dn-notes').value || null,
                    boxes: boxes.map(box => ({
                        box_number: box.box_number,
                        items: box.items.map(bi => ({
                            order_item_id: bi.order_item_id,
                            quantity: parseFloat(bi.quantity) || 0
                        }))
                    }))
                }),
                success: function(response) {
                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Delivery Note Generated!',
                            text: response.message,
                            confirmButtonColor: '#4f46e5'
                        }).then(() => {
                            if (response.redirect) {
                                window.open(response.redirect, '_blank');
                            }
                            window.location.href = REDIRECT_URL;
                        });
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check mr-2"></i> Save & Generate Delivery Note';
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to generate delivery note.',
                        'error');
                }
            });
        }

        // Initialize with no boxes
        renderBoxes();
    </script>
@endsection
