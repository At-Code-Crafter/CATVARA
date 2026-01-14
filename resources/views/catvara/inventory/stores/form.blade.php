@extends('catvara.layouts.app')

@section('title', isset($store) ? 'Edit Store' : 'Add Store')

@section('content')
    <div class="max-w-4xl mx-auto">
        <form
            action="{{ isset($store) ? company_route('inventory.stores.update', ['store' => $store->id]) : company_route('inventory.stores.store') }}"
            method="POST" id="storeForm">
            @csrf
            @if (isset($store))
                @method('PUT')
            @endif

            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ company_route('inventory.stores.index') }}"
                        class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-brand-600 transition-colors mb-2">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Stores
                    </a>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                        {{ isset($store) ? 'Edit ' . $store->name : 'Create New Store' }}
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all">
                        {{ isset($store) ? 'Update Store' : 'Save Store' }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8">
                {{-- Basic Settings Box --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="font-bold text-slate-800">Basic Information</h3>
                        <p class="text-xs text-slate-500 mt-1">Primary details for this retail location.</p>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Store Name
                                    <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $store->name ?? '') }}"
                                    class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                    placeholder="e.g. Downtown Outlet" required>
                                @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="code" class="block text-sm font-semibold text-slate-700 mb-2">Store Code
                                    <span class="text-red-500">*</span></label>
                                <input type="text" name="code" id="code" value="{{ old('code', $store->code ?? '') }}"
                                    class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                    placeholder="e.g. ST-001" required>
                                @error('code') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact & Location Box --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="font-bold text-slate-800">Contact & Address</h3>
                        <p class="text-xs text-slate-500 mt-1">How customers and staff can reach or find this store.</p>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-slate-700 mb-2">Phone
                                    Number</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $store->phone ?? '') }}"
                                    class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                    placeholder="e.g. +1 (555) 000-0000">
                                @error('phone') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="address" class="block text-sm font-semibold text-slate-700 mb-2">Physical
                                    Address</label>
                                <textarea name="address" id="address" rows="4"
                                    class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                    placeholder="Enter the full street address...">{{ old('address', $store->address ?? '') }}</textarea>
                                @error('address') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Box --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-slate-800">Store Status</h3>
                                <p class="text-xs text-slate-500 mt-1">Inactive stores will be hidden from the storefront
                                    and inventory transactions.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $store->is_active ?? true) ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="mt-8 pt-8 border-t border-slate-100 flex items-center justify-end gap-3 mb-12">
                <a href="{{ company_route('inventory.stores.index') }}"
                    class="px-6 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-8 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all">
                    {{ isset($store) ? 'Update Store' : 'Create Store' }}
                </button>
            </div>
        </form>
    </div>
@endsection