@extends('catvara.layouts.app')

@section('page-title', 'Edit Company')

@section('page-actions')
  <a href="{{ route('tenants.index') }}"
    class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 transition-all">
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    Back to List
  </a>
@endsection

@section('content')
  <form action="{{ route('tenants.update', $company->id) }}" class="ajax-form" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Col: Main Info & Financials -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Basic Info Card -->
        <div class="card overflow-hidden">
          <div class="p-6 border-b border-slate-50 flex items-center">
            <div class="w-8 h-8 bg-accent/10 rounded-lg flex items-center justify-center text-accent mr-3">
              <i data-lucide="building-2" class="w-4 h-4"></i>
            </div>
            <h3 class="font-bold text-slate-800">Company Information</h3>
          </div>
          <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Company Name <span
                    class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $company->name) }}"
                  class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all @error('name') border-rose-500 @enderror">
                @error('name')
                  <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Legal Name <span
                    class="text-rose-500">*</span></label>
                <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}"
                  class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all @error('legal_name') border-rose-500 @enderror">
                @error('legal_name')
                  <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Company Code <span
                    class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $company->code) }}"
                  class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all @error('code') border-rose-500 @enderror">
                @error('code')
                  <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Status <span
                    class="text-rose-500">*</span></label>
                <select name="company_status_id" class="select2 @error('company_status_id') border-rose-500 @enderror">
                  <option value="">-- Select Status --</option>
                  @foreach ($statuses as $st)
                    <option value="{{ $st->id }}"
                      {{ old('company_status_id', $company->company_status_id) == $st->id ? 'selected' : '' }}>
                      {{ $st->name }}</option>
                  @endforeach
                </select>
                @error('company_status_id')
                  <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Website URL</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                  <i data-lucide="globe" class="w-4 h-4"></i>
                </div>
                <input type="text" name="website_url" value="{{ old('website_url', $company->website_url) }}"
                  class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all"
                  placeholder="https://example.com">
              </div>
            </div>
          </div>
        </div>

        <!-- Financial & Document Settings -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Document Settings Card -->
          <div class="card overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex items-center">
              <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 mr-3">
                <i data-lucide="file-text" class="w-4 h-4"></i>
              </div>
              <h3 class="font-bold text-slate-800">Documents</h3>
            </div>
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-500 uppercase">Invoice Prefix</label>
                  <input type="text" name="invoice_prefix"
                    value="{{ old('invoice_prefix', $company->detail?->invoice_prefix) }}"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-accent"
                    placeholder="INV">
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-500 uppercase">Postfix</label>
                  <input type="text" name="invoice_postfix"
                    value="{{ old('invoice_postfix', $company->detail?->invoice_postfix) }}"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-accent"
                    placeholder="2025">
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-500 uppercase">Quote Prefix</label>
                  <input type="text" name="quote_prefix"
                    value="{{ old('quote_prefix', $company->detail?->quote_prefix) }}"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-accent"
                    placeholder="QT">
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-500 uppercase">Postfix</label>
                  <input type="text" name="quote_postfix"
                    value="{{ old('quote_postfix', $company->detail?->quote_postfix) }}"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-accent"
                    placeholder="2025">
                </div>
              </div>
            </div>
          </div>

          <!-- Financial Settings Card -->
          <div class="card overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex items-center">
              <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600 mr-3">
                <i data-lucide="banknote" class="w-4 h-4"></i>
              </div>
              <h3 class="font-bold text-slate-800">Financials</h3>
            </div>
            <div class="p-6 space-y-4">
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-500 uppercase">Base Currency <span
                    class="text-rose-500">*</span></label>
                @if ($company->base_currency_id && $company->baseCurrency)
                  <div
                    class="flex items-center px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                    <i data-lucide="lock" class="w-3 h-3 mr-2"></i>
                    {{ $company->baseCurrency->code }} - {{ $company->baseCurrency->name }}
                  </div>
                  <p class="text-[10px] text-slate-400">Locked once set.</p>
                @else
                  <select name="base_currency_id" class="select2">
                    <option value="">-- Select Currency --</option>
                    @foreach ($currencies as $currency)
                      <option value="{{ $currency->id }}"
                        {{ old('base_currency_id', $company->base_currency_id) == $currency->id ? 'selected' : '' }}>
                        {{ $currency->code }} - {{ $currency->name }}</option>
                    @endforeach
                  </select>
                @endif
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-500 uppercase">Tax Number</label>
                <input type="text" name="tax_number" value="{{ old('tax_number', $company->detail?->tax_number) }}"
                  class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent"
                  placeholder="TRN / VAT">
              </div>
            </div>
          </div>
        </div>

        <!-- Address Card -->
        <div class="card overflow-hidden">
          <div class="p-6 border-b border-slate-50 flex items-center">
            <div class="w-8 h-8 bg-rose-50 rounded-lg flex items-center justify-center text-rose-600 mr-3">
              <i data-lucide="map-pin" class="w-4 h-4"></i>
            </div>
            <h3 class="font-bold text-slate-800">Address</h3>
          </div>
          <div class="p-6">
            <textarea name="address" rows="3"
              class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent transition-all"
              placeholder="Full address">{{ old('address', $company->detail?->address) }}</textarea>
          </div>
        </div>

        <!-- Payment Terms Card -->
        <div class="card overflow-hidden">
          <div class="p-6 border-b border-slate-50 flex items-center">
            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600 mr-3">
              <i data-lucide="clock" class="w-4 h-4"></i>
            </div>
            <h3 class="font-bold text-slate-800">Enabled Payment Terms</h3>
          </div>
          <div class="p-6 grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach ($paymentTerms as $term)
              <label
                class="relative flex items-center p-3 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer transition-all group">
                <input type="checkbox" name="payment_terms[]" value="{{ $term->id }}"
                  class="w-4 h-4 rounded text-accent focus:ring-accent border-slate-300"
                  {{ (is_array(old('payment_terms')) && in_array($term->id, old('payment_terms'))) || $company->paymentTerms->contains($term->id) ? 'checked' : '' }}>
                <div class="ml-3">
                  <span class="block text-xs font-bold text-slate-700">{{ $term->name }}</span>
                  <span class="block text-[10px] text-slate-400 capitalize">{{ $term->code }}</span>
                </div>
              </label>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Right Col: Identity & Actions -->
      <div class="space-y-8">
        <!-- Logo Card -->
        <div class="card overflow-hidden">
          <div class="p-6 border-b border-slate-50 flex items-center">
            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600 mr-3">
              <i data-lucide="image" class="w-4 h-4"></i>
            </div>
            <h3 class="font-bold text-slate-800">Identity</h3>
          </div>
          <div class="p-6">
            <div class="flex flex-col items-center justify-center">
              @php
                $logoSrc = $company->logo
                    ? asset('storage/' . $company->logo)
                    : 'https://ui-avatars.com/api/?name=' .
                        urlencode($company->name) .
                        '&background=f1f5f9&color=64748b&size=128';
              @endphp
              <div class="relative w-32 h-32 mb-4">
                <img id="logoPreview" src="{{ $logoSrc }}"
                  class="w-full h-full object-contain rounded-2xl border-2 border-dashed border-slate-200 p-2 bg-white"
                  alt="Logo Preview">
                <label for="logoInput"
                  class="absolute bottom-0 right-0 p-2 bg-white rounded-xl shadow-lg border border-slate-100 cursor-pointer hover:bg-slate-50 transition-all text-accent group">
                  <i data-lucide="camera" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                  <input type="file" name="logo" id="logoInput" class="hidden" accept="image/*">
                </label>
              </div>
              <p class="text-[10px] text-slate-400 uppercase font-bold tracking-tight">Company Logo</p>
            </div>
          </div>
        </div>

        <!-- Actions Card -->
        <div class="card p-6">
          <div class="space-y-3">
            <button type="submit"
              class="w-full flex justify-center items-center px-6 py-3 bg-accent text-white rounded-xl text-sm font-bold shadow-lg shadow-accent/20 hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all">
              <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
              Update Company
            </button>
            <a href="{{ route('tenants.index') }}"
              class="w-full flex justify-center items-center px-6 py-3 border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all">
              Cancel
            </a>
          </div>
          <div class="mt-6 pt-6 border-t border-slate-50 text-center">
            <p class="text-[10px] text-slate-400 uppercase tracking-widest">Added On</p>
            <p class="text-xs font-bold text-slate-600 mt-1">{{ $company->created_at->format('d M, Y') }}</p>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Logo preview
      $('#logoInput').on('change', function(e) {
        const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
        if (file) {
          const reader = new FileReader();
          reader.onload = function(ev) {
            $('#logoPreview').attr('src', ev.target.result);
          };
          reader.readAsDataURL(file);
        }
      });
    });
  </script>
@endpush
