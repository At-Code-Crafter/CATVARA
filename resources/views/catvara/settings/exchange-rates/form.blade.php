@extends('catvara.layouts.app')

@section('title', isset($exchangeRate) ? 'Edit Exchange Rate' : 'Add Exchange Rate')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ company_route('settings.exchange-rates.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Exchange Rates
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($exchangeRate) ? 'Edit Exchange Rate' : 'Add Exchange Rate' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Configure currency conversion rate.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ company_route('settings.exchange-rates.index') }}" class="btn btn-white shadow-soft">
          Cancel
        </a>
        <button type="submit" form="rateForm" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-save mr-2"></i> {{ isset($exchangeRate) ? 'Update Rate' : 'Save Rate' }}
        </button>
      </div>
    </div>

    <form id="rateForm"
      action="{{ isset($exchangeRate) ? company_route('settings.exchange-rates.update', ['exchange_rate' => $exchangeRate->id]) : company_route('settings.exchange-rates.store') }}"
      method="POST">
      @csrf
      @if (isset($exchangeRate))
        @method('PUT')
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Main Form --}}
        <div class="lg:col-span-2 space-y-8">

          {{-- Currency Pair Card --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-exchange-alt"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Currency Pair</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">From → To</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Base Currency --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Base Currency <span class="text-rose-500">*</span>
                </label>
                <select name="base_currency_id" required
                  class="w-full rounded-xl border-slate-200 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 shadow-sm text-sm py-2.5 px-4 font-semibold">
                  <option value="">Select base currency</option>
                  @foreach($currencies as $currency)
                    <option value="{{ $currency->id }}"
                      {{ old('base_currency_id', $exchangeRate->base_currency_id ?? $companyCurrency?->id) == $currency->id ? 'selected' : '' }}>
                      {{ $currency->code }} - {{ $currency->name }}
                    </option>
                  @endforeach
                </select>
                @error('base_currency_id')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Target Currency --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Target Currency <span class="text-rose-500">*</span>
                </label>
                <select name="target_currency_id" required
                  class="w-full rounded-xl border-slate-200 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 shadow-sm text-sm py-2.5 px-4 font-semibold">
                  <option value="">Select target currency</option>
                  @foreach($currencies as $currency)
                    <option value="{{ $currency->id }}"
                      {{ old('target_currency_id', $exchangeRate->target_currency_id ?? '') == $currency->id ? 'selected' : '' }}>
                      {{ $currency->code }} - {{ $currency->name }}
                    </option>
                  @endforeach
                </select>
                @error('target_currency_id')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Rate Details Card --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-brand-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-calculator"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Rate Details</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Conversion Information</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              {{-- Rate --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Exchange Rate <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-percent"></i>
                  <input type="number" name="rate" step="0.00000001" min="0.00000001"
                    value="{{ old('rate', $exchangeRate->rate ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="1.000000" required>
                </div>
                @error('rate')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Effective Date --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Effective Date
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-calendar"></i>
                  <input type="date" name="effective_date"
                    value="{{ old('effective_date', isset($exchangeRate) ? $exchangeRate->effective_date?->format('Y-m-d') : date('Y-m-d')) }}"
                    class="w-full py-2.5 font-semibold">
                </div>
                @error('effective_date')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Source --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Source
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-tag"></i>
                  <input type="text" name="source"
                    value="{{ old('source', $exchangeRate->source ?? 'manual') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="e.g. manual, api">
                </div>
                @error('source')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>
        </div>

        {{-- Right Column: Info --}}
        <div class="space-y-8">

          {{-- Company Currency Info --}}
          @if($companyCurrency)
          <div class="card p-6 bg-white border-slate-100 shadow-soft">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-50 pb-4">
              Company Currency
            </h3>
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600 font-bold text-lg">
                {{ $companyCurrency->symbol }}
              </div>
              <div>
                <p class="font-bold text-slate-800">{{ $companyCurrency->code }}</p>
                <p class="text-sm text-slate-500">{{ $companyCurrency->name }}</p>
              </div>
            </div>
          </div>
          @endif

          {{-- Info Card --}}
          <div class="bg-emerald-50/50 rounded-2xl p-6 border border-emerald-100">
            <h4 class="text-[11px] font-black text-emerald-600 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-info-circle"></i> Exchange Rates
            </h4>
            <p class="text-xs text-emerald-700 leading-relaxed font-medium mb-3">
              Exchange rates are used to convert amounts between currencies in invoices, payments, and reports.
            </p>
            <p class="text-xs text-emerald-600 leading-relaxed">
              <strong>Example:</strong> If 1 USD = 0.79 GBP, enter 0.79 as the rate with USD as base and GBP as target.
            </p>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection
