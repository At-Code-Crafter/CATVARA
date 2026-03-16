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

        /* Removed mobile min-height to allow better natural flow */
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
                        class="w-6 h-6 rounded bg-slate-100 text-slate-400 flex items-center justify-center font-black text-[10px]">
                        03
                    </div>
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Finalize</span>
                </div>
            </div>
        </div>

        <div class="pos-screen-container flex flex-col">
            {{-- Mobile Tab Switcher --}}
            <div class="lg:hidden flex border-b border-slate-200 bg-white mb-2 p-1 gap-1 rounded-xl shrink-0">
                <button type="button" onclick="switchMobileTab('products')" id="tab-products"
                    class="flex-1 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-brand-600 text-white transition-all shadow-sm">
                    Items
                </button>
                <button type="button" onclick="switchMobileTab('cart')" id="tab-cart"
                    class="flex-1 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-500 hover:bg-slate-100 transition-all">
                    Basket(<span id="mobileCartCount">0</span>)
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 flex-1 min-h-0 items-stretch overflow-hidden">

                {{-- LEFT: Product side --}}
                <div id="productSection"
                    class="lg:col-span-5 flex flex-col min-h-0 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden transition-all duration-300">

                    {{-- Search & Filter Header --}}
                    <div class="shrink-0 z-10 bg-white border-b border-slate-100 p-3 space-y-3">

                        {{-- Search Bar with Custom Item Button --}}
                        <div class="flex items-center gap-3">
                            <div class="flex-1 relative">
                                <i
                                    class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" id="productSearch"
                                    class="w-full pl-9 h-11 rounded-xl border border-slate-200 text-sm font-medium focus:border-brand-500 focus:ring-0 bg-slate-50/50 placeholder:text-slate-400 transition-shadow"
                                    placeholder="Search products or scan SKU...">
                            </div>
                            <button type="button" onclick="openCustomItemModal()"
                                class="h-11 px-4 rounded-xl bg-slate-900 text-white text-xs font-bold uppercase tracking-wide hover:bg-slate-800 transition-colors shadow-lg shadow-slate-200">
                                <i class="fas fa-plus mr-1"></i> Custom Item
                            </button>
                        </div>

                        {{-- Category & Brand Filters --}}
                        <div class="flex items-center gap-2">
                            <select id="categoryFilter"
                                class="flex-1 h-10 rounded-xl border border-slate-200 text-[10px] font-bold text-slate-600 bg-white focus:border-brand-500 focus:ring-0 hover:border-slate-300 transition-colors cursor-pointer uppercase tracking-tight">
                                <option value="">All Categories</option>
                            </select>
                            <select id="brandFilter"
                                class="flex-1 h-10 rounded-xl border border-slate-200 text-[10px] font-bold text-slate-600 bg-white focus:border-brand-500 focus:ring-0 hover:border-slate-300 transition-colors cursor-pointer uppercase tracking-tight">
                                <option value="">All Brands</option>
                            </select>
                        </div>
                    </div>

                    {{-- Product Grid --}}
                    <div class="scrollable-content min-h-0 flex-1 p-2 bg-slate-50/50">
                        <div id="productGrid"
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-2 pb-2">
                            {{-- injected --}}
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Cart side --}}
                <div id="cartSection"
                    class="lg:col-span-7 hidden lg:flex flex-col h-auto lg:h-full relative bg-slate-50 border-l border-slate-200 transition-all duration-300 overflow-y-auto">

                    {{-- Customer Cards (Moved Here) --}}
                    <div
                        class="shrink-0 p-3 bg-white border-b border-slate-100 flex items-start gap-3 overflow-x-auto no-scrollbar z-10 shadow-sm">
                        <div
                            class="group relative flex items-start gap-3 px-3 py-2.5 rounded-xl border border-slate-200 bg-white shrink-0 w-[48%] hover:border-brand-300 transition-all cursor-default shadow-sm hover:shadow-md">
                            <div
                                class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-receipt text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1" id="billingAddressContainer">
                                @include('catvara.sales-orders.partials._address_card_content', [
                                    'address' => $order->billingAddress,
                                    'name' => $order->billingAddress->name,
                                ])
                            </div>
                            <button type="button" onclick="openCustomerSwitcher('BILLING')"
                                class="absolute top-2 right-2 text-[10px] font-bold text-brand-600 bg-brand-50 px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                CHANGE
                            </button>
                        </div>

                        <div
                            class="group relative flex items-start gap-3 px-3 py-2.5 rounded-xl border border-slate-200 bg-white shrink-0 w-[48%] hover:border-indigo-300 transition-all cursor-default shadow-sm hover:shadow-md">
                            <div
                                class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-user-tag text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1" id="shippingAddressContainer">
                                @include('catvara.sales-orders.partials._address_card_content', [
                                    'address' => $order->shippingAddress,
                                    'name' => $order->shippingAddress->name,
                                ])
                            </div>
                            <div
                                class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="openEditShippingAddress()"
                                    class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">
                                    EDIT
                                </button>
                                <button type="button" onclick="openCustomerSwitcher('SHIPPING')"
                                    class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                                    CHANGE
                                </button>
                            </div>
                        </div>
                    </div>


                    {{-- Cart Items List --}}
                    <div class="p-3 bg-slate-50 min-h-[400px] max-h-[550px] overflow-y-auto lg:min-h-[420px] lg:max-h-[520px]"
                        id="cartItemsContainer"></div>

                    {{-- Footer Controls (Redesigned) --}}
                    <div
                        class="bg-white border-t border-slate-200 shadow-[0_-4px_15px_-3px_rgba(0,0,0,0.05)] shrink-0 z-20">



                        <div class="p-4 lg:p-6 grid grid-cols-12 gap-4 lg:gap-8 flex-col lg:flex-row">
                            {{-- LEFT: Payment & Logistics --}}
                            <div class="col-span-12 lg:col-span-7 space-y-4 lg:space-y-5">
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Payment
                                            Term</label>
                                        <select id="paymentTermSelect"
                                            class="w-full h-10 rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-slate-50 focus:bg-white focus:border-brand-500 transition-colors">
                                            <option value="">Select...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Currency</label>
                                        <select id="currencySelect"
                                            class="w-full h-10 rounded-xl border-slate-200 text-xs font-medium text-slate-700 bg-slate-50 focus:bg-white focus:border-brand-500 transition-colors">
                                            @foreach ($enabledCurrencies as $cur)
                                                <option value="{{ $cur->code }}"
                                                    {{ $order->currency->code == $cur->code ? 'selected' : '' }}>
                                                    {{ $cur->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tax
                                            Group</label>
                                        <select id="taxGroupSelect"
                                            class="w-full h-10 rounded-xl border-slate-200 text-xs font-medium text-slate-700 bg-slate-50 focus:bg-white focus:border-brand-500 transition-colors">
                                            <option value="" data-rate="0">No Tax</option>
                                            @foreach ($taxGroups as $tg)
                                                <option value="{{ $tg->id }}" data-rate="{{ $tg->rate }}"
                                                    {{ $order->tax_group_id == $tg->id ? 'selected' : '' }}>
                                                    {{ $tg->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-4">
                                    <div class="col-span-12 sm:col-span-4">
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Due
                                            Date</label>
                                        <input type="text" id="paymentDueDate"
                                            class="w-full h-10 rounded-xl border-slate-200 text-xs font-medium text-slate-700 bg-slate-50 focus:bg-white focus:border-brand-500 text-center"
                                            placeholder="-" readonly>
                                    </div>
                                    <div class="col-span-12 sm:col-span-8">
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Additional
                                            Notes</label>
                                        <input type="text" id="commentsInput"
                                            class="w-full h-10 rounded-xl border-slate-200 px-4 text-xs font-medium text-slate-700 bg-slate-50 focus:bg-white focus:border-brand-500 transition-colors"
                                            placeholder="Add private notes for this order...">
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT: Order Breakdown --}}
                            <div
                                class="col-span-12 lg:col-span-5 flex flex-col justify-between bg-slate-50/50 rounded-2xl p-4 border border-slate-100">
                                <div class="space-y-4 text-sm">
                                    <div class="flex justify-between items-center border-b border-slate-200/50 pb-2.5">
                                        <span
                                            class="text-slate-500 font-bold text-[10px] uppercase tracking-wider">Subtotal</span>
                                        <span class="text-slate-800 font-black text-sm font-mono"
                                            id="cartSubtotal">0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-slate-200/50 pb-2.5">
                                        <span class="text-slate-500 font-bold text-[10px] uppercase tracking-wider">Item
                                            Tax</span>
                                        <span class="text-slate-700 font-bold text-xs font-mono"
                                            id="cartItemTaxDisplay">0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-slate-200/50 pb-2.5">
                                        <span
                                            class="text-slate-500 font-bold text-[10px] uppercase tracking-wider">Shipping</span>
                                        <div class="flex items-center gap-2">
                                            <div class="w-24">
                                                <input type="number" id="shippingInput"
                                                    class="w-full h-8 text-right text-xs font-black bg-white border border-slate-200 rounded-lg focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 px-2 transition-all"
                                                    value="0">
                                            </div>
                                            <span class="text-[10px] text-slate-400 font-mono font-bold"
                                                id="cartShippingTaxDisplay">0.00</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-slate-200/50 pb-2.5">
                                        <span
                                            class="text-slate-500 font-bold text-[10px] uppercase tracking-wider">Discount</span>
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1 w-24">
                                                <span class="text-[10px] text-slate-400 font-black">%</span>
                                                <input type="number" id="globalDiscountPercentInput"
                                                    class="w-full h-8 text-right text-xs font-black bg-white border border-slate-200 rounded-lg focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 px-2 transition-all"
                                                    placeholder="0">
                                            </div>
                                            <span class="text-xs text-brand-600 font-black font-mono self-center"
                                                id="globalDiscountAmountDisplay">- 0.00</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-slate-200/50 pb-2.5">
                                        <span class="text-slate-500 font-bold text-[10px] uppercase tracking-wider">Total
                                            VAT</span>
                                        <span class="text-slate-700 font-black text-xs font-mono"
                                            id="cartTaxAmount">0.00</span>
                                    </div>

                                    <div
                                        class="pt-3 mt-2 px-3 py-2.5 bg-brand-50/50 rounded-xl border border-brand-100/50 flex justify-between items-center">
                                        <span class="text-xs font-black text-brand-900 uppercase tracking-widest">Grand
                                            Total</span>
                                        <div class="text-right">
                                            <span class="block text-2xl font-black text-brand-600 leading-none font-mono"
                                                id="cartGrandTotal">0.00</span>
                                            <span
                                                class="text-[10px] text-brand-400 font-black uppercase tracking-tighter">{{ $order->currency->code ?? 'AED' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Desktop Buttons --}}
                                <div class="pt-4 hidden lg:grid grid-cols-2 gap-3 mt-2">
                                    <button type="button" onclick="saveOrder('draft')"
                                        class="h-11 rounded-xl border border-slate-200 text-slate-500 font-bold text-xs uppercase hover:bg-white hover:text-slate-700 transition-colors">
                                        Save Draft
                                    </button>
                                    <button type="button" onclick="saveOrder('generate')"
                                        class="next-step-btn h-11 rounded-xl bg-brand-600 text-white font-bold text-xs uppercase tracking-widest hover:bg-brand-700 shadow-lg shadow-brand-200/50 transition-all flex items-center justify-center gap-2">
                                        Generate Order <i class="fas fa-check-circle"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- BOTTOM: Action Buttons (Persistent on Mobile) --}}
                    <div class="col-span-12 lg:hidden pt-2 grid grid-cols-2 gap-3 border-t border-slate-100 mt-2">
                        <button type="button" onclick="saveOrder('draft')"
                            class="h-11 rounded-xl border border-slate-200 text-slate-500 font-bold text-xs uppercase hover:bg-white hover:text-slate-700 transition-colors">
                            Save Draft
                        </button>
                        <button type="button" onclick="saveOrder('generate')"
                            class="next-step-btn h-11 rounded-xl bg-brand-600 text-white font-bold text-xs uppercase tracking-widest hover:bg-brand-700 shadow-lg shadow-brand-200/50 transition-all flex items-center justify-center gap-2">
                            Generate Order <i class="fas fa-check-circle"></i>
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
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="variantModalBackdrop">
        </div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    id="variantModalPanel">

                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 pb-0">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-16 h-16 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden border border-slate-200">
                                <img id="modalImg" src="" class="w-full h-full object-cover hidden"
                                    alt="">
                                <i id="modalIcon" class="fas fa-box text-slate-300 text-2xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-slate-900 leading-6" id="modalProductName">Product
                                    Name</h3>
                                <p class="text-xs text-slate-500 mt-1" id="modalProductCategory">Category</p>
                            </div>
                            <button type="button" class="text-slate-400 hover:text-slate-500"
                                onclick="closeVariantModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mt-6">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-3">Select
                                Variant</label>
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
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    id="customPanel">

                    <div class="bg-white px-4 pt-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-black text-slate-900">Add Custom Item</h3>
                                <p class="text-xs text-slate-500 mt-1">Use when item is not in catalog.</p>
                            </div>
                            <button type="button" class="text-slate-400 hover:text-slate-500"
                                onclick="closeCustomItemModal()">
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

    {{-- Edit Shipping Address Modal --}}
    <div id="editShippingModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeEditShippingModal()">
        </div>
        <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-indigo-500"></i> Edit Shipping Address
                    </h3>
                    <button onclick="closeEditShippingModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label
                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Name</label>
                        <input type="text" id="shipAddrName"
                            class="w-full h-10 rounded-lg border border-slate-200 text-sm font-medium px-3 focus:border-brand-500 focus:ring-0"
                            value="{{ $order->shippingAddress->name ?? '' }}">
                    </div>
                    <div>
                        <label
                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Phone</label>
                        <input type="text" id="shipAddrPhone"
                            class="w-full h-10 rounded-lg border border-slate-200 text-sm font-medium px-3 focus:border-brand-500 focus:ring-0"
                            value="{{ $order->shippingAddress->phone ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Address
                            Line 1</label>
                        <textarea id="shipAddrLine1" rows="2"
                            class="w-full rounded-lg border border-slate-200 text-sm font-medium px-3 py-2 focus:border-brand-500 focus:ring-0">{{ $order->shippingAddress->address_line_1 ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Address
                            Line 2</label>
                        <input type="text" id="shipAddrLine2"
                            class="w-full h-10 rounded-lg border border-slate-200 text-sm font-medium px-3 focus:border-brand-500 focus:ring-0"
                            value="{{ $order->shippingAddress->address_line_2 ?? '' }}">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">City</label>
                            <input type="text" id="shipAddrCity"
                                class="w-full h-10 rounded-lg border border-slate-200 text-sm font-medium px-3 focus:border-brand-500 focus:ring-0"
                                value="{{ $order->shippingAddress->city ?? '' }}">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Zip
                                Code</label>
                            <input type="text" id="shipAddrZip"
                                class="w-full h-10 rounded-lg border border-slate-200 text-sm font-medium px-3 focus:border-brand-500 focus:ring-0"
                                value="{{ $order->shippingAddress->zip_code ?? '' }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Country</label>
                            <select id="shipAddrCountry"
                                class="w-full h-10 rounded-lg border border-slate-200 text-sm font-medium px-3 focus:border-brand-500 focus:ring-0">
                                <option value="">Select Country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}"
                                        {{ ($order->shippingAddress->country_id ?? '') == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">State</label>
                            <select id="shipAddrState"
                                class="w-full h-10 rounded-lg border border-slate-200 text-sm font-medium px-3 focus:border-brand-500 focus:ring-0">
                                <option value="">Select State</option>
                                @if ($order->shippingAddress->state)
                                    <option value="{{ $order->shippingAddress->state_id }}" selected>
                                        {{ $order->shippingAddress->state->name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeEditShippingModal()"
                        class="btn btn-white text-sm">Cancel</button>
                    <button type="button" onclick="saveShippingAddress()" id="saveShipAddrBtn"
                        class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 text-white border-none text-sm">
                        <i class="fas fa-save mr-1"></i> Save Address
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Inputs --}}
    <input type="hidden" id="orderUuid" value="{{ $order->uuid }}">
    <input type="hidden" id="orderCurrency" value="{{ $order->currency->code ?? 'AED' }}">
    <input type="hidden" id="additionalInput" value="{{ $initialState['additional'] ?? 0 }}">

    <input type="hidden" id="billToUuid" value="{{ $order->customer->uuid }}">
    <input type="hidden" id="shipToUuid" value="{{ $order->shippingCustomer->uuid ?? $order->customer->uuid }}">

    {{-- Global Sidebar --}}
    <div id="sideDrawer" class="fixed inset-0 z-[100] hidden" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300"
            id="drawerBackdrop" onclick="hideSidebar()"></div>
        <div class="fixed inset-y-0 right-0 z-10 w-full max-w-md bg-white shadow-2xl translate-x-full transition-transform duration-300 ease-in-out"
            id="drawerPanel">
            <div id="drawerContent" class="h-full flex flex-col"></div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        const productsUrl = "{{ company_route('load-products') }}";
        const paymentTermsUrl = "{{ company_route('load-payment-terms') }}";
        const paymentMethodsUrl = "{{ company_route('load-payment-methods') }}";
        const orderUpdateUrl = "{{ company_route('sales-orders.update', ['sales_order' => $order->uuid]) }}";
        const orderFinalizeUrl = "{{ company_route('sales-orders.finalize.store', ['sales_order' => $order->uuid]) }}";

        const initialState = @json($initialState ?? null);
        const currentOrder = @json($order);
        const customerDiscountPercent = {{ $customerDiscount ?? 0 }};

        let allProducts = [];
        let paymentTerms = [];
        let paymentMethods = [];
        let allTaxGroups = @json($taxGroups);

        let cart = [];
        let selectedCartIndex = -1;

        // ---------------------------
        // CART INIT (UUID-safe)
        // ---------------------------
        if (initialState && initialState.items) {
            cart = initialState.items.map(item => ({
                type: item.type || 'variant',
                variant_id: item.variant_id || item.variantId || null,
                custom_name: item.custom_name || item.customName || null,
                custom_sku: item.custom_sku || item.customSku || null,
                qty: parseFloat(item.qty) || 0,
                unit_price: parseFloat(item.unit_price ?? item.unitPrice) || 0,
                discount_percent: parseFloat(item.discount_percent ?? item.discountPercent) || 0,
                tax_group_id: item.tax_group_id || null,
                temp_init: true
            }));

            $('#shippingInput').val(initialState.shipping || 0);
            $('#additionalInput').val(initialState.additional || 0);
            $('#taxGroupSelect').val(initialState.tax_group_id || '');
            $('#globalDiscountPercentInput').val(initialState.global_discount_percent || 0);
            $('#commentsInput').val(initialState.notes || '');
            $('#currencySelect').val(initialState.currency || 'AED');
            if (!$('#currencySelect').val()) {
                const firstOpt = $('#currencySelect option:first').val();
                if (firstOpt) $('#currencySelect').val(firstOpt);
            }
        } else if (currentOrder.items && currentOrder.items.length > 0) {
            cart = currentOrder.items.map(item => ({
                type: item.type || 'variant',
                // IMPORTANT: prefer uuid if present
                variant_id: item.product_variant?.uuid || item.product_variant_uuid || item
                    .product_variant_id || null,
                custom_name: item.custom_name || item.product_name || null,
                custom_sku: item.custom_sku || item.sku || null,
                qty: parseFloat(item.quantity) || 0,
                unit_price: parseFloat(item.unit_price) || 0,
                discount_percent: parseFloat(item.discount_percent) || 0,
                tax_group_id: item.tax_group_id || null,
                temp_init: true
            }));

            // Populate form fields from currentOrder
            $('#shippingInput').val(parseFloat(currentOrder.shipping_total) || 0);
            $('#globalDiscountPercentInput').val(parseFloat(currentOrder.global_discount_percent) || 0);
            if (currentOrder.tax_group_id) {
                $('#taxGroupSelect').val(currentOrder.tax_group_id);
            }
            if (currentOrder.notes) {
                $('#commentsInput').val(currentOrder.notes);
            }
            if (currentOrder.currency_id) {
                $('#currencySelect').val(currentOrder.currency_id);
            }
        }

        $(document).ready(function() {
            loadProducts();
            loadPaymentTerms();

            $('#productSearch').on('input', function() {
                renderProducts(this.value, $('#categoryFilter').val(), $('#brandFilter').val());
            });

            $('#categoryFilter, #brandFilter').on('change', function() {
                renderProducts($('#productSearch').val(), $('#categoryFilter').val(), $('#brandFilter')
                    .val());
            });

            $('#shippingInput, #additionalInput, #globalDiscountPercentInput').on('input', renderCart);
            $('#taxGroupSelect').on('change', renderCart);

            $('#modalAddBtn').on('click', function() {
                const variantUuid = $('input[name="selected_variant"]:checked').val();
                if (!variantUuid) return;

                let selectedVariant = null;
                let parentProduct = null;

                for (let p of allProducts) {
                    const found = (p.variants || []).find(v => (v.uuid + '') === (variantUuid + '') || (v
                        .id + '') === (
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

                    const formatted = dueDate.toISOString().split('T')[0];
                    $('#paymentDueDate').val(formatted);
                } else {
                    $('#paymentDueDate').val('-');
                }
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
                    populateCategoriesAndBrands();
                    renderProducts();
                    hydrateCart();
                },
                error: function() {
                    $('#productGrid').html(
                        '<div class="col-span-full text-center text-red-500">Failed to load catalog.</div>');
                }
            });
        }

        function getProductBrandName(p) {
            if (!p.brand) return null;
            if (typeof p.brand === 'object') return p.brand.name || p.brand.title || null;
            if (typeof p.brand === 'string' && p.brand.trim().startsWith('{')) {
                try {
                    const parsed = JSON.parse(p.brand);
                    return parsed.name || parsed.title || p.brand;
                } catch (e) {
                    return p.brand;
                }
            }
            return p.brand;
        }

        function populateCategoriesAndBrands() {
            // Categories
            const categories = [...new Set(allProducts.map(p => {
                if (!p.category) return null;
                if (typeof p.category === 'object') return p.category.name || p.category.title || null;
                return p.category;
            }).filter(Boolean))].sort();
            const catSelect = $('#categoryFilter');
            catSelect.empty().append(new Option('All Categories', ''));
            categories.forEach(cat => catSelect.append(new Option(cat, cat)));

            // Brands
            const brands = [...new Set(allProducts.map(p => getProductBrandName(p)).filter(Boolean))].sort();
            const brandSelect = $('#brandFilter');
            brandSelect.empty().append(new Option('All Brands', ''));
            brands.forEach(b => brandSelect.append(new Option(b, b)));
        }

        // SKU search helper (safe if sku not present)
        function productMatchesSearch(p, lower, raw) {
            if ((p.name || '').toLowerCase().includes(lower)) return true;
            const bName = getProductBrandName(p);
            if ((bName || '').toLowerCase().includes(lower)) return true;
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

        function renderProducts(search = '', category = '', brand = '') {
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

            if (brand) {
                filtered = filtered.filter(p => getProductBrandName(p) === brand);
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
                  <span class="text-xs font-black text-slate-900 font-mono">${price}</span>
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
                        const foundVar = (p.variants || []).find(v => ((v.uuid || v.id) + '') === (item.variant_id +
                            ''));
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

                const activeClass = isSelected ? 'ring-2 ring-brand-500 bg-brand-50/50' :
                    'hover:border-brand-200 hover:shadow-sm';

                let actionControlsHtml = '';
                if (isSelected) {
                    actionControlsHtml = `
            <div class="mt-2 pt-2 border-t border-slate-200/60 flex flex-wrap gap-2 items-center" onclick="event.stopPropagation()">
                <div class="flex items-center gap-1 rounded-lg bg-white border border-slate-200 p-1 shadow-sm">
                  <button type="button" onclick="posUpdateQty(-1)" class="w-7 h-7 flex items-center justify-center rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                    <i class="fas fa-minus text-[10px]"></i>
                  </button>
                  <span class="w-8 text-center text-xs font-bold text-slate-800">${qty}</span>
                  <button type="button" onclick="posUpdateQty(1)" class="w-7 h-7 flex items-center justify-center rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                    <i class="fas fa-plus text-[10px]"></i>
                  </button>
                </div>

                <button type="button" onclick="posPromptPrice()" class="h-8 px-3 rounded-lg bg-white border border-dashed border-slate-300 text-slate-600 text-[10px] font-bold uppercase hover:border-brand-400 hover:text-brand-600 transition-colors flex items-center gap-1.5 shadow-sm">
                   <i class="fas fa-tag"></i> Price
                </button>

                <div class="flex items-center rounded-lg bg-white border border-slate-200 p-1 shadow-sm gap-1">
                    <button type="button" onclick="posApplyDisc(5)" class="px-2 h-7 rounded bg-slate-50 hover:bg-slate-100 text-[10px] font-bold text-slate-600 border border-slate-100 transition-colors">5%</button>
                    <button type="button" onclick="posPromptDisc()" class="px-2 h-7 rounded bg-slate-50 hover:bg-slate-100 text-[10px] font-bold text-slate-600 border border-slate-100 transition-colors flex items-center gap-1"><i class="fas fa-percent"></i></button>
                 </div>

                 <select onchange="posUpdateLineTax(this.value)" class="h-8 w-24 rounded-lg border-slate-200 text-[10px] font-medium bg-white shadow-sm focus:border-brand-500 focus:ring-0">
                    <option value="">Global Tax</option>
                    @foreach ($taxGroups as $tg)
                      <option value="{{ $tg->id }}" ${item.tax_group_id == {{ $tg->id }} ? 'selected' : '' }>{{ $tg->name }}</option>
                    @endforeach
                 </select>

                <button type="button" onclick="posRemoveItem()" class="ml-auto h-8 w-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:scale-105 transition-all flex items-center justify-center shadow-sm">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>
            `;
                }

                const row = `
          <div onclick="selectCartItem(${index})"
            class="flex flex-col gap-0 p-2 rounded-xl bg-white border border-slate-100 transition-all group mb-1.5 cursor-pointer ${activeClass}">
            <div class="flex items-center gap-2">
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
                  <span class="text-xs font-black text-slate-900">${lineNet.toFixed(2)}</span>
                </div>
              </div>

              <div class="flex items-center gap-2 mt-1">
                <span class="text-[10px] font-bold text-slate-500">${qty} x ${unit.toFixed(2)}</span>
                ${discP > 0 ? `<span class="text-[9px] font-black text-brand-700 bg-brand-50 px-1.5 py-0.5 rounded-lg">(-${discP}%)</span>` : ''}
                ${isCustom ? `<span class="text-[9px] font-black text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded-lg">CUSTOM</span>` : ''}
              </div>
            </div>
            </div>
            ${actionControlsHtml}
          </div>
        `;
                container.append(row);
            });


            // Inline controls are now rendered within cart items

            const globalTaxRate = parseFloat($('#taxGroupSelect option:selected').data('rate')) || 0;
            const shipping = parseFloat($('#shippingInput').val()) || 0;
            const globalDiscP = parseFloat($('#globalDiscountPercentInput').val()) || 0;

            let itemTaxTotal = 0;
            cart.forEach(item => {
                const qty = parseFloat(item.qty) || 0;
                const unit = parseFloat(item.unit_price) || 0;
                const discP = parseFloat(item.discount_percent) || 0;
                const lineNet = (qty * unit) * (1 - discP / 100);

                let lineTaxRate = globalTaxRate;
                if (item.tax_group_id) {
                    const foundTg = allTaxGroups.find(tg => tg.id == item.tax_group_id);
                    if (foundTg) lineTaxRate = parseFloat(foundTg.rate) || 0;
                }
                itemTaxTotal += lineNet * (lineTaxRate / 100);
            });

            const shippingTax = shipping * (globalTaxRate / 100);
            const totalTax = itemTaxTotal + shippingTax;

            const subtotalWithItemTax = subtotal + itemTaxTotal;
            const globalDiscAmount = (subtotal + itemTaxTotal + shipping + shippingTax) * (globalDiscP / 100);
            const grandTotal = (subtotal + itemTaxTotal + shipping + shippingTax) - globalDiscAmount;

            $('#cartSubtotal').text(subtotal.toFixed(2));
            $('#cartItemTaxDisplay').text(itemTaxTotal.toFixed(2));
            $('#cartShippingTaxDisplay').text(shippingTax.toFixed(2));
            $('#globalDiscountAmountDisplay').text('- ' + globalDiscAmount.toFixed(2));
            $('#cartTaxAmount').text((itemTaxTotal + shippingTax).toFixed(2));
            $('#cartGrandTotal').text(grandTotal.toFixed(2));

            $('#itemCountLabel').text(cart.length + ' Lines • ' + countQty + ' Qty');
            $('#mobileCartCount').text(cart.length);
            $('.next-step-btn').prop('disabled', cart.length === 0).toggleClass('opacity-60 cursor-not-allowed', cart
                .length ===
                0);
        }

        window.switchMobileTab = function(tab) {
            if (tab === 'products') {
                $('#productSection').removeClass('hidden').addClass('flex');
                $('#cartSection').removeClass('flex').addClass('hidden');
                $('#tab-products').addClass('bg-brand-600 text-white shadow-sm').removeClass(
                    'text-slate-500 hover:bg-slate-100');
                $('#tab-cart').removeClass('bg-brand-600 text-white shadow-sm').addClass(
                    'text-slate-500 hover:bg-slate-100');
            } else {
                $('#productSection').removeClass('flex').addClass('hidden');
                $('#cartSection').removeClass('hidden').addClass('flex');
                $('#tab-cart').addClass('bg-brand-600 text-white shadow-sm').removeClass(
                    'text-slate-500 hover:bg-slate-100');
                $('#tab-products').removeClass('bg-brand-600 text-white shadow-sm').addClass(
                    'text-slate-500 hover:bg-slate-100');
            }
        };

        $(document).on('input', '#shippingInput, #globalDiscountPercentInput', renderCart);
        $(document).on('change', '#taxGroupSelect', renderCart);

        window.posUpdateLineTax = function(tgId) {
            if (selectedCartIndex !== -1 && cart[selectedCartIndex]) {
                cart[selectedCartIndex].tax_group_id = tgId || null;
                renderCart();
            }
        };

        window.selectCartItem = function(index) {
            selectedCartIndex = index;
            renderCart();
        };



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

        // ---- Edit Shipping Address Logic
        window.openEditShippingAddress = function() {
            $('#editShippingModal').removeClass('hidden');
        };

        window.closeEditShippingModal = function() {
            $('#editShippingModal').addClass('hidden');
        };

        // Country -> State cascade for shipping address modal
        $('#shipAddrCountry').on('change', function() {
            const countryId = $(this).val();
            const $stateSelect = $('#shipAddrState');
            $stateSelect.html('<option value="">Loading...</option>').prop('disabled', true);

            if (!countryId) {
                $stateSelect.html('<option value="">Select State</option>').prop('disabled', false);
                return;
            }

            $.get("{{ url('settings/countries') }}/" + countryId + "/states", function(data) {
                let options = '<option value="">Select State</option>';
                (data.states || data).forEach(function(s) {
                    options += `<option value="${s.id}">${s.name}</option>`;
                });
                $stateSelect.html(options).prop('disabled', false);
            }).fail(function() {
                $stateSelect.html('<option value="">Select State</option>').prop('disabled', false);
            });
        });

        window.saveShippingAddress = function() {
            const btn = $('#saveShipAddrBtn');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                url: "{{ company_route('sales-orders.update-shipping-address', ['sales_order' => $order->uuid]) }}",
                method: 'POST',
                data: {
                    _method: 'PUT',
                    _token: "{{ csrf_token() }}",
                    name: $('#shipAddrName').val(),
                    phone: $('#shipAddrPhone').val(),
                    address_line_1: $('#shipAddrLine1').val(),
                    address_line_2: $('#shipAddrLine2').val(),
                    city: $('#shipAddrCity').val(),
                    zip_code: $('#shipAddrZip').val(),
                    country_id: $('#shipAddrCountry').val(),
                    state_id: $('#shipAddrState').val(),
                },
                success: function(resp) {
                    if (resp.success) {
                        $('#shippingAddressContainer').html(resp.shipping_html);
                        closeEditShippingModal();
                        Swal.fire({
                            icon: 'success',
                            title: 'Address Updated',
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    let msg = xhr.responseJSON?.message || 'Failed to update address.';
                    if (errors) {
                        msg = Object.values(errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg,
                        confirmButtonColor: '#ef4444'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Address');
                }
            });
        };

        // ---- Customer Switcher Logic
        let currentSwitchType = 'BILLING';

        window.openCustomerSwitcher = function(type = 'BILLING') {
            currentSwitchType = type;
            const sideDrawer = $('#sideDrawer');
            const drawerContent = $('#drawerContent');
            const drawerBackdrop = $('#drawerBackdrop');
            const drawerPanel = $('#drawerPanel');

            // Loading state
            drawerContent.html(
                '<div class="flex items-center justify-center h-full"><i class="fas fa-circle-notch fa-spin text-2xl text-brand-500"></i></div>'
            );

            sideDrawer.removeClass('hidden');
            setTimeout(() => {
                drawerBackdrop.removeClass('opacity-0');
                drawerPanel.removeClass('translate-x-full');
            }, 10);

            $.ajax({
                url: "{{ company_route('sales-orders.customer-switcher', ['sales_order' => $order->uuid]) }}",
                data: {
                    type: type
                },
                success: function(html) {
                    drawerContent.html(html);
                }
            });
        };

        window.hideSidebar = function() {
            const sideDrawer = $('#sideDrawer');
            const drawerBackdrop = $('#drawerBackdrop');
            const drawerPanel = $('#drawerPanel');

            drawerBackdrop.addClass('opacity-0');
            drawerPanel.addClass('translate-x-full');
            setTimeout(() => sideDrawer.addClass('hidden'), 300);
        };

        window.setSidebarContent = function(html) {
            $('#drawerContent').html(html);
        };

        window.confirmCustomerSwitch = function(customerUuid, customerName) {
            Swal.fire({
                title: 'Change Customer?',
                text: `Switch ${currentSwitchType.toLowerCase()} address to ${customerName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, switch it',
                confirmButtonColor: '#4f46e5'
            }).then((result) => {
                if (result.isConfirmed) {
                    switchOrderCustomer(customerUuid);
                }
            });
        };

        function switchOrderCustomer(newUuid) {
            const url = "{{ company_route('sales-orders.update-customers', ['sales_order' => $order->uuid]) }}";

            const billToNow = $('#billToUuid').val();
            const shipToNow = $('#shipToUuid').val();

            const payload = {
                _method: 'PUT',
                _token: "{{ csrf_token() }}",
                bill_to: (currentSwitchType === 'BILLING') ? newUuid : billToNow,
                ship_to: (currentSwitchType === 'SHIPPING') ? newUuid : shipToNow
            };

            $.ajax({
                url: url,
                method: 'POST',
                data: payload,
                success: function(resp) {
                    if (resp.success) {
                        if (currentSwitchType === 'BILLING') {
                            $('#billingAddressContainer').html(resp.billing_html);
                            $('#billToUuid').val(newUuid);

                            if (resp.payment_term_id) {
                                $('#paymentTermSelect').val(resp.payment_term_id).trigger('change');
                            }
                        } else {
                            $('#shippingAddressContainer').html(resp.shipping_html);
                            $('#shipToUuid').val(newUuid);
                        }

                        hideSidebar();

                        Swal.fire({
                            icon: 'success',
                            title: 'Customer Updated',
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to process order.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg,
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        }

        function saveOrder(action) {
            const btn = $('.next-step-btn');
            const originalHtml = btn.html();

            // Basic POS Validations
            if (action === 'generate') {
                if (cart.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Cart',
                        text: 'You must add at least one item before generating an order.',
                        confirmButtonColor: '#ef4444'
                    });
                    return;
                }

                const termId = $('#paymentTermSelect').val();
                if (!termId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Payment Terms Required',
                        text: 'Please select a payment term before proceeding.',
                        confirmButtonColor: '#ef4444'
                    });
                    return;
                }
            }

            if (action === 'generate') {
                btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Finalizing...');
            }

            const payload = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                currency: $('#currencySelect').val(),
                tax_group_id: $('#taxGroupSelect').val(),
                payment_term_id: $('#paymentTermSelect').val(),
                due_date: $('#paymentDueDate').val(),
                shipping: $('#shippingInput').val(),
                global_discount_percent: $('#globalDiscountPercentInput').val() || 0,
                global_discount_amount: parseFloat($('#globalDiscountAmountDisplay').text().replace('- ', '')) || 0,
                additional: $('#additionalInput').val() || 0,
                notes: $('#commentsInput').val(),
                items: cart.map(i => ({
                    type: i.type || 'variant',
                    variant_id: (i.type === 'custom') ? null : (i.variant_id || i.variant?.id),
                    custom_name: (i.type === 'custom') ? (i.custom_name || null) : null,
                    custom_sku: (i.type === 'custom') ? (i.custom_sku || null) : null,
                    qty: i.qty,
                    unit_price: i.unit_price,
                    discount_percent: i.discount_percent,
                    tax_group_id: i.tax_group_id || null
                }))
            };

            const isGenerate = action === 'generate';
            const targetUrl = isGenerate ? orderFinalizeUrl : orderUpdateUrl;

            if (!isGenerate) {
                payload._method = 'PUT';
            }

            $.ajax({
                url: targetUrl,
                method: 'POST',
                data: payload,
                success: function(resp) {
                    if (isGenerate) {
                        if (resp.redirect) {
                            window.location.href = resp.redirect;
                        } else if (resp.ok) {
                            window.location.href =
                                "{{ company_route('sales-orders.show', ['sales_order' => $order->uuid]) }}";
                        }
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Draft Saved',
                            toast: true,
                            position: 'bottom-end',
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
