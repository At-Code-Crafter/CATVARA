@extends('catvara.layouts.app')

@section('title', 'Create New Tenant')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('tenants.index') }}" class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Administration</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Onboard New Tenant</h1>
        <p class="text-slate-500 font-medium mt-1">Register a new company into the system ecosystem.</p>
      </div>
    </div>

    <form action="{{ route('tenants.store') }}" class="ajax-form space-y-8" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Forms --}}
        <div class="lg:col-span-2 space-y-8">
          {{-- Section 1: Core Identity --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-brand-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-building"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Core Identity</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Base Subscription Details</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Company Name --}}
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Company Display Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-signature text-xs"></i>
                  <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. Acme Corporation">
                </div>
                @error('name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Legal Name --}}
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Legal Registered Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-landmark text-xs"></i>
                  <input type="text" name="legal_name" value="{{ old('legal_name') }}" required
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. Acme Corp LLC">
                </div>
                @error('legal_name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- System Code --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Internal Code <span
                    class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-fingerprint"></i>
                  <input type="text" name="code" value="{{ old('code') }}" required
                    class="w-full py-2.5 font-semibold placeholder:font-normal uppercase" placeholder="e.g. ACME-01">
                </div>
                @error('code')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Status --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Lifecycle Status <span
                    class="text-rose-500">*</span></label>
                <select name="company_status_id" class="w-full pt-1">
                  <option value="">Select Status</option>
                  @foreach ($statuses as $st)
                    <option value="{{ $st->id }}" {{ old('company_status_id') == $st->id ? 'selected' : '' }}>
                      {{ $st->name }}</option>
                  @endforeach
                </select>
                @error('company_status_id')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Website --}}
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Official
                  Website</label>
                <div class="input-icon-group">
                  <i class="fas fa-globe text-xs"></i>
                  <input type="url" name="website_url" value="{{ old('website_url') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="https://www.acme.com">
                </div>
                @error('website_url')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Section 2: Document Sequencing --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-file-invoice"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Document Sequencing</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Prefixes & Numbers</p>
              </div>
            </div>

            <div class="space-y-6">
              @php
                $docTypes = [
                    'invoice' => 'Invoice',
                    'quote' => 'Quote',
                    'order' => 'Sales Order',
                    'credit_note' => 'Credit Note',
                    'payment' => 'Payment',
                    'customer' => 'Customer',
                ];
              @endphp

              @foreach ($docTypes as $key => $label)
                <div class="group">
                  <label
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">{{ $label }}
                    Configuration</label>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                      <div class="input-icon-group">
                        <i class="fas fa-indent text-xs text-slate-300"></i>
                        <input type="text" name="sequences[{{ $key }}][prefix]"
                          value="{{ old("sequences.$key.prefix", strtoupper($key) == 'INVOICE' ? 'INV' : (strtoupper($key) == 'QUOTE' ? 'QT' : '')) }}"
                          class="w-full py-2 font-semibold text-xs" placeholder="Prefix">
                      </div>
                    </div>
                    <div class="space-y-1">
                      <div class="input-icon-group">
                        <i class="fas fa-outdent text-xs text-slate-300"></i>
                        <input type="text" name="sequences[{{ $key }}][postfix]"
                          value="{{ old("sequences.$key.postfix") }}" class="w-full py-2 font-semibold text-xs"
                          placeholder="Postfix">
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Section 3: Localization & Tax --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-rose-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Localization & Tax</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Regional Settings</p>
              </div>
            </div>

            <div class="space-y-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Headquarters
                  Address</label>
                <textarea name="address" rows="3" class="w-full py-2.5 font-semibold bg-slate-50 border-slate-200"
                  placeholder="Full physical office address">{{ old('address') }}</textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Business
                    Phone</label>
                  <div class="input-icon-group">
                    <i class="fas fa-phone text-xs"></i>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                      class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. +1 234 567 890">
                  </div>
                </div>
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Business
                    Email</label>
                  <div class="input-icon-group">
                    <i class="fas fa-envelope text-xs"></i>
                    <input type="email" name="email" value="{{ old('email') }}"
                      class="w-full py-2.5 font-semibold placeholder:font-normal"
                      placeholder="e.g. contact@company.com">
                  </div>
                </div>
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tax Registration No
                    (TRN)</label>
                  <div class="input-icon-group">
                    <i class="fas fa-percent text-xs"></i>
                    <input type="text" name="tax_number" value="{{ old('tax_number') }}"
                      class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. 100-XXX-XXX">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Right Column: Assets & Actions --}}
        <div class="space-y-8">
          {{-- Brand Asset --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft text-center">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4">
              Brand Asset</h3>

            <div class="relative group/logo mx-auto w-40 h-40 mb-6">
              <div
                class="w-full h-full rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all group-hover/logo:border-brand-400/50">
                <img id="logoPreview"
                  src="https://ui-avatars.com/api/?name=Company&background=f8fafc&color=cbd5e1&size=128"
                  class="w-full h-full object-contain p-4 transition-transform duration-500 group-hover/logo:scale-110"
                  alt="Logo Preview">
              </div>
              <label for="logoInput"
                class="absolute -bottom-2 -right-2 h-10 w-10 bg-white rounded-xl shadow-xl border border-slate-100 flex items-center justify-center cursor-pointer hover:bg-brand-50 hover:text-brand-500 transition-all text-slate-400 z-10">
                <i class="fas fa-camera text-sm"></i>
                <input type="file" name="logo" id="logoInput" class="hidden" accept="image/*">
              </label>
            </div>

            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Company Logo</p>
            <p class="text-[10px] text-slate-400 font-medium">Recommended: Square PNG/SVG (512px)</p>
          </div>

          {{-- Execution Card --}}
          <div class="card p-8 bg-slate-900 border-none shadow-2xl shadow-slate-900/40 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
              <i class="fas fa-rocket text-6xl text-white"></i>
            </div>
            <h3 class="text-white font-black text-lg mb-4 relative z-10">Execute Onboarding</h3>
            <p class="text-slate-400 text-xs font-bold mb-8 leading-relaxed relative z-10">
              Review all fields before saving. This will initialize the tenant container in the multisite ecosystem.
            </p>

            <div class="space-y-4">
              <button type="submit"
                class="w-full btn btn-primary py-4 shadow-xl shadow-brand-500/20 font-black tracking-tight flex items-center justify-center gap-3">
                <i class="fas fa-check-circle opacity-50"></i> Initialize Tenant
              </button>
              <a href="{{ route('tenants.index') }}"
                class="w-full flex items-center justify-center py-4 text-xs font-black text-slate-400 hover:text-white transition-colors uppercase tracking-widest">
                Abort Process
              </a>
            </div>
          </div>

          {{-- Help Widget --}}
          <div class="bg-brand-50/50 rounded-2xl p-6 border border-brand-100">
            <h4 class="text-[11px] font-black text-brand-600 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-lightbulb"></i> Deployment Guide
            </h4>
            <ul class="space-y-3">
              <li class="flex items-start gap-2.5">
                <div
                  class="h-4 w-4 rounded-full bg-brand-500 text-white flex items-center justify-center text-[8px] mt-0.5">
                  1</div>
                <p class="text-[11px] text-slate-600 font-bold leading-tight">Code must be unique across all system
                  clusters.</p>
              </li>
              <li class="flex items-start gap-2.5">
                <div
                  class="h-4 w-4 rounded-full bg-brand-500 text-white flex items-center justify-center text-[8px] mt-0.5">
                  2</div>
                <p class="text-[11px] text-slate-600 font-bold leading-tight">Document prefixes cannot be changed after
                  first invoice.</p>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Logo Preview Engine
      $('#logoInput').on('change', function(e) {
        const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
        if (file) {
          const reader = new FileReader();
          reader.onload = function(ev) {
            $('#logoPreview').attr('src', ev.target.result).addClass('animate-pulse-slow');
            setTimeout(() => $('#logoPreview').removeClass('animate-pulse-slow'), 1000);
          };
          reader.readAsDataURL(file);
        }
      });
    });
  </script>
@endpush
