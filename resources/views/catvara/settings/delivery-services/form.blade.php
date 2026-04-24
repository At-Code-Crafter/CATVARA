@extends('catvara.layouts.app')

@section('title', isset($deliveryService) ? 'Edit Delivery Service' : 'Create Delivery Service')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ company_route('settings.delivery-services.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Settings</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($deliveryService) ? 'Edit Delivery Service' : 'Create Delivery Service' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Courier / shipping partner used on delivery notes</p>
      </div>
    </div>

    @if (session('error'))
      <div class="p-4 mb-6 bg-rose-50 border border-rose-100 rounded-xl text-rose-700 text-sm font-bold">
        {{ session('error') }}
      </div>
    @endif

    <form
      action="{{ isset($deliveryService) ? company_route('settings.delivery-services.update', ['delivery_service' => $deliveryService->id]) : company_route('settings.delivery-services.store') }}"
      method="POST" class="space-y-8">
      @csrf
      @if (isset($deliveryService))
        @method('PUT')
      @endif

      <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-brand-400"></div>
        <div class="flex items-center gap-4 mb-8">
          <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center shadow-sm">
            <i class="fas fa-truck"></i>
          </div>
          <div>
            <h3 class="text-lg font-black text-slate-800 tracking-tight">Service Details</h3>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Courier Information</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-1.5">
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Name
              <span class="text-rose-500">*</span></label>
            <div class="input-icon-group">
              <i class="fas fa-tag"></i>
              <input type="text" name="name" value="{{ old('name', $deliveryService->name ?? '') }}" required
                class="w-full py-2.5 font-semibold" placeholder="e.g. FedEx">
            </div>
            @error('name')
              <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="space-y-1.5">
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Code</label>
            <div class="input-icon-group">
              <i class="fas fa-fingerprint"></i>
              <input type="text" name="code" value="{{ old('code', $deliveryService->code ?? '') }}"
                class="w-full py-2.5 font-semibold uppercase" placeholder="e.g. FEDEX">
            </div>
            @error('code')
              <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="space-y-1.5 md:col-span-2">
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tracking URL Template</label>
            <div class="input-icon-group">
              <i class="fas fa-link"></i>
              <input type="text" name="tracking_url_template"
                value="{{ old('tracking_url_template', $deliveryService->tracking_url_template ?? '') }}"
                class="w-full py-2.5 font-semibold"
                placeholder="https://example.com/track?number={tracking}">
            </div>
            <p class="text-[10px] text-slate-400 ml-1">Use <code>{tracking}</code> as placeholder for the tracking number.</p>
            @error('tracking_url_template')
              <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="space-y-1.5">
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sort Order</label>
            <div class="input-icon-group">
              <i class="fas fa-sort-numeric-down"></i>
              <input type="number" name="sort_order" min="0"
                value="{{ old('sort_order', $deliveryService->sort_order ?? 0) }}"
                class="w-full py-2.5 font-semibold">
            </div>
          </div>

          <div class="space-y-1.5 flex items-end">
            <label class="inline-flex items-center gap-3 cursor-pointer">
              <input type="checkbox" name="is_active" value="1"
                {{ old('is_active', $deliveryService->is_active ?? true) ? 'checked' : '' }}
                class="h-5 w-5 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
              <span class="text-sm font-bold text-slate-700">Active</span>
            </label>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-4">
        <a href="{{ company_route('settings.delivery-services.index') }}"
          class="btn btn-ghost text-slate-500">Cancel</a>
        <button type="submit" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-save mr-2"></i>
          {{ isset($deliveryService) ? 'Update Service' : 'Create Service' }}
        </button>
      </div>
    </form>
  </div>
@endsection
