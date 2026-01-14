@extends('catvara.layouts.app')

@section('title', 'Edit Product')

@section('content')
    <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $product->name }}</h1>
                    <span class="px-2.5 py-1 {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }} text-[10px] font-bold uppercase tracking-wider rounded-md border border-emerald-100">
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
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Product Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" value="{{ old('name', $product->name) }}"
                                        class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm text-sm py-2.5 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                                    <select name="category_id" class="select2 w-full">
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Slug</label>
                                    <div class="flex items-center px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-400 text-sm italic">
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
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Multi-Channel Pricing</h3>
                            </div>
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase rounded-md border border-slate-200">Currency: {{ $currency->symbol }}</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">Variant SKU</th>
                                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 text-center w-24">Stock</th>
                                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 text-right w-32">Cost</th>
                                        @foreach ($channels as $ch)
                                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 text-right w-40">{{ strtoupper($ch->name) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($product->variants as $variant)
                                        <tr class="transition-colors">
                                            <td class="px-6 py-5">
                                                <div class="flex items-center gap-3">
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" name="variants[{{ $variant->id }}][is_active]" value="1" class="sr-only peer" {{ $variant->is_active ? 'checked' : '' }}>
                                                        <div class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none ring-2 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
                                                    </label>
                                                    <div>
                                                        <input type="text" name="variants[{{ $variant->id }}][sku]" value="{{ $variant->sku }}"
                                                               class="w-full min-w-[200px] rounded-lg focus:border-brand-500 focus:ring-brand-500 text-sm py-1.5 font-medium">
                                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                                            @foreach ($variant->attributeValues as $val)
                                                                <span class="inline-flex items-center px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[9px] font-bold uppercase rounded border border-slate-200">
                                                                    {{ $val->attribute->name }}: {{ $val->value }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <a href="{{ company_route('inventory.index', ['sku' => $variant->sku]) }}" 
                                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:bg-brand-50 hover:text-brand-600 transition-all border border-slate-200"
                                                   title="Manage Stock">
                                                    <i class="fas fa-boxes text-xs"></i>
                                                </a>
                                            </td>
                                            <td class="px-6 py-5 text-right">
                                                <input type="number" step="0.01" name="variants[{{ $variant->id }}][cost_price]" value="{{ $variant->cost_price }}"
                                                       class="w-24 rounded-lg focus:border-brand-500 focus:ring-brand-500 text-sm py-1.5 text-right">
                                            </td>
                                            @foreach ($channels as $ch)
                                                @php
                                                    $price = $variant->prices->where('price_channel_id', $ch->id)->first();
                                                    $val = $price ? $price->price : '';
                                                @endphp
                                                <td class="px-6 py-5 text-right">
                                                    <input type="number" step="0.01" name="prices[{{ $variant->id }}][{{ $ch->id }}]" value="{{ $val }}"
                                                           class="w-32 rounded-lg border-brand-100 bg-brand-50/20 focus:border-brand-500 focus:ring-brand-500 text-sm py-1.5 text-right font-bold text-brand-600">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
                            @php
                                $mainImageSrc = !empty($product->image)
                                    ? asset('storage/' . $product->image)
                                    : '#';
                                $hasImage = !empty($product->image);
                            @endphp
                            <div class="relative group cursor-pointer border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden hover:border-brand-300 transition-colors bg-slate-50/50"
                                 onclick="document.getElementById('image').click()">
                                <input type="file" name="image" id="image" class="hidden" accept="image/*" onchange="previewImage(this)">
                                <div id="imagePlaceholder" class="{{ $hasImage ? 'hidden' : '' }} aspect-square flex flex-col items-center justify-center p-8 text-center">
                                    <div class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 mb-4">
                                        <i class="fas fa-cloud-upload-alt text-xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-600">Click to update</p>
                                </div>
                                <img id="imagePreview" src="{{ $mainImageSrc }}" alt="Preview" class="{{ $hasImage ? '' : 'hidden' }} w-full aspect-square object-cover">
                                <div id="removeImageBtn" class="{{ $hasImage ? 'flex' : 'hidden' }} absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur shadow-sm items-center justify-center text-red-500 hover:bg-white transition-all cursor-pointer"
                                     onclick="event.stopPropagation(); removeImage();">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
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
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $product->is_active ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
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
                                    <p class="text-xs font-bold text-slate-700">{{ $product->created_at->format('d M, Y') }}</p>
                                </div>
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Last Update</p>
                                    <p class="text-xs font-bold text-slate-700">{{ $product->updated_at->diffForHumans() }}</p>
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
                            <a href="{{ company_route('inventory.index') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                                <span class="text-sm font-medium text-slate-600 group-hover:text-brand-600"><i class="fas fa-boxes mr-3 text-slate-400 group-hover:text-brand-500"></i> Stock Levels</span>
                                <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                            <a href="#" class="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                                <span class="text-sm font-medium text-slate-600 group-hover:text-brand-600"><i class="fas fa-history mr-3 text-slate-400 group-hover:text-brand-500"></i> Audit Logs</span>
                                <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
@endsection

@push('scripts')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
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
