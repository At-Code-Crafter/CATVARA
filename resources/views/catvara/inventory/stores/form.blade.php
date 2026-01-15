@extends('catvara.layouts.app')

@section('title', isset($store) ? 'Edit Store' : 'Add Store')

@section('content')
  <div class="max-w-5xl mx-auto">
    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
      <div>
        <nav class="flex mb-1" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
            <li class="inline-flex items-center">
              <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-brand-600 transition-colors">
                <i class="fas fa-home mr-2"></i> Dashboard
              </a>
            </li>
            <li><i class="fas fa-chevron-right text-slate-300 text-xs"></i></li>
            <li>
              <a href="#" class="text-slate-500 hover:text-brand-600 transition-colors">Inventory</a>
            </li>
            <li><i class="fas fa-chevron-right text-slate-300 text-xs"></i></li>
            <li>
              <a href="{{ company_route('inventory.stores.index') }}"
                class="text-slate-500 hover:text-brand-600 transition-colors">Stores</a>
            </li>
            <li><i class="fas fa-chevron-right text-slate-300 text-xs"></i></li>
            <li aria-current="page">
              <span class="font-medium text-slate-400">{{ isset($store) ? 'Edit' : 'Create' }}</span>
            </li>
          </ol>
        </nav>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
          {{ isset($store) ? 'Edit Store: ' . $store->name : 'Create New Store' }}
        </h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Configure retail location details and settings.</p>
      </div>

      {{-- Top Actions --}}
      <div class="flex items-center gap-3">
        <a href="{{ company_route('inventory.stores.index') }}" class="btn btn-secondary">
          Cancel
        </a>
        <button type="submit" form="storeForm" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-save mr-2"></i> {{ isset($store) ? 'Update Store' : 'Save Store' }}
        </button>
      </div>
    </div>

    <form
      action="{{ isset($store) ? company_route('inventory.stores.update', ['store' => $store->id]) : company_route('inventory.stores.store') }}"
      method="POST" id="storeForm">
      @csrf
      @if (isset($store))
        @method('PUT')
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Main Details --}}
        <div class="lg:col-span-2 space-y-6">
          {{-- Basic Information Card --}}
          <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
              <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-store text-brand-400"></i> Basic Information
              </h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <label for="name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Store
                  Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $store->name ?? '') }}"
                  class="w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 font-semibold transition-all"
                  placeholder="e.g. Downtown Outlet" required>
                @error('name')
                  <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label for="code" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Store
                  Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" id="code" value="{{ old('code', $store->code ?? '') }}"
                  class="w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 font-semibold transition-all"
                  placeholder="e.g. ST-001" required>
                @error('code')
                  <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label for="phone" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Phone
                  Number</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $store->phone ?? '') }}"
                  class="w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 font-semibold transition-all"
                  placeholder="e.g. +1 (555) 000-0000">
                @error('phone')
                  <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Address Card --}}
          <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-brand-400"></i> Location Address
              </h3>
            </div>
            <div class="p-6">
              <label for="address" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Physical
                Address</label>
              <textarea name="address" id="address" rows="4"
                class="w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 font-medium transition-all"
                placeholder="Enter the full street address...">{{ old('address', $store->address ?? '') }}</textarea>
              @error('address')
                <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        {{-- Right Column: Status & Metadata --}}
        <div class="lg:col-span-1 space-y-6">
          {{-- Status Card --}}
          <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 class="font-bold text-slate-800">Status & Visibility</h3>
            </div>
            <div class="p-6">
              <div class="flex items-center justify-between">
                <span class="flex flex-col">
                  <span class="font-bold text-slate-700 text-sm">Store Active</span>
                  <span class="text-xs text-slate-400">Enable/disable this location</span>
                </span>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                    {{ old('is_active', $store->is_active ?? true) ? 'checked' : '' }}>
                  <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500">
                  </div>
                </label>
              </div>
            </div>
          </div>

          {{-- Info Card --}}
          <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6">
            <div class="flex items-start gap-3">
              <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
              <div>
                <h4 class="font-bold text-blue-800 text-sm">Store Management</h4>
                <p class="text-xs text-blue-600 mt-1 leading-relaxed">
                  Stores are physical retail locations where transactions occur.
                  Inventory can be tracked specifically for each store.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Mobile Sticky Action Bar --}}
      <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-slate-200 sm:hidden z-50">
        <button type="submit" class="btn btn-primary w-full shadow-lg shadow-brand-500/30">
          {{ isset($store) ? 'Update Store' : 'Save Store' }}
        </button>
      </div>
    </form>
  </div>
@endsection
