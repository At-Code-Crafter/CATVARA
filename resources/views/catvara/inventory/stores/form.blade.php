@extends('catvara.layouts.app')

@section('title', isset($store) ? 'Edit Store' : 'Add Store')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ company_route('inventory.stores.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Store Management
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($store) ? 'Edit Store' : 'Create New Store' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Configure retail location details and settings.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ company_route('inventory.stores.index') }}" class="btn btn-white shadow-soft">
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
        <div class="lg:col-span-2 space-y-8">

          {{-- Basic Information Card --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-brand-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-store"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Basic Information</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Store Details</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Store Name --}}
              <div class="md:col-span-2 space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Store Name <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-store"></i>
                  <input type="text" name="name" value="{{ old('name', $store->name ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="e.g. Downtown Outlet" required>
                </div>
                @error('name')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Store Code --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Store Code <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-hashtag"></i>
                  <input type="text" name="code" value="{{ old('code', $store->code ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal font-mono uppercase"
                    placeholder="e.g. STORE-001" required>
                </div>
                @error('code')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Phone --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Phone Number
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-phone"></i>
                  <input type="text" name="phone" value="{{ old('phone', $store->phone ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="e.g. +44 7000 000000">
                </div>
                @error('phone')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Address Card --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-amber-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Location Address</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Physical Address</p>
              </div>
            </div>

            <div class="space-y-1.5">
              <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                Street Address
              </label>
              <textarea name="address" rows="3"
                class="w-full rounded-xl border-slate-200 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 shadow-sm placeholder-slate-400 text-sm py-3 px-4 font-medium transition-all resize-none"
                placeholder="Enter the full street address...">{{ old('address', $store->address ?? '') }}</textarea>
              @error('address')
                <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        {{-- Right Column: Status & Info --}}
        <div class="space-y-8">

          {{-- Status Card --}}
          <div class="card p-6 bg-white border-slate-100 shadow-soft">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4">
              Status & Visibility
            </h3>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-bold text-slate-700 text-sm">Store Active</p>
                <p class="text-xs text-slate-400">Enable/disable this location</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                  {{ old('is_active', $store->is_active ?? true) ? 'checked' : '' }}>
                <div
                  class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                </div>
              </label>
            </div>
          </div>

          {{-- Info Card --}}
          <div class="bg-sky-50/50 rounded-2xl p-6 border border-sky-100">
            <h4 class="text-[11px] font-black text-sky-600 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-info-circle"></i> Store Management
            </h4>
            <p class="text-xs text-sky-700 leading-relaxed font-medium">
              Stores are physical retail locations where transactions occur.
              Inventory can be tracked specifically for each store, and sales
              can be recorded at store level for reporting.
            </p>
          </div>

          {{-- Quick Actions (for edit mode) --}}
          @if(isset($store))
            <div class="card p-6 bg-white border-slate-100 shadow-soft">
              <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-50 pb-4">
                Quick Actions
              </h3>
              <div class="space-y-3">
                <a href="{{ company_route('inventory.inventory.index') }}?location={{ $store->inventoryLocation?->id }}"
                   class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors group">
                  <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-boxes text-sm"></i>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700 group-hover:text-brand-600">View Stock</p>
                    <p class="text-xs text-slate-400">Check inventory at this store</p>
                  </div>
                </a>
              </div>
            </div>
          @endif
        </div>
      </div>
    </form>
  </div>
@endsection
