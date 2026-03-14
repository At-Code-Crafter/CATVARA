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

        {{-- Box Labels Table --}}
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-boxes-stacked text-brand-400"></i> Box Items
                </h3>
                <span class="text-xs font-bold text-slate-400">{{ $order->items->count() }}
                    {{ Str::plural('box', $order->items->count()) }}</span>
            </div>
            <div class="p-0">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80">
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Box No.</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Description
                            </th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">
                                Qty</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">
                                Unit Price</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">
                                Box Amount</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">
                                Label</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($order->items as $index => $item)
                            @php
                                $boxNo = $index + 1;
                                $desc = $item->product_name;
                                if ($item->variant_description) {
                                    $desc .= ' — ' . $item->variant_description;
                                }
                                $boxAmount = (float) $item->line_total;
                            @endphp
                            <tr class="hover:bg-orange-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-100 text-xs font-bold text-slate-700">
                                        Box {{ $boxNo }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-slate-800">{{ $desc }}</p>
                                    @if ($item->productVariant?->sku)
                                        <p class="text-[11px] text-slate-400 mt-0.5">SKU: {{ $item->productVariant->sku }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-bold text-slate-700">{{ (int) $item->quantity }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-slate-600">
                                    {{ money($item->unit_price, $order->currency->code) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="text-sm font-bold text-slate-800">{{ money($boxAmount, $order->currency->code) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ company_route('sales-orders.box-label-preview', ['sales_order' => $order->uuid, 'boxIndex' => $boxNo]) }}"
                                        target="_blank"
                                        class="btn bg-brand-400 hover:bg-brand-500 text-white text-xs px-4 py-2 rounded-lg font-bold shadow-sm">
                                        <i class="fas fa-eye mr-1.5"></i> Preview
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 border-t-2 border-slate-200">
                            <td class="px-6 py-4 text-sm font-extrabold text-slate-800" colspan="2">
                                Total Boxes: {{ $order->items->count() }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-extrabold text-slate-800">
                                {{ (int) $order->items->sum('quantity') }}
                            </td>
                            <td class="px-6 py-4"></td>
                            <td class="px-6 py-4 text-right text-sm font-extrabold text-slate-800">
                                {{ money($order->grand_total, $order->currency->code) }}
                            </td>
                            <td class="px-6 py-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Remarks Card --}}
        <div class="card bg-white border-slate-100 shadow-soft">
            <div class="p-5 border-b border-slate-50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-brand-400"></i> Remarks
                </h3>
            </div>
            <div class="p-5 space-y-2">
                @foreach ($order->items as $index => $item)
                    <div class="flex items-center gap-3 text-sm">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-slate-100 text-xs font-bold text-slate-600">Box
                            {{ $index + 1 }}</span>
                        <span class="text-slate-600">
                            {{ $item->product_name }}{{ $item->variant_description ? ' — ' . $item->variant_description : '' }}
                            <span class="text-slate-400 ml-1">(Qty: {{ (int) $item->quantity }})</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
