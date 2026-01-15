@extends('catvara.layouts.app')

@section('page-title', 'Create Company')

@section('page-actions')
  <a href="{{ route('tenants.index') }}"
    class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 transition-all">
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    Back to List
  </a>
@endsection

@section('content')
  <form action="{{ route('tenants.store') }}" class="ajax-form" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Col: Main Info -->
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
                <input type="text" name="name" value="{{ old('name') }}"
                  class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all @error('name') border-rose-500 @enderror"
                  placeholder="e.g. London Trade">
                @error('name')
                  <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Legal Name <span
                    class="text-rose-500">*</span></label>
                <input type="text" name="legal_name" value="{{ old('legal_name') }}"
                  class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all @error('legal_name') border-rose-500 @enderror"
                  placeholder="e.g. London Trade Limited">
                @error('legal_name')
                  <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Company Code <span
                    class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}"
                  class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all @error('code') border-rose-500 @enderror"
                  placeholder="e.g. UK-TRADE">
                <p class="text-[10px] text-slate-400">Unique internal code (uppercase recommended).</p>
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
                    <option value="{{ $st->id }}" {{ old('company_status_id') == $st->id ? 'selected' : '' }}>
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
                <input type="text" name="website_url" value="{{ old('website_url') }}"
                  class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all @error('website_url') border-rose-500 @enderror"
                  placeholder="https://example.com">
              </div>
              @error('website_url')
                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        <!-- Document Settings Card -->
        <div class="card overflow-hidden">
          <div class="p-6 border-b border-slate-50 flex items-center">
            <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 mr-3">
              <i data-lucide="file-text" class="w-4 h-4"></i>
            </div>
            <h3 class="font-bold text-slate-800">Document Settings</h3>
          </div>
          <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Invoice Prefix</label>
              <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix') }}"
                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all"
                placeholder="e.g. INV">
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Invoice Postfix</label>
              <input type="text" name="invoice_postfix" value="{{ old('invoice_postfix') }}"
                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all"
                placeholder="e.g. 2025">
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Quote Prefix</label>
              <input type="text" name="quote_prefix" value="{{ old('quote_prefix') }}"
                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all"
                placeholder="e.g. QT">
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Quote Postfix</label>
              <input type="text" name="quote_postfix" value="{{ old('quote_postfix') }}"
                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all"
                placeholder="e.g. 2025">
            </div>
          </div>
        </div>

        <!-- Address & Tax Card -->
        <div class="card overflow-hidden">
          <div class="p-6 border-b border-slate-50 flex items-center">
            <div class="w-8 h-8 bg-rose-50 rounded-lg flex items-center justify-center text-rose-600 mr-3">
              <i data-lucide="map-pin" class="w-4 h-4"></i>
            </div>
            <h3 class="font-bold text-slate-800">Address & Tax</h3>
          </div>
          <div class="p-6 space-y-6">
            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Full Address</label>
              <textarea name="address" rows="3"
                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all"
                placeholder="Company physical address">{{ old('address') }}</textarea>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Tax Number / TRN</label>
              <input type="text" name="tax_number" value="{{ old('tax_number') }}"
                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-accent focus:border-accent transition-all"
                placeholder="VAT / TRN / GST">
            </div>
          </div>
        </div>
      </div>

      <!-- Right Col: Logo & Actions -->
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
              <div class="relative w-32 h-32 mb-4">
                <img id="logoPreview"
                  src="https://ui-avatars.com/api/?name=Company&background=f8fafc&color=cbd5e1&size=128"
                  class="w-full h-full object-contain rounded-2xl border-2 border-dashed border-slate-200 p-2"
                  alt="Logo Preview">
                <label for="logoInput"
                  class="absolute bottom-0 right-0 p-2 bg-white rounded-xl shadow-lg border border-slate-100 cursor-pointer hover:bg-slate-50 transition-all text-accent">
                  <i data-lucide="camera" class="w-4 h-4"></i>
                  <input type="file" name="logo" id="logoInput" class="hidden" accept="image/*">
                </label>
              </div>
              <p class="text-[10px] text-slate-400 uppercase font-bold tracking-tight">Logo Preview</p>
              <p class="text-[10px] text-slate-400 mt-1">Recommended size: 512x512</p>
            </div>
          </div>
        </div>

        <!-- Actions Card -->
        <div class="card p-6">
          <div class="space-y-3">
            <button type="submit"
              class="w-full flex justify-center items-center px-6 py-3 bg-accent text-white rounded-xl text-sm font-bold shadow-lg shadow-accent/20 hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all">
              <i data-lucide="save" class="w-4 h-4 mr-2"></i>
              Save Company
            </button>
            <a href="{{ route('tenants.index') }}"
              class="w-full flex justify-center items-center px-6 py-3 border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all">
              Cancel
            </a>
          </div>
        </div>

        <!-- Tips / Info Card -->
        <div class="bg-primary/5 rounded-2xl p-6 border border-primary/10">
          <h4 class="text-xs font-bold text-primary uppercase mb-3 flex items-center">
            <i data-lucide="info" class="w-4 h-4 mr-2"></i>
            Quick Help
          </h4>
          <ul class="space-y-3">
            <li class="text-xs text-slate-600 flex items-start">
              <i data-lucide="check-circle" class="w-3 h-3 mr-2 mt-0.5 text-accent"></i>
              Ensure company code is unique.
            </li>
            <li class="text-xs text-slate-600 flex items-start">
              <i data-lucide="check-circle" class="w-3 h-3 mr-2 mt-0.5 text-accent"></i>
              Set correct document prefixes.
            </li>
          </ul>
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
