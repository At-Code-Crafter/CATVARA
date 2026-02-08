@extends('catvara.layouts.app')

@section('title', isset($companyBank) ? 'Edit Bank Account' : 'Add Bank Account')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ company_route('settings.company-banks.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Settings</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($companyBank) ? 'Edit Bank Account' : 'Add Bank Account' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Configure bank account details for payments</p>
      </div>
    </div>

    <form
      action="{{ isset($companyBank) ? company_route('settings.company-banks.update', ['company_bank' => $companyBank->id]) : company_route('settings.company-banks.store') }}"
      method="POST" class="space-y-8">
      @csrf
      @if (isset($companyBank))
        @method('PUT')
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Form Fields --}}
        <div class="lg:col-span-2 space-y-8">
          {{-- Bank Details --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-university"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Bank Details</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Bank Information</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Bank Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-building"></i>
                  <input type="text" name="bank_name" value="{{ old('bank_name', $companyBank->bank_name ?? '') }}"
                    required class="w-full py-2.5 font-semibold" placeholder="e.g. HSBC Bank">
                </div>
                @error('bank_name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Branch
                  <span class="text-slate-300">(Optional)</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-map-marker-alt"></i>
                  <input type="text" name="branch" value="{{ old('branch', $companyBank->branch ?? '') }}"
                    class="w-full py-2.5 font-semibold" placeholder="e.g. Main Branch">
                </div>
                @error('branch')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Account Details --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-credit-card"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Account Details</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Account Information</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Account Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-user"></i>
                  <input type="text" name="account_name"
                    value="{{ old('account_name', $companyBank->account_name ?? '') }}" required
                    class="w-full py-2.5 font-semibold" placeholder="e.g. Company Ltd">
                </div>
                @error('account_name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Account Number
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-hashtag"></i>
                  <input type="text" name="account_number"
                    value="{{ old('account_number', $companyBank->account_number ?? '') }}" required
                    class="w-full py-2.5 font-semibold font-mono" placeholder="e.g. 12345678">
                </div>
                @error('account_number')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">IBAN
                  <span class="text-slate-300">(Optional)</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-globe"></i>
                  <input type="text" name="iban" value="{{ old('iban', $companyBank->iban ?? '') }}"
                    class="w-full py-2.5 font-semibold font-mono uppercase" placeholder="e.g. GB29NWBK60161331926819">
                </div>
                @error('iban')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">SWIFT/BIC Code
                  <span class="text-slate-300">(Optional)</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-exchange-alt"></i>
                  <input type="text" name="swift_code" value="{{ old('swift_code', $companyBank->swift_code ?? '') }}"
                    class="w-full py-2.5 font-semibold font-mono uppercase" placeholder="e.g. NWBKGB2L">
                </div>
                @error('swift_code')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Currency
                  <span class="text-rose-500">*</span></label>
                <select name="currency_id" class="w-full pt-1" required>
                  <option value="">Select Currency</option>
                  @foreach ($currencies as $currency)
                    <option value="{{ $currency->id }}"
                      {{ old('currency_id', $companyBank->currency_id ?? '') == $currency->id ? 'selected' : '' }}>
                      {{ $currency->code }} - {{ $currency->name }}
                    </option>
                  @endforeach
                </select>
                @error('currency_id')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Status --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-purple-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-cogs"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Status</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Account Status</p>
              </div>
            </div>

            <div class="space-y-4">
              <label
                class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border border-slate-100 hover:bg-slate-50">
                <input type="checkbox" name="is_active" value="1"
                  {{ old('is_active', $companyBank->is_active ?? true) ? 'checked' : '' }}
                  class="h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-blue-500 checked:border-blue-500 relative">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-700">Active Account</span>
                  <span class="text-[10px] text-slate-400 font-medium">Enable this bank account for use</span>
                </div>
              </label>
            </div>
          </div>
        </div>

        {{-- Right Column: Summary Card --}}
        <div class="lg:col-span-1">
          <div class="card p-6 bg-white border-slate-100 shadow-soft sticky top-24">
            <div class="flex items-center gap-3 mb-6">
              <div class="h-8 w-8 rounded-lg bg-brand-50 text-brand-500 flex items-center justify-center text-sm">
                <i class="fas fa-info-circle"></i>
              </div>
              <h4 class="font-bold text-slate-800">Quick Tips</h4>
            </div>

            <div class="space-y-4 text-xs text-slate-500">
              <div class="flex items-start gap-3">
                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                <p>Ensure account details are accurate for payment processing</p>
              </div>
              <div class="flex items-start gap-3">
                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                <p>IBAN and SWIFT codes are required for international transfers</p>
              </div>
              <div class="flex items-start gap-3">
                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                <p>Bank details will appear on invoices for customer payments</p>
              </div>
            </div>

            <hr class="my-6 border-slate-100">

            <div class="space-y-3">
              <button type="submit" class="btn btn-primary w-full shadow-lg shadow-brand-500/30">
                <i class="fas fa-save mr-2"></i>
                {{ isset($companyBank) ? 'Update Account' : 'Save Account' }}
              </button>
              <a href="{{ company_route('settings.company-banks.index') }}"
                class="btn btn-secondary w-full text-center">
                <i class="fas fa-times mr-2"></i> Cancel
              </a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection
