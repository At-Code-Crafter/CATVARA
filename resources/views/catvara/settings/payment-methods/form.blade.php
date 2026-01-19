@extends('catvara.layouts.app')

@section('title', isset($paymentMethod) ? 'Edit Payment Method' : 'Create Payment Method')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ company_route('settings.payment-methods.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Settings</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($paymentMethod) ? 'Edit Payment Method' : 'Create Payment Method' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Configure payment option for transactions</p>
      </div>
    </div>

    <form
      action="{{ isset($paymentMethod) ? company_route('settings.payment-methods.update', ['payment_method' => $paymentMethod->id]) : company_route('settings.payment-methods.store') }}"
      method="POST" class="space-y-8">
      @csrf
      @if (isset($paymentMethod))
        @method('PUT')
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Form Fields --}}
        <div class="lg:col-span-2 space-y-8">
          {{-- Basic Details --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-credit-card"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Method Details</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Basic Information</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Method Code
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-fingerprint"></i>
                  <input type="text" name="code" value="{{ old('code', $paymentMethod->code ?? '') }}" required
                    class="w-full py-2.5 font-semibold uppercase" placeholder="e.g. CASH">
                </div>
                @error('code')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Method Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-tag"></i>
                  <input type="text" name="name" value="{{ old('name', $paymentMethod->name ?? '') }}" required
                    class="w-full py-2.5 font-semibold" placeholder="e.g. Cash Payment">
                </div>
                @error('name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Payment Type
                  <span class="text-rose-500">*</span></label>
                <select name="type" class="w-full pt-1" required>
                  <option value="">Select Type</option>
                  <option value="CASH" {{ old('type', $paymentMethod->type ?? '') == 'CASH' ? 'selected' : '' }}>Cash
                  </option>
                  <option value="CARD" {{ old('type', $paymentMethod->type ?? '') == 'CARD' ? 'selected' : '' }}>Card
                  </option>
                  <option value="GATEWAY" {{ old('type', $paymentMethod->type ?? '') == 'GATEWAY' ? 'selected' : '' }}>
                    Payment Gateway</option>
                  <option value="BANK" {{ old('type', $paymentMethod->type ?? '') == 'BANK' ? 'selected' : '' }}>Bank
                    Transfer</option>
                  <option value="WALLET" {{ old('type', $paymentMethod->type ?? '') == 'WALLET' ? 'selected' : '' }}>
                    Digital Wallet</option>
                  <option value="CREDIT" {{ old('type', $paymentMethod->type ?? '') == 'CREDIT' ? 'selected' : '' }}>
                    Credit/Store Credit</option>
                </select>
                @error('type')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Features --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-cogs"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Features & Settings</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Configuration Options</p>
              </div>
            </div>

            <div class="space-y-4">
              <label
                class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border border-slate-100 hover:bg-slate-50">
                <input type="checkbox" name="is_active" value="1"
                  {{ old('is_active', $paymentMethod->is_active ?? true) ? 'checked' : '' }}
                  class="h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-blue-500 checked:border-blue-500 relative">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-700">Active Method</span>
                  <span class="text-[10px] text-slate-400 font-medium">Enable this method for transactions</span>
                </div>
              </label>

              <label
                class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border border-slate-100 hover:bg-slate-50">
                <input type="checkbox" name="allow_refund" value="1"
                  {{ old('allow_refund', $paymentMethod->allow_refund ?? true) ? 'checked' : '' }}
                  class="h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-green-500 checked:border-green-500 relative">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-700">Allow Refunds</span>
                  <span class="text-[10px] text-slate-400 font-medium">Enable refund processing for this method</span>
                </div>
              </label>

              <label
                class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border border-slate-100 hover:bg-slate-50">
                <input type="checkbox" name="requires_reference" value="1"
                  {{ old('requires_reference', $paymentMethod->requires_reference ?? false) ? 'checked' : '' }}
                  class="h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-purple-500 checked:border-purple-500 relative">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-700">Requires Reference</span>
                  <span class="text-[10px] text-slate-400 font-medium">Mandate reference number for payments</span>
                </div>
              </label>
            </div>
          </div>
        </div>

        {{-- Right Column: Actions --}}
        <div class="space-y-8">
          {{-- Save Card --}}
          <div class="card p-8 bg-slate-900 border-none shadow-2xl shadow-slate-900/40">
            <h3 class="text-white font-black text-lg mb-6 flex items-center gap-2">
              <i class="fas fa-save text-brand-400"></i> Save Method
            </h3>

            <div class="space-y-6">
              <button type="submit"
                class="w-full btn btn-primary py-4 shadow-xl shadow-brand-500/20 font-black tracking-tight flex items-center justify-center gap-3">
                <i class="fas fa-check opacity-50"></i>
                {{ isset($paymentMethod) ? 'Update Method' : 'Create Method' }}
              </button>

              <a href="{{ company_route('settings.payment-methods.index') }}"
                class="w-full flex items-center justify-center py-2 text-[10px] font-black text-slate-500 hover:text-rose-400 transition-colors uppercase tracking-widest">
                Cancel
              </a>
            </div>
          </div>

          @if (isset($paymentMethod))
            {{-- Info Card --}}
            <div class="card p-6 bg-white border-slate-100 shadow-soft">
              <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-blue-400"></i> Method Info
              </h3>
              <div class="space-y-3 text-xs">
                <div class="flex justify-between">
                  <span class="text-slate-400 font-bold">Created</span>
                  <span class="text-slate-600 font-bold">{{ $paymentMethod->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400 font-bold">Last Updated</span>
                  <span class="text-slate-600 font-bold">{{ $paymentMethod->updated_at->format('M d, Y') }}</span>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Add checkmark icon to checkboxes when checked
      $('input[type="checkbox"]').on('change', function() {
        $(this).next('span.checkmark').remove();
        if ($(this).is(':checked')) {
          $(this).after(
            '<span class="checkmark absolute text-white top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none"><i class="fas fa-check text-[10px]"></i></span>'
          );
        }
      }).trigger('change');
    });
  </script>
@endpush
