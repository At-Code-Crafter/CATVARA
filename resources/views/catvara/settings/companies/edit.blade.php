@extends('catvara.layouts.app')

@section('title', 'Edit Tenant Configuration')

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
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Modify Tenant Hub</h1>
        <p class="text-slate-500 font-medium mt-1">Configure parameters for <span
            class="text-brand-500 font-black">{{ $company->name }}</span>.</p>
      </div>
    </div>

    <form action="{{ route('tenants.update', $company->id) }}" class="ajax-form space-y-8" method="POST"
      enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Configuration Panels --}}
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
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Company Display Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-signature text-xs"></i>
                  <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                    class="w-full py-2.5 font-semibold" placeholder="e.g. Acme Corporation">
                </div>
                @error('name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Legal Registered Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-landmark text-xs"></i>
                  <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}" required
                    class="w-full py-2.5 font-semibold" placeholder="e.g. Acme Corp LLC">
                </div>
                @error('legal_name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Internal Code <span
                    class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-fingerprint text-xs"></i>
                  <input type="text" name="code" value="{{ old('code', $company->code) }}" required
                    class="w-full py-2.5 font-semibold uppercase" placeholder="e.g. ACME-01">
                </div>
                @error('code')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Lifecycle Status <span
                    class="text-rose-500">*</span></label>
                <select name="company_status_id" class="w-full pt-1">
                  @foreach ($statuses as $st)
                    <option value="{{ $st->id }}"
                      {{ old('company_status_id', $company->company_status_id) == $st->id ? 'selected' : '' }}>
                      {{ $st->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Official
                  Website</label>
                <div class="input-icon-group">
                  <i class="fas fa-globe text-xs"></i>
                  <input type="url" name="website_url" value="{{ old('website_url', $company->website_url) }}"
                    class="w-full py-2.5 font-semibold" placeholder="https://www.acme.com">
                </div>
              </div>
            </div>
          </div>

          {{-- Section 2: Financial Grid --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Document Sequencing --}}
            <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
              <div class="absolute top-0 left-0 w-1 h-full bg-indigo-400"></div>
              <div class="flex items-center gap-4 mb-8">
                <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center shadow-sm">
                  <i class="fas fa-list-ol"></i>
                </div>
                <div>
                  <h3 class="text-lg font-black text-slate-800 tracking-tight">Sequencing</h3>
                  <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Document Formats</p>
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
                            value="{{ old("sequences.$key.prefix", $sequences[strtoupper($key)]->prefix ?? '') }}"
                            class="w-full py-2 font-semibold text-xs" placeholder="Prefix">
                        </div>
                      </div>
                      <div class="space-y-1">
                        <div class="input-icon-group">
                          <i class="fas fa-outdent text-xs text-slate-300"></i>
                          <input type="text" name="sequences[{{ $key }}][postfix]"
                            value="{{ old("sequences.$key.postfix", $sequences[strtoupper($key)]->postfix ?? '') }}"
                            class="w-full py-2 font-semibold text-xs" placeholder="Postfix">
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            {{-- Fiscal Settings --}}
            <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
              <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
              <div class="flex items-center gap-4 mb-8">
                <div
                  class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-sm">
                  <i class="fas fa-coins"></i>
                </div>
                <div>
                  <h3 class="text-lg font-black text-slate-800 tracking-tight">Fiscal Base</h3>
                  <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Currency & Tax</p>
                </div>
              </div>

              <div class="space-y-6">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Base Currency <span
                      class="text-rose-500">*</span></label>
                  @if ($company->base_currency_id && $company->baseCurrency)
                    <div
                      class="flex items-center px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-black text-slate-500 group">
                      <i class="fas fa-lock mr-2 text-slate-300"></i>
                      {{ $company->baseCurrency->code }} — {{ $company->baseCurrency->name }}
                    </div>
                    <p class="text-[9px] text-slate-400 font-bold tracking-widest mt-1">LOCKED: PERMANENT SYSTEM SEED</p>
                  @else
                    <select name="base_currency_id" class="w-full pt-1">
                      <option value="">Select Currency</option>
                      @foreach ($currencies as $currency)
                        <option value="{{ $currency->id }}"
                          {{ old('base_currency_id', $company->base_currency_id) == $currency->id ? 'selected' : '' }}>
                          {{ $currency->code }} - {{ $currency->name }}
                        </option>
                      @endforeach
                    </select>
                  @endif
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tax Registration
                    (TRN)</label>
                  <div class="input-icon-group">
                    <i class="fas fa-percent text-xs"></i>
                    <input type="text" name="tax_number"
                      value="{{ old('tax_number', $company->detail?->tax_number) }}"
                      class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. 100-XXX-XXX">
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Section 3: Localization & Address --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-rose-400"></div>
            <div class="flex items-center gap-4 mb-4">
              <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Localization</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Office Hub Details</p>
              </div>
            </div>
            <div class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Business
                    Phone</label>
                  <div class="input-icon-group">
                    <i class="fas fa-phone text-xs"></i>
                    <input type="text" name="phone" value="{{ old('phone', $company->detail?->phone) }}"
                      class="w-full py-2.5 font-semibold" placeholder="e.g. +1 234 567 890">
                  </div>
                </div>
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Business
                    Email</label>
                  <div class="input-icon-group">
                    <i class="fas fa-envelope text-xs"></i>
                    <input type="email" name="email" value="{{ old('email', $company->detail?->email) }}"
                      class="w-full py-2.5 font-semibold" placeholder="e.g. contact@company.com">
                  </div>
                </div>
              </div>
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Headquarters
                  Address</label>
                <textarea name="address" rows="3" class="w-full py-2.5 font-semibold bg-slate-50 border-slate-200"
                  placeholder="Full physical headquarters address">{{ old('address', $company->detail?->address) }}</textarea>
              </div>
            </div>
          </div>
          {{-- Section 5: Pricing Channels --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-purple-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-tags"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Pricing Channels</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Sales Channel Access</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              @foreach ($priceChannels as $channel)
                <label
                  class="flex items-center gap-3 p-4 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer group/channel ring-4 ring-transparent hover:ring-slate-50">
                  <div class="relative flex items-center">
                    <input type="checkbox" name="price_channels[]" value="{{ $channel->id }}"
                      class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-purple-500 checked:border-purple-500"
                      {{ (is_array(old('price_channels')) && in_array($channel->id, old('price_channels'))) || $company->priceChannels->contains($channel->id) ? 'checked' : '' }}>
                    <span
                      class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                      <i class="fas fa-check text-[10px]"></i>
                    </span>
                  </div>
                  <div class="flex flex-col">
                    <span
                      class="text-xs font-black text-slate-700 group-hover/channel:text-purple-600 uppercase tracking-tight">{{ $channel->name }}</span>
                    <span
                      class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $channel->code }}</span>
                  </div>
                </label>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Right Column: Brand & Metadata --}}
        <div class="space-y-8">
          {{-- Identity Widget --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft text-center">
            <h3
              class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4 text-left flex items-center gap-2">
              <i class="fas fa-image text-brand-400"></i> Corporate Brand
            </h3>

            <div class="relative group/logo mx-auto w-40 h-40 mb-6">
              @php
                $logoSrc = $company->logo
                    ? asset('storage/' . $company->logo)
                    : 'https://ui-avatars.com/api/?name=' .
                        urlencode($company->name) .
                        '&background=f8fafc&color=cbd5e1&size=128';
              @endphp
              <div
                class="w-full h-full rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all group-hover/logo:border-brand-400/50">
                <img id="logoPreview" src="{{ $logoSrc }}"
                  class="w-full h-full object-contain p-4 transition-transform duration-500 group-hover/logo:scale-110"
                  alt="Logo Preview">
              </div>
              <label for="logoInput"
                class="absolute -bottom-2 -right-2 h-10 w-10 bg-white rounded-xl shadow-xl border border-slate-100 flex items-center justify-center cursor-pointer hover:bg-brand-50 hover:text-brand-500 transition-all text-slate-400 z-10">
                <i class="fas fa-camera text-sm"></i>
                <input type="file" name="logo" id="logoInput" class="hidden" accept="image/*">
              </label>
            </div>

            <div class="space-y-1">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Brandmark Identity</p>
              <p class="text-[10px] text-slate-300 font-medium">SVG, PNG, JPG (Max 2MB)</p>
            </div>
          </div>

          {{-- Execution & Info --}}
          <div class="card p-8 bg-slate-900 border-none shadow-2xl shadow-slate-900/40">
            <h3 class="text-white font-black text-lg mb-6 flex items-center gap-2">
              <i class="fas fa-save text-brand-400"></i> Persist Hub
            </h3>

            <div class="space-y-6">
              <button type="submit"
                class="w-full btn btn-primary py-4 shadow-xl shadow-brand-500/20 font-black tracking-tight flex items-center justify-center gap-3">
                <i class="fas fa-sync-alt opacity-50"></i> Update Configuration
              </button>

              <div class="pt-6 border-t border-slate-800 space-y-4">
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                  <span class="text-slate-500">System Integrity</span>
                  <span class="text-emerald-400 flex items-center gap-1.5">
                    <i class="fas fa-shield-alt"></i> Verified
                  </span>
                </div>
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                  <span class="text-slate-500">Member Since</span>
                  <span class="text-slate-300">{{ $company->created_at->format('M Y') }}</span>
                </div>
              </div>

              <a href="{{ route('tenants.index') }}"
                class="w-full flex items-center justify-center py-2 text-[10px] font-black text-slate-500 hover:text-rose-400 transition-colors uppercase tracking-widest">
                Discard Changes
              </a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Identity Preview Engine
      $('#logoInput').on('change', function(e) {
        const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
        if (file) {
          const reader = new FileReader();
          reader.onload = function(ev) {
            $('#logoPreview').attr('src', ev.target.result).parents('.w-40').addClass('ring-4 ring-brand-50');
            setTimeout(() => $('#logoPreview').parents('.w-40').removeClass('ring-4 ring-brand-50'), 600);
          };
          reader.readAsDataURL(file);
        }
      });
    });
  </script>
@endpush
