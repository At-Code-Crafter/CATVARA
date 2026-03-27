@extends('catvara.layouts.app')

@section('title', 'Edit Product')

@section('content')
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $product->name }}</h1>
                <span
                    class="px-2.5 py-1 {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }} text-[10px] font-bold uppercase tracking-wider rounded-md border border-emerald-100">
                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-slate-500 text-sm">Update product details, pricing channels, and media.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ company_route('catalog.products.index') }}"
                class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors shadow-sm">
                Back to List
            </a>
            <button type="submit" form="product-form"
                class="px-6 py-2.5 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>

    <form id="product-form" action="{{ company_route('catalog.products.update', ['product' => $product->id]) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Main (Left) --}}
            <div class="lg:col-span-8 space-y-8">
                {{-- Module: Basic Info --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                            <i class="fas fa-info-circle text-sm"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Product Details</h3>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Product Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}"
                                    class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm text-sm py-2.5 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                                <select name="category_id" id="category_id" class="no-select2 w-full">
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-400 mt-1">Type a new name to create category on save</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Brand</label>
                                <select name="brand_id" id="brand_id" class="no-select2 w-full">
                                    <option value="">Select or type to create (Optional)</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-400 mt-1">Type a new name to create brand on save</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Slug</label>
                                <div
                                    class="flex items-center px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-400 text-sm italic">
                                    /products/{{ $product->slug }}
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                                <textarea name="description" rows="4"
                                    class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm text-sm py-2.5 transition-all">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Module: Variants & Pricing --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                                <i class="fas fa-tags text-sm"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Variants & Pricing</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="px-3 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase rounded-md border border-slate-200">
                                Currency: {{ $currency->symbol }}
                            </span>
                            <button type="button" id="toggleAddVariant"
                                class="px-4 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors shadow-sm">
                                <i class="fas fa-plus mr-1.5"></i> Add Variant
                            </button>
                        </div>
                    </div>

                    {{-- Add New Variant Form (Hidden by Default) --}}
                    <div id="addVariantPanel" class="hidden border-b border-slate-200 bg-brand-50/20 p-6">
                        <h4 class="text-sm font-bold text-slate-700 mb-4"><i
                                class="fas fa-plus-circle mr-1.5 text-brand-500"></i> New Variant</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">SKU <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="newVariantSku" placeholder="e.g. PROD-BLK-XL"
                                    class="w-full rounded-lg border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cost Price</label>
                                <input type="number" step="0.01" id="newVariantCost" placeholder="0.00"
                                    class="w-full rounded-lg border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm py-2">
                            </div>
                            @foreach ($channels as $ch)
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1.5">{{ strtoupper($ch->name) }}
                                        Price</label>
                                    <input type="number" step="0.01" data-channel-id="{{ $ch->id }}"
                                        placeholder="0.00"
                                        class="new-variant-price w-full rounded-lg border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm py-2">
                                </div>
                            @endforeach
                        </div>

                        @if ($attributes->count())
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Attributes</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach ($attributes as $attr)
                                        <div>
                                            <label
                                                class="block text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $attr->name }}</label>
                                            <select data-attribute-id="{{ $attr->id }}"
                                                class="new-variant-attr w-full rounded-lg border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm py-2">
                                                <option value="">— None —</option>
                                                @foreach ($attr->values as $val)
                                                    <option value="{{ $val->id }}">{{ $val->value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3">
                            <button type="button" id="saveNewVariant"
                                class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                                <i class="fas fa-check mr-1.5"></i> Save Variant
                            </button>
                            <button type="button" id="cancelAddVariant"
                                class="px-5 py-2 text-xs font-bold text-slate-600 bg-white hover:bg-slate-50 rounded-lg border border-slate-200 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                        Variant SKU</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 text-center w-24">
                                        Stock</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 text-right w-32">
                                        Cost</th>
                                    @foreach ($channels as $ch)
                                        <th
                                            class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 text-right w-40">
                                            {{ strtoupper($ch->name) }}
                                        </th>
                                    @endforeach
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 text-center w-28">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($product->variants as $variant)
                                    <tr class="variant-row transition-colors border-b border-slate-50"
                                        data-variant-id="{{ $variant->id }}">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" class="sr-only peer variant-active-toggle"
                                                        data-variant-id="{{ $variant->id }}"
                                                        {{ $variant->is_active ? 'checked' : '' }}>
                                                    <div
                                                        class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none ring-2 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500">
                                                    </div>
                                                </label>
                                                <div>
                                                    <input type="text" value="{{ $variant->sku }}"
                                                        class="variant-sku w-full min-w-[200px] rounded-lg focus:border-brand-500 focus:ring-brand-500 text-sm py-1.5 font-medium"
                                                        data-variant-id="{{ $variant->id }}">
                                                    <div class="mt-1 d-flex flex-wrap gap-1">
                                                        @foreach ($variant->attributeValues as $val)
                                                            <span
                                                                class="inline-flex items-center px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[9px] font-bold uppercase rounded border border-slate-200">
                                                                {{ $val->attribute->name }}: {{ $val->value }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <div class="flex flex-col items-center gap-1.5">
                                                <span
                                                    class="text-sm font-bold {{ $variant->inventory->sum('quantity') > 0 ? 'text-slate-700' : 'text-slate-300' }}">
                                                    {{ (float) $variant->inventory->sum('quantity') }}
                                                </span>
                                                <a href="{{ company_route('inventory.variant.details', ['product_variant' => $variant->id]) }}"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-50 text-slate-400 hover:bg-brand-50 hover:text-brand-600 transition-all border border-slate-200"
                                                    title="View Inventory Details">
                                                    <i class="fas fa-boxes text-[10px]"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <input type="number" step="0.01" value="{{ $variant->cost_price }}"
                                                class="variant-cost w-24 rounded-lg focus:border-brand-500 focus:ring-brand-500 text-sm py-1.5 text-right"
                                                data-variant-id="{{ $variant->id }}">
                                        </td>
                                        @foreach ($channels as $ch)
                                            @php
                                                $price = $variant->prices->where('price_channel_id', $ch->id)->first();
                                                $val = $price ? $price->price : '';
                                            @endphp
                                            <td class="px-6 py-5 text-right">
                                                <input type="number" step="0.01" value="{{ $val }}"
                                                    class="variant-price w-32 rounded-lg border-brand-100 bg-brand-50/20 focus:border-brand-500 focus:ring-brand-500 text-sm py-1.5 text-right font-bold text-brand-600"
                                                    data-variant-id="{{ $variant->id }}"
                                                    data-channel-id="{{ $ch->id }}">
                                            </td>
                                        @endforeach
                                        <td class="px-6 py-5 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" onclick="saveVariant({{ $variant->id }})"
                                                    class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors flex items-center justify-center border border-emerald-200"
                                                    title="Save Changes">
                                                    <i class="fas fa-save text-xs"></i>
                                                </button>
                                                @if (auth()->user()->isSuperAdmin())
                                                    <button type="button"
                                                        onclick="deleteVariant({{ $variant->id }}, '{{ addslashes($variant->sku) }}')"
                                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors flex items-center justify-center border border-red-200"
                                                        title="Delete Variant">
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if ($product->variants->isEmpty())
                            <div class="text-center py-10 text-slate-400">
                                <i class="fas fa-box-open text-2xl mb-2 opacity-40"></i>
                                <p class="text-sm font-medium">No variants yet. Click "Add Variant" to create one.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar (Right) --}}
            <div class="lg:col-span-4 space-y-8">
                {{-- Module: Image --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                            <i class="fas fa-image text-sm"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Main Image</h3>
                    </div>
                    <div class="p-6">
                        <input type="file" name="image" id="image" class="filepond" accept="image/*">
                    </div>
                </div>

                {{-- Module: Visibility --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Status</h3>
                            <p class="text-xs text-slate-400 mt-1">Visible in frontend catalog.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shadow-sm">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                {{ $product->is_active ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600">
                            </div>
                        </label>
                    </div>

                    <div class="space-y-4">
                        <button type="submit" form="product-form"
                            class="w-full px-5 py-3 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all">
                            <i class="fas fa-save mr-2"></i> Update Product
                        </button>
                        <div class="grid grid-cols-2 gap-3 mt-8">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Created</p>
                                <p class="text-xs font-bold text-slate-700">{{ $product->created_at->format('d M, Y') }}
                                </p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Last Update
                                </p>
                                <p class="text-xs font-bold text-slate-700">{{ $product->updated_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Audit / Quick Links --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-link text-sm"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Quick Actions</h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <a href="{{ company_route('inventory.index') }}"
                            class="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                            <span class="text-sm font-medium text-slate-600 group-hover:text-brand-600"><i
                                    class="fas fa-boxes mr-3 text-slate-400 group-hover:text-brand-500"></i> Stock
                                Levels</span>
                            <i
                                class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="#"
                            class="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                            <span class="text-sm font-medium text-slate-600 group-hover:text-brand-600"><i
                                    class="fas fa-history mr-3 text-slate-400 group-hover:text-brand-500"></i> Audit
                                Logs</span>
                            <i
                                class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const PRODUCT_ID = {{ $product->id }};
        const VARIANT_STORE_URL = "{{ company_route('catalog.products.variants.store', ['product' => $product->id]) }}";
        const VARIANT_UPDATE_URL =
            "{{ company_route('catalog.products.variants.update', ['product' => $product->id, 'variant' => '__VID__']) }}";
        const VARIANT_DELETE_URL =
            "{{ company_route('catalog.products.variants.destroy', ['product' => $product->id, 'variant' => '__VID__']) }}";

        $(document).ready(function() {
            // ---------- Select2 ----------
            $('#category_id').select2({
                width: '100%',
                tags: true,
                placeholder: 'Select or type to create',
                allowClear: true,
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') return null;
                    return {
                        id: 'new:' + term,
                        text: term,
                        newTag: true
                    };
                }
            });

            $('#brand_id').select2({
                width: '100%',
                tags: true,
                placeholder: 'Select or type to create (Optional)',
                allowClear: true,
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') return null;
                    return {
                        id: 'new:' + term,
                        text: term,
                        newTag: true
                    };
                }
            });

            // ---------- FilePond ----------
            FilePond.registerPlugin(FilePondPluginImagePreview);

            FilePond.create(document.querySelector('.filepond'), {
                allowImagePreview: true,
                imagePreviewHeight: 250,
                labelIdle: 'Drag & Drop your image or <span class="filepond--label-action">Browse</span>',
                credits: false,
                @if (!empty($product->image))
                    files: [{
                        source: '{{ asset('storage/' . $product->image) }}',
                        options: {
                            type: 'local'
                        }
                    }],
                @endif
                server: {
                    load: (source, load, error, progress, abort, headers) => {
                        fetch(source).then(res => res.blob()).then(load);
                    }
                }
            });

            // ---------- Add Variant Panel ----------
            $('#toggleAddVariant').on('click', function() {
                $('#addVariantPanel').slideToggle(200);
            });

            $('#cancelAddVariant').on('click', function() {
                $('#addVariantPanel').slideUp(200);
                clearNewVariantForm();
            });

            // ---------- Save New Variant ----------
            $('#saveNewVariant').on('click', function() {
                const btn = $(this);
                const sku = $('#newVariantSku').val().trim();

                if (!sku) {
                    Swal.fire('Validation', 'SKU is required.', 'warning');
                    return;
                }

                // Collect prices
                const prices = {};
                $('.new-variant-price').each(function() {
                    const chId = $(this).data('channel-id');
                    const val = $(this).val();
                    if (val) prices[chId] = val;
                });

                // Collect attribute values
                const attributeValues = [];
                $('.new-variant-attr').each(function() {
                    const val = $(this).val();
                    if (val) attributeValues.push(parseInt(val));
                });

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1.5"></i> Saving...');

                $.ajax({
                    url: VARIANT_STORE_URL,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        sku: sku,
                        cost_price: $('#newVariantCost').val() || 0,
                        attribute_values: attributeValues,
                        prices: prices,
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Variant Added',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.errors ?
                            Object.values(xhr.responseJSON.errors).flat().join('\n') :
                            (xhr.responseJSON?.message || 'Something went wrong.');
                        Swal.fire('Error', msg, 'error');
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-check mr-1.5"></i> Save Variant');
                    }
                });
            });
        });

        // ---------- Save Existing Variant (inline) ----------
        function saveVariant(variantId) {
            const row = $(`.variant-row[data-variant-id="${variantId}"]`);
            const sku = row.find('.variant-sku').val().trim();
            const cost = row.find('.variant-cost').val() || 0;
            const active = row.find('.variant-active-toggle').is(':checked') ? 1 : 0;

            if (!sku) {
                Swal.fire('Validation', 'SKU cannot be empty.', 'warning');
                return;
            }

            // Collect channel prices
            const prices = {};
            row.find('.variant-price').each(function() {
                const chId = $(this).data('channel-id');
                prices[chId] = $(this).val() || 0;
            });

            const saveBtn = row.find('button[title="Save Changes"]');
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin text-xs"></i>');

            $.ajax({
                url: VARIANT_UPDATE_URL.replace('__VID__', variantId),
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    sku: sku,
                    cost_price: cost,
                    is_active: active,
                    prices: prices,
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: res.message,
                        timer: 1200,
                        showConfirmButton: false
                    });
                    saveBtn.prop('disabled', false).html('<i class="fas fa-save text-xs"></i>');
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join('\n') :
                        (xhr.responseJSON?.message || 'Something went wrong.');
                    Swal.fire('Error', msg, 'error');
                    saveBtn.prop('disabled', false).html('<i class="fas fa-save text-xs"></i>');
                }
            });
        }

        // ---------- Delete Variant (super admin only) ----------
        function deleteVariant(variantId, sku) {
            Swal.fire({
                title: 'Delete Variant?',
                html: `Are you sure you want to delete variant <strong>${sku}</strong>? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete',
            }).then(result => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: VARIANT_DELETE_URL.replace('__VID__', variantId),
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $(`.variant-row[data-variant-id="${variantId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Could not delete variant.',
                            'error');
                    }
                });
            });
        }

        function clearNewVariantForm() {
            $('#newVariantSku').val('');
            $('#newVariantCost').val('');
            $('.new-variant-price').val('');
            $('.new-variant-attr').val('');
        }
    </script>
@endpush
