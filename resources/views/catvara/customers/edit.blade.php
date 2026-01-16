@extends('catvara.layouts.app')

@section('title', 'Edit Customer: ' . $customer->display_name)

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('customers.index', $company->uuid) }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Update
            Profile</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Modify Customer Account</h1>
        <p class="text-slate-500 font-medium mt-1">Update registration details for this customer account in the system
          ecosystem.</p>
      </div>
      <div>
        <a href="{{ route('customers.show', [$company->uuid, $customer->id]) }}" class="btn btn-white shadow-soft">
          <i class="fas fa-external-link-alt mr-2 opacity-50"></i> View Profile
        </a>
      </div>
    </div>

    <form action="{{ route('customers.update', [$company->uuid, $customer->id]) }}" method="POST"
      class="ajax-form space-y-8">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Forms --}}
        <div class="lg:col-span-2 space-y-8">

          {{-- Section 1: Customer Identity --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-brand-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-id-card"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Customer Identity</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Modified Profile Details</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Display Name --}}
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Display Name <span
                    class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-signature"></i>
                  <input type="text" name="display_name" required
                    value="{{ old('display_name', $customer->display_name) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. John Doe or Acme Corp">
                </div>
              </div>

              {{-- Type --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Account
                  Category</label>
                <select name="type" id="customer_type" class="w-full pt-1">
                  <option value="INDIVIDUAL" {{ old('type', $customer->type) == 'INDIVIDUAL' ? 'selected' : '' }}>
                    Individual (B2C)</option>
                  <option value="COMPANY" {{ old('type', $customer->type) == 'COMPANY' ? 'selected' : '' }}>Company (B2B)
                  </option>
                </select>
              </div>

              {{-- Tax Number --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tax / Registration
                  #</label>
                <div class="input-icon-group">
                  <i class="fas fa-file-invoice"></i>
                  <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="VAT, GST or Reg No">
                </div>
              </div>

              {{-- Legal Name --}}
              <div
                class="space-y-1.5 md:col-span-2 {{ old('type', $customer->type) == 'COMPANY' ? '' : 'hidden' }} animate-fade-in"
                id="legal_name_container">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Legal Registered
                  Name</label>
                <div class="input-icon-group">
                  <i class="fas fa-landmark"></i>
                  <input type="text" name="legal_name" value="{{ old('legal_name', $customer->legal_name) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="Full legal entity name">
                </div>
              </div>
            </div>
          </div>

          {{-- Section 2: Contact & Social --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-address-book"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Contact Channels</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Communication Access</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                <div class="input-icon-group">
                  <i class="far fa-envelope"></i>
                  <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="customer@example.com">
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Phone Number</label>
                <div class="input-icon-group">
                  <i class="fas fa-phone-alt"></i>
                  <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="+1 (000) 000-0000">
                </div>
              </div>
            </div>
          </div>

          {{-- Section 3: Localization --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-rose-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Localization</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Primary Physical Address</p>
              </div>
            </div>

            <div class="space-y-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Billing / Shipping
                  Address</label>
                <textarea name="address_line_1" rows="3" class="w-full py-2.5 font-semibold bg-slate-50 border-slate-200"
                  placeholder="Street name, building, unit number...">{{ old('address_line_1', $customer->address?->address_line_1) }}</textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Country</label>
                  <select name="country_id" id="country_id" class="w-full pt-1">
                    <option value="">Select country...</option>
                    @foreach ($countries as $country)
                      <option value="{{ $country->id }}" data-uuid="{{ $country->uuid }}"
                        {{ old('country_id', $customer->address?->country_id) == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">State /
                    Region</label>
                  <select name="state_id" id="state_id" class="w-full pt-1">
                    <option value="">Select state...</option>
                    @foreach ($states as $st)
                      <option value="{{ $st->id }}"
                        {{ old('state_id', $customer->address?->state_id) == $st->id ? 'selected' : '' }}>
                        {{ $st->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Town / City</label>
                  <div class="input-icon-group">
                    <i class="fas fa-city"></i>
                    <input type="text" name="city" value="{{ old('city', $customer->address?->city) }}"
                      class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. London">
                  </div>
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Postal Code</label>
                  <div class="input-icon-group">
                    <i class="fas fa-mail-bulk"></i>
                    <input type="text" name="zip_code" value="{{ old('zip_code', $customer->address?->zip_code) }}"
                      class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. SW1E 5JL">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Right Column: Settings & Execution --}}
        <div class="space-y-8">

          {{-- Billing Settings --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4">
              Billing Defaults</h3>

            <div class="space-y-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Payment Term</label>
                <select name="payment_term_id" class="w-full pt-1">
                  <option value="">Standard Terms</option>
                  @foreach ($paymentTerms as $term)
                    <option value="{{ $term->id }}"
                      {{ old('payment_term_id', $customer->payment_term_id) == $term->id ? 'selected' : '' }}>
                      {{ $term->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Global Discount
                  (%)</label>
                <div class="input-icon-group">
                  <i class="fas fa-percentage"></i>
                  <input type="number" name="percentage_discount" step="0.01" min="0" max="100"
                    value="{{ old('percentage_discount', $customer->percentage_discount) }}"
                    class="w-full py-2.5 font-semibold" placeholder="0.00">
                </div>
              </div>

              <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                <div>
                  <h4 class="font-bold text-slate-800 text-[11px] uppercase tracking-wider">Account Visibility</h4>
                  <p class="text-[10px] text-slate-400 font-medium tracking-tight">Allow in transactions</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="hidden" name="is_active" value="0">
                  <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                    {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                  <div
                    class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500">
                  </div>
                </label>
              </div>
            </div>
          </div>

          {{-- Execution Card --}}
          <div class="card p-8 bg-slate-900 border-none shadow-2xl shadow-slate-900/40 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
              <i class="fas fa-save text-6xl text-white"></i>
            </div>
            <h3 class="text-white font-black text-lg mb-4 relative z-10">Execute Updates</h3>
            <p class="text-slate-400 text-xs font-bold mb-8 leading-relaxed relative z-10">
              Updating this profile will reflect across all system modules immediately. Review changes carefully.
            </p>

            <div class="space-y-4">
              <button type="submit"
                class="w-full btn btn-primary py-4 shadow-xl shadow-brand-500/20 font-black tracking-tight flex items-center justify-center gap-3">
                <i class="fas fa-check-circle opacity-50"></i> Save Changes
              </button>
              <a href="{{ route('customers.index', $company->uuid) }}"
                class="w-full flex items-center justify-center py-4 text-xs font-black text-slate-400 hover:text-white transition-colors uppercase tracking-widest">
                Dismiss
              </a>
            </div>
          </div>

          {{-- Metadata Widget --}}
          <div class="bg-indigo-50/50 rounded-2xl p-6 border border-indigo-100">
            <h4 class="text-[11px] font-black text-indigo-600 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-info-circle"></i> Profile Metadata
            </h4>
            <div class="space-y-2">
              <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest">
                <span class="text-slate-400">Record Origin</span>
                <span class="text-slate-600">Manual Entry</span>
              </div>
              <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest">
                <span class="text-slate-400">Lifecycle</span>
                <span class="text-slate-600">{{ $customer->created_at->diffForHumans() }}</span>
              </div>
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
      // Toggle Legal Name
      function toggleLegalName() {
        if ($('#customer_type').val() === 'COMPANY') {
          $('#legal_name_container').removeClass('hidden').hide().fadeIn(400);
        } else {
          $('#legal_name_container').fadeOut(300, function() {
            $(this).addClass('hidden');
          });
        }
      }

      $('#customer_type').on('change', toggleLegalName);
      // Initial state handled via PHP class, but call JS for smoothness if data changes
      // No initial call needed as PHP handles 'hidden' class based on DB value.

      // Country -> State cascading
      $('#country_id').on('change', function() {
        const countryUuid = $(this).find(':selected').data('uuid');
        const $stateSelect = $('#state_id');

        $stateSelect.prop('disabled', true).html('<option value="">Loading...</option>');

        if (countryUuid) {
          $.get(`/settings/countries/${countryUuid}/states`, function(states) {
            let options = '<option value="">Select state...</option>';
            states.forEach(state => {
              options += `<option value="${state.id}">${state.name}</option>`;
            });
            $stateSelect.html(options).prop('disabled', false);
          });
        } else {
          $stateSelect.html('<option value="">Select country first...</option>').prop('disabled', true);
        }
      });
    });
  </script>
@endpush
