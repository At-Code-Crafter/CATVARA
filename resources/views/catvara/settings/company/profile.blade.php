@extends('catvara.layouts.app')

@section('title', 'Company Profile')

@section('content')
  <div class="w-full pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Configuration
          </span>
          <span class="text-slate-300">/</span>
          <span class="text-[10px] font-black text-brand-500 uppercase tracking-widest">Profile</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Company Profile</h1>
        <p class="text-slate-500 font-medium mt-1">Manage your organization's identity, branding, and global settings.</p>
      </div>
      <div class="flex items-center gap-2">
        <span
          class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-wider border border-emerald-100">
          <i class="fas fa-check-circle mr-1.5 opacity-70"></i> Active Tenancy
        </span>
      </div>
    </div>

    <form id="profileForm" action="{{ route('settings.company-profile.update', ['company' => $company->uuid]) }}"
      method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

        {{-- Left Column: Identity & Branding --}}
        <div class="xl:col-span-8 space-y-8">

          {{-- General Identity Card --}}
          <div class="card p-8 bg-white shadow-soft border-slate-100">
            <h3 class="text-lg font-black text-slate-800 mb-8 flex items-center gap-3">
              <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-sm">
                <i class="fas fa-building"></i>
              </div>
              Organization Identity
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Name</label>
                <div class="input-icon-group">
                  <i class="fas fa-file-invoice text-xs"></i>
                  <input type="text" name="name" value="{{ old('name', $company->name) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="Name">
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Legal Name</label>
                <div class="input-icon-group">
                  <i class="fas fa-file-invoice text-xs"></i>
                  <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="Legal Name">
                </div>
              </div>


              <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Company Code</label>
                <div
                  class="px-4 py-3 bg-slate-100 border border-slate-200 rounded-2xl text-slate-500 font-black tracking-widest text-sm flex items-center justify-between">
                  <span>{{ $company->code }}</span>
                  <i class="fas fa-lock opacity-30 text-xs"></i>
                </div>
                <p class="text-[10px] text-slate-400 font-bold mt-1 ml-1 leading-relaxed">System-generated identifier.
                  Cannot be changed.</p>
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Website URL</label>
                <div class="input-icon-group">
                  <i class="fas fa-globe text-xs"></i>
                  <input type="url" name="website_url" value="{{ old('website_url', $company->website_url) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="https://example.com">
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Password Expiry
                  (Days)</label>
                <div class="input-icon-group border-amber-100 bg-amber-50/30">
                  <i class="fas fa-key text-xs text-amber-500"></i>
                  <input type="number" name="password_expiry_days"
                    value="{{ old('password_expiry_days', $company->password_expiry_days) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal bg-transparent"
                    placeholder="0 = Never expire">
                </div>
                <p class="text-[9px] text-amber-600 font-bold ml-1">Force users to change password every X days.</p>
              </div>
            </div>
          </div>

          {{-- Contact & Location Card --}}
          <div class="card p-8 bg-white shadow-soft border-slate-100">
            <h3 class="text-lg font-black text-slate-800 mb-8 flex items-center gap-3">
              <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center text-sm">
                <i class="fas fa-map-marked-alt"></i>
              </div>
              Contact & Location
            </h3>

            <div class="space-y-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tax ID / VAT
                    Number</label>
                  <div class="input-icon-group">
                    <i class="fas fa-percentage text-xs"></i>
                    <input type="text" name="tax_number"
                      value="{{ old('tax_number', $company->detail->tax_number ?? '') }}"
                      class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="Tax Number">
                  </div>
                </div>
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Business
                    Phone</label>
                  <div class="input-icon-group">
                    <i class="fas fa-phone text-xs"></i>
                    <input type="text" name="phone" value="{{ old('phone', $company->detail->phone ?? '') }}"
                      class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="Phone Number">
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Headquarters
                  Address</label>
                <textarea name="address" rows="4"
                  class="w-full px-4 py-3 bg-slate-50 border-slate-200 focus:bg-white focus:border-brand-500 rounded-2xl transition-all font-bold text-slate-700 resize-none">{{ old('address', $company->detail->address ?? '') }}</textarea>
              </div>
            </div>
          </div>

        </div>

        {{-- Right Column: Branding & Actions --}}
        <div class="xl:col-span-4 space-y-8">

          {{-- Logo Upload Card --}}
          <div class="card p-8 bg-white shadow-soft border-slate-100 text-center">
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6">Visual Branding</h3>

            <div class="relative group inline-block mx-auto mb-6">
              <div
                class="w-48 h-48 rounded-3xl overflow-hidden border-4 border-slate-50 shadow-2xl bg-white flex items-center justify-center relative transition-transform group-hover:scale-[1.02]">
                <img id="logoPreview"
                  src="{{ $company->logo ? asset('storage/' . $company->logo) : asset('theme/adminlte/dist/img/AdminLTELogo.png') }}"
                  class="max-w-full max-h-full object-contain p-4">

                <div
                  class="absolute inset-0 bg-slate-900/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all cursor-pointer rounded-2xl backdrop-blur-[2px]"
                  onclick="document.getElementById('logoInput').click()">
                  <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center text-white mb-2">
                    <i class="fas fa-camera text-xl"></i>
                  </div>
                  <span class="text-white text-[10px] font-black uppercase tracking-widest">Change Logo</span>
                </div>
              </div>
              <input type="file" name="logo" id="logoInput" class="hidden" accept="image/*">
            </div>

            <p class="text-[10px] text-slate-400 font-medium leading-relaxed max-w-[200px] mx-auto">
              Recommended: Solid PNG or SVG. Max size 2MB.
            </p>
          </div>

          {{-- Document Sequencing Card --}}
          <div class="card p-8 bg-white shadow-soft border-slate-100">
            <h3
              class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center justify-between">
              <span>Document Sequencing</span>
              <i class="fas fa-list-ol opacity-30"></i>
            </h3>

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

          {{-- Save Changes Card --}}
          <div class="card p-6 bg-slate-900 text-white border-0 shadow-xl shadow-slate-200">
            <button type="submit" class="w-full btn btn-primary py-4 shadow-lg shadow-brand-500/20 group">
              <i class="fas fa-save mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
              Save Profile Changes
            </button>
            <p class="text-center text-[10px] text-slate-500 font-bold mt-4 uppercase tracking-widest">
              Last updated: {{ $company->updated_at ? $company->updated_at->diffForHumans() : 'Never' }}
            </p>
          </div>

        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Photo Preview logic
      $('#logoInput').on('change', function() {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            $('#logoPreview').attr('src', e.target.result);
          }
          reader.readAsDataURL(file);
        }
      });

      // AJAX form submission
      $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const $btn = $(this).find('button[type="submit"]');
        const originalHtml = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving...');

        $.ajax({
          url: $(this).attr('action'),
          method: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: response.message,
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true,
            });
          },
          error: function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong';
            Swal.fire({
              icon: 'error',
              title: 'Update Failed',
              text: error
            });
          },
          complete: function() {
            $btn.prop('disabled', false).html(originalHtml);
          }
        });
      });
    });
  </script>
@endpush
