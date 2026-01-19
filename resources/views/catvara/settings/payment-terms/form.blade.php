@extends('catvara.layouts.app')

@section('title', isset($payment_term) ? 'Edit Payment Term' : 'Create Payment Term')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ company_route('settings.payment-terms.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Settings</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($payment_term) ? 'Edit Payment Term' : 'Create Payment Term' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Configure payment deadline settings</p>
      </div>
    </div>

    <form
      action="{{ isset($payment_term) ? company_route('settings.payment-terms.update', ['payment_term' => $payment_term->id]) : company_route('settings.payment-terms.store') }}"
      method="POST" class="space-y-8">
      @csrf
      @if (isset($payment_term))
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
                <i class="fas fa-clock"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Term Details</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Basic Information</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Term Code
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-fingerprint"></i>
                  <input type="text" name="code" value="{{ old('code', $payment_term->code ?? '') }}" required
                    class="w-full py-2.5 font-semibold uppercase" placeholder="e.g. NET_30">
                </div>
                @error('code')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Term Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-tag"></i>
                  <input type="text" name="name" value="{{ old('name', $payment_term->name ?? '') }}" required
                    class="w-full py-2.5 font-semibold" placeholder="e.g. Net 30 Days">
                </div>
                @error('name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Due Days
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-calendar-day"></i>
                  <input type="number" name="due_days" value="{{ old('due_days', $payment_term->due_days ?? 0) }}"
                    required min="0" class="w-full py-2.5 font-semibold" placeholder="0">
                </div>
                <p class="text-[10px] text-slate-400 ml-1">Number of days before payment is due. Use 0 for immediate
                  payment.</p>
                @error('due_days')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Settings --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-cogs"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Settings</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Status Options</p>
              </div>
            </div>

            <div class="space-y-4">
              <label
                class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border border-slate-100 hover:bg-slate-50">
                <input type="checkbox" name="is_active" value="1"
                  {{ old('is_active', $payment_term->is_active ?? true) ? 'checked' : '' }}
                  class="h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-blue-500 checked:border-blue-500 relative">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-700">Active Term</span>
                  <span class="text-[10px] text-slate-400 font-medium">Enable this term for selection in invoices and
                    orders</span>
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
              <i class="fas fa-save text-brand-400"></i> Save Term
            </h3>

            <div class="space-y-6">
              <button type="submit"
                class="w-full btn btn-primary py-4 shadow-xl shadow-brand-500/20 font-black tracking-tight flex items-center justify-center gap-3">
                <i class="fas fa-check opacity-50"></i>
                {{ isset($payment_term) ? 'Update Term' : 'Create Term' }}
              </button>

              <a href="{{ company_route('settings.payment-terms.index') }}"
                class="w-full flex items-center justify-center py-2 text-[10px] font-black text-slate-500 hover:text-rose-400 transition-colors uppercase tracking-widest">
                Cancel
              </a>
            </div>
          </div>

          @if (isset($payment_term))
            {{-- Info Card --}}
            <div class="card p-6 bg-white border-slate-100 shadow-soft">
              <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-blue-400"></i> Term Info
              </h3>
              <div class="space-y-3 text-xs">
                <div class="flex justify-between">
                  <span class="text-slate-400 font-bold">Created</span>
                  <span class="text-slate-600 font-bold">{{ $payment_term->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400 font-bold">Last Updated</span>
                  <span class="text-slate-600 font-bold">{{ $payment_term->updated_at->format('M d, Y') }}</span>
                </div>
              </div>
            </div>

            {{-- Delete Card --}}
            <div class="card p-6 bg-rose-50 border-rose-100 shadow-soft">
              <h3 class="text-sm font-black text-rose-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> Danger Zone
              </h3>
              <p class="text-xs text-rose-500 mb-4">Deleting this payment term will remove it permanently.</p>
              <form action="{{ company_route('settings.payment-terms.destroy', ['payment_term' => $payment_term->id]) }}"
                method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full btn bg-rose-500 hover:bg-rose-600 text-white py-2 text-xs font-bold"
                  onclick="return confirm('Are you sure you want to delete this payment term?')">
                  <i class="fas fa-trash mr-2"></i> Delete Term
                </button>
              </form>
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
