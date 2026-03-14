@extends('catvara.layouts.app')

@section('title', 'Box Labels — ' . $order->order_number)

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
            <span class="text-slate-600">Box Labels</span>
        </nav>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">
                    <i class="fas fa-box text-brand-400 mr-2"></i> Box Labels
                </h2>
                <p class="text-slate-400 text-sm mt-1 font-medium">
                    Order {{ $order->order_number }} &bull; {{ $order->created_at->format('M d, Y') }}
                    @if ($order->customer)
                        &bull; {{ $order->customer->display_name ?? $order->customer->legal_name }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ company_route('sales-orders.show', ['sales_order' => $order->uuid]) }}" class="btn btn-white">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Order
                </a>
            </div>
        </div>

        @if ($boxes->isEmpty())
            <div class="card bg-white border-slate-100 shadow-soft p-12 text-center">
                <i class="fas fa-box-open text-4xl text-slate-300 mb-3"></i>
                <p class="text-slate-500 font-bold">No boxes found</p>
                <p class="text-sm text-slate-400 mt-1">Box assignments are created when generating a delivery note.</p>
            </div>
        @else
            {{-- Ship To Card --}}
            @if ($order->shippingAddress)
                <div class="card bg-white border-slate-100 shadow-soft p-5">
                    <div class="flex items-start gap-3">
                        <div
                            class="h-9 w-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Ship To</p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ $order->shippingAddress->name }}<br>
                                {!! $order->shippingAddress->render() !!}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Box Cards --}}
            @foreach ($boxes as $boxNumber => $boxItems)
                @php
                    $boxTotal = $boxItems->sum(fn($bi) => (float) $bi->quantity * (float) $bi->orderItem->unit_price);
                    $boxQty = $boxItems->sum('quantity');
                @endphp
                <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
                    <div class="p-5 border-b border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-black uppercase tracking-wider">
                                Box {{ $boxNumber }}
                            </span>
                            <span class="text-xs text-slate-400 font-bold">{{ $boxItems->count() }}
                                {{ Str::plural('item', $boxItems->count()) }}</span>
                        </div>
                        <a href="{{ company_route('sales-orders.box-label-preview', ['sales_order' => $order->uuid, 'boxIndex' => $boxNumber]) }}"
                            target="_blank"
                            class="btn bg-brand-400 hover:bg-brand-500 text-white text-xs px-4 py-2 rounded-lg font-bold shadow-sm">
                            <i class="fas fa-eye mr-1.5"></i> Preview
                        </a>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/80">
                                    <th class="px-5 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Description</th>
                                    <th
                                        class="px-5 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                                        Qty</th>
                                    <th
                                        class="px-5 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                        Unit Price</th>
                                    <th
                                        class="px-5 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                        Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($boxItems as $bi)
                                    @php
                                        $item = $bi->orderItem;
                                        $desc = $item->product_name;
                                        if ($item->variant_description) {
                                            $desc .= ' — ' . $item->variant_description;
                                        }
                                        $lineAmount = (float) $bi->quantity * (float) $item->unit_price;
                                    @endphp
                                    <tr class="hover:bg-orange-50/30 transition-colors">
                                        <td class="px-5 py-3">
                                            <p class="text-sm font-semibold text-slate-800">{{ $desc }}</p>
                                            @if ($item->productVariant?->sku)
                                                <p class="text-[11px] text-slate-400 mt-0.5">SKU:
                                                    {{ $item->productVariant->sku }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <span
                                                class="text-sm font-bold text-slate-700">{{ (float) $bi->quantity }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-right text-sm text-slate-600">
                                            {{ money($item->unit_price, $order->currency->code) }}
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <span
                                                class="text-sm font-bold text-slate-800">{{ money($lineAmount, $order->currency->code) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50 border-t border-slate-200">
                                    <td class="px-5 py-3 text-xs font-extrabold text-slate-600">Box Total</td>
                                    <td class="px-5 py-3 text-center text-xs font-extrabold text-slate-700">
                                        {{ (float) $boxQty }}</td>
                                    <td class="px-5 py-3"></td>
                                    <td class="px-5 py-3 text-right text-xs font-extrabold text-slate-800">
                                        {{ money($boxTotal, $order->currency->code) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach

            {{-- Grand Total --}}
            <div class="card bg-white border-slate-100 shadow-soft p-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-bold text-slate-600">
                        Total: {{ $boxes->count() }} {{ Str::plural('box', $boxes->count()) }}
                        &bull; {{ (float) $order->boxItems->sum('quantity') }} units
                    </div>
                    <div class="text-lg font-extrabold text-slate-800">
                        {{ money($order->boxItems->sum(fn($bi) => (float) $bi->quantity * (float) $bi->orderItem->unit_price), $order->currency->code) }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
