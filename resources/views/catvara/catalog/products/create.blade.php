@extends('catvara.layouts.app')

@section('title', 'Create Product')

@section('content')
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create New Product</h1>
            <p class="text-slate-500 mt-1">Define core details and generate variant combinations.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ company_route('catalog.products.index') }}"
                class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors shadow-sm">
                Cancel
            </a>
            <button type="button" id="btnSubmitTop"
                class="px-6 py-2.5 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all">
                <i class="fas fa-save mr-2"></i> Create Product
            </button>
        </div>
    </div>

    <form id="productForm" action="{{ company_route('catalog.products.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Left: Basic Info & Image (Box Modules) --}}
            <div class="lg:col-span-4 space-y-8">
                {{-- Module: Basic Info --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                            <i class="fas fa-info-circle text-sm"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">General Information</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Product Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                placeholder="e.g. Cotton T-Shirt" required>
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-2">Category
                                <span class="text-red-500">*</span></label>
                            <select name="category_id" id="category_id" class="select2 w-full" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="description"
                                class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                            <textarea name="description" id="description" rows="4"
                                class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                placeholder="Describe your product...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Module: Variant Settings --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                            <i class="fas fa-sliders-h text-sm"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Configure Variants</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div id="attrContainer" class="space-y-4">
                            @forelse ($attributes as $attr)
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ $attr->name }}</label>
                                    <select class="select2 w-full attribute-selector" data-attr-id="{{ $attr->id }}"
                                        data-attr-name="{{ $attr->name }}" multiple>
                                        @foreach ($attr->values as $val)
                                            <option value="{{ $val->id }}">{{ $val->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 italic">No attributes found. Please create some attributes
                                    first.</p>
                            @endforelse
                        </div>

                        <div class="pt-4 border-t border-slate-50 flex flex-col gap-3">
                            <button type="button" id="btnGenerate"
                                class="w-full px-5 py-3 text-sm font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-xl transition-all border border-brand-100">
                                <i class="fas fa-bolt mr-2"></i> Generate Combinations
                            </button>
                            <button type="button" id="btnClear"
                                class="text-xs text-center text-slate-400 hover:text-slate-600 transition-colors uppercase font-bold tracking-widest">
                                Clear Selection
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Module: Image --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                            <i class="fas fa-image text-sm"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Product Image</h3>
                    </div>
                    <div class="p-6">
                        <div class="relative group cursor-pointer border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden hover:border-brand-300 transition-colors bg-slate-50/50"
                            onclick="document.getElementById('image').click()">
                            <input type="file" name="image" id="image" class="hidden" accept="image/*"
                                onchange="previewImage(this)">
                            <div id="imagePlaceholder"
                                class="aspect-square flex flex-col items-center justify-center p-8 text-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 mb-4 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-cloud-upload-alt text-xl"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-600">Click to upload</p>
                                <p class="text-xs text-slate-400 mt-1">PNG, JPG up to 5MB</p>
                            </div>
                            <img id="imagePreview" src="#" alt="Preview" class="hidden w-full aspect-square object-cover">
                            <div id="removeImageBtn"
                                class="hidden absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur shadow-sm items-center justify-center text-red-500 hover:bg-white transition-all cursor-pointer"
                                onclick="event.stopPropagation(); removeImage();">
                                <i class="fas fa-times"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Variants Grid --}}
            <div class="lg:col-span-8 space-y-8">
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                                <i class="fas fa-layer-group text-sm"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Product Variants</h3>
                        </div>
                        <span id="variantCountDisplay"
                            class="px-3 py-1 bg-brand-50 text-brand-700 text-xs font-bold rounded-full border border-brand-100">0
                            Variants</span>
                    </div>

                    <div class="overflow-x-auto min-h-[300px]">
                        <table class="w-full text-left border-collapse" id="variantTable">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-50">
                                        Variant Details</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-50 w-44">
                                        SKU</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-50 w-32 text-right">
                                        Cost Price</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-50 w-32 text-right">
                                        Selling Price</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-50 w-16 text-center">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="emptyRow">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-4">
                                                <i class="fas fa-magic text-2xl"></i>
                                            </div>
                                            <p class="text-sm font-medium text-slate-400">Generate some variants to
                                                start!</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                        <p class="text-xs text-slate-400 italic">Variants are created using cartesian combinations of
                            selected attributes.</p>
                        <button type="button" id="btnSubmitBottom"
                            class="px-8 py-3 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all">
                            <i class="fas fa-check-circle mr-2"></i> Create Product & Variants
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Category Based Attribute Loading
            $('#category_id').on('change', function () {
                const catId = $(this).val();
                if (!catId) return;

                const url = "{{ company_route('catalog.categories.attributes', ['category' => ':id']) }}".replace(':id', catId);

                $.get(url, function (data) {
                    const container = $('#attrContainer');
                    container.empty();

                    if (!data || data.length === 0) {
                        container.html('<p class="text-sm text-slate-500 italic">No attributes assigned to this category.</p>');
                        return;
                    }

                    data.forEach(function (attr) {
                        const html = `
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">${attr.name}</label>
                                                        <select class="select2 w-full attribute-selector" data-attr-id="${attr.id}" data-attr-name="${attr.name}" multiple>
                                                            ${(attr.values || []).map(v => `<option value="${v.id}">${v.value}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                `;
                        container.append(html);
                    });

                    $('.attribute-selector').select2({ width: '100%', placeholder: "Select values..." });
                });
            });

            // Variant Generation Logic
            $('#btnGenerate').on('click', function () {
                let selectedAttrs = [];
                $('.attribute-selector').each(function () {
                    let attrId = $(this).data('attr-id');
                    let values = $(this).select2('data');

                    if (values && values.length > 0) {
                        selectedAttrs.push({
                            id: attrId,
                            values: values.map(v => ({ id: v.id, text: v.text }))
                        });
                    }
                });

                if (selectedAttrs.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Selection Missing', text: 'Please select at least one attribute value.' });
                    return;
                }

                const combinations = cartesian(selectedAttrs.map(a => a.values));
                const tbody = $('#variantTable tbody');
                tbody.empty();

                combinations.forEach((combo, index) => {
                    const name = Array.isArray(combo) ? combo.map(c => c.text).join(' / ') : combo.text;
                    const attrIds = Array.isArray(combo) ? combo.map(c => c.id) : [combo.id];
                    const rowId = `row_${Date.now()}_${index}`;

                    const tr = `
                                                <tr id="${rowId}" class="variant-row group transition-colors hover:bg-slate-50/50">
                                                    <td class="px-6 py-4 border-b border-slate-50">
                                                        <div class="text-sm font-bold text-slate-800">${name}</div>
                                                        <input type="hidden" name="variants[${index}][name]" value="${name}">
                                                        ${attrIds.map(id => `<input type="hidden" name="variants[${index}][attributes][]" value="${id}">`).join('')}
                                                    </td>
                                                    <td class="px-6 py-4 border-b border-slate-50">
                                                        <input type="text" name="variants[${index}][sku]" placeholder="Auto-gen SKU"
                                                            class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm py-2">
                                                    </td>
                                                    <td class="px-6 py-4 border-b border-slate-50">
                                                        <div class="flex items-center justify-end">
                                                            <input type="number" step="0.01" name="variants[${index}][cost]" placeholder="0.00"
                                                                class="w-24 rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm py-2 text-right">
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 border-b border-slate-50">
                                                        <div class="flex items-center justify-end">
                                                            <input type="number" step="0.01" name="variants[${index}][price]" placeholder="0.00"
                                                                class="w-24 rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500 text-sm py-2 text-right font-bold text-brand-600 bg-brand-50/30">
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 border-b border-slate-50 text-center">
                                                        <button type="button" class="text-slate-300 hover:text-red-500 transition-colors" onclick="$('#${rowId}').remove(); refreshVariantCount();">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            `;
                    tbody.append(tr);
                });

                refreshVariantCount();
            });

            $('#btnClear').on('click', function () {
                $('.attribute-selector').val(null).trigger('change');
                $('#variantTable tbody').html('<tr id="emptyRow"><td colspan="5" class="px-6 py-16 text-center text-slate-400">No variants generated yet.</td></tr>');
                refreshVariantCount();
            });

            // Cartesian Helper
            function cartesian(args) {
                var r = [], max = args.length - 1;
                function helper(arr, i) {
                    for (var j = 0, l = args[i].length; j < l; j++) {
                        var a = arr.slice(0);
                        a.push(args[i][j]);
                        if (i == max) r.push(a);
                        else helper(a, i + 1);
                    }
                }
                helper([], 0);
                return r;
            }

            window.refreshVariantCount = function () {
                const count = $('#variantTable tbody tr.variant-row').length;
                $('#variantCountDisplay').text(`${count} Variants`);
                if (count === 0 && $('#emptyRow').length === 0) {
                    $('#variantTable tbody').html('<tr id="emptyRow"><td colspan="5" class="px-6 py-16 text-center"><p class="text-sm font-medium text-slate-400">Generate some variants to start!</p></td></tr>');
                } else if (count > 0) {
                    $('#emptyRow').remove();
                }
            };

            // Form Submit Logic (AJAX as requested for variants handling)
            function submitForm() {
                if (!$('#name').val()) {
                    Swal.fire('Error', 'Product Name is required', 'error');
                    return;
                }
                if ($('#variantTable tbody tr.variant-row').length === 0) {
                    Swal.fire('Error', 'Please generate at least one variant', 'error');
                    return;
                }

                const fd = new FormData(document.getElementById('productForm'));

                $.ajax({
                    url: $('#productForm').attr('action'),
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) window.location.href = res.redirect;
                        else Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    },
                    error: function (err) {
                        Swal.fire('Error', err.responseJSON?.message || 'Server error occurred', 'error');
                    }
                });
            }

            $('#btnSubmitTop, #btnSubmitBottom').on('click', submitForm);
        });

        // Image Preview Helpers
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#imagePreview').attr('src', e.target.result).removeClass('hidden');
                    $('#imagePlaceholder').addClass('hidden');
                    $('#removeImageBtn').removeClass('hidden').addClass('flex');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeImage() {
            $('#image').val('');
            $('#imagePreview').addClass('hidden');
            $('#imagePlaceholder').removeClass('hidden');
            $('#removeImageBtn').addClass('hidden').removeClass('flex');
        }
    </script>
@endpush