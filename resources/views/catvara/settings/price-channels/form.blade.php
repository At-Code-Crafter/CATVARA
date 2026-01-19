@extends('catvara.layouts.app')

@section('title', isset($priceChannel) ? 'Edit Pricing Channel' : 'Create Pricing Channel')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('price-channels.index') }}" class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Settings</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($priceChannel) ? 'Edit Pricing Channel' : 'Create Pricing Channel' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Configure sales channel for pricing strategies</p>
      </div>
    </div>

    <form
      action="{{ isset($priceChannel) ? route('price-channels.update', $priceChannel->id) : route('price-channels.store') }}"
      method="POST" class="space-y-8">
      @csrf
      @if (isset($priceChannel))
        @method('PUT')
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Form Fields --}}
        <div class="lg:col-span-2 space-y-8">
          {{-- Channel Details --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-purple-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-tags"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Channel Details</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Basic Information</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Channel Code
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-fingerprint"></i>
                  <input type="text" name="code" value="{{ old('code', $priceChannel->code ?? '') }}" required
                    class="w-full py-2.5 font-semibold uppercase" placeholder="e.g. WHOLESALE">
                </div>
                @error('code')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Channel Name
                  <span class="text-rose-500">*</span></label>
                <div class="input-icon-group">
                  <i class="fas fa-tag"></i>
                  <input type="text" name="name" value="{{ old('name', $priceChannel->name ?? '') }}" required
                    class="w-full py-2.5 font-semibold" placeholder="e.g. Wholesale Pricing">
                </div>
                @error('name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="space-y-1.5 md:col-span-2">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $priceChannel->is_active ?? true) ? 'checked' : '' }}
                    class="h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-purple-500 checked:border-purple-500 relative">
                  <span class="text-sm font-bold text-slate-700">Active Channel</span>
                </label>
                <p class="text-[10px] text-slate-400 font-medium ml-8">Enable this channel for use across the system</p>
              </div>
            </div>
          </div>
        </div>

        {{-- Right Column: Actions --}}
        <div class="space-y-8">
          {{-- Save Card --}}
          <div class="card p-8 bg-slate-900 border-none shadow-2xl shadow-slate-900/40">
            <h3 class="text-white font-black text-lg mb-6 flex items-center gap-2">
              <i class="fas fa-save text-brand-400"></i> Save Channel
            </h3>

            <div class="space-y-6">
              <button type="submit"
                class="w-full btn btn-primary py-4 shadow-xl shadow-brand-500/20 font-black tracking-tight flex items-center justify-center gap-3">
                <i class="fas fa-check opacity-50"></i>
                {{ isset($priceChannel) ? 'Update Channel' : 'Create Channel' }}
              </button>

              <a href="{{ route('price-channels.index') }}"
                class="w-full flex items-center justify-center py-2 text-[10px] font-black text-slate-500 hover:text-rose-400 transition-colors uppercase tracking-widest">
                Cancel
              </a>
            </div>
          </div>

          @if (isset($priceChannel))
            {{-- Info Card --}}
            <div class="card p-6 bg-white border-slate-100 shadow-soft">
              <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-purple-400"></i> Channel Info
              </h3>
              <div class="space-y-3 text-xs">
                <div class="flex justify-between">
                  <span class="text-slate-400 font-bold">Created</span>
                  <span class="text-slate-600 font-bold">{{ $priceChannel->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400 font-bold">Last Updated</span>
                  <span class="text-slate-600 font-bold">{{ $priceChannel->updated_at->format('M d, Y') }}</span>
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
      // Add checkmark icon to checkbox when checked
      $('input[type="checkbox"][name="is_active"]').on('change', function() {
        if ($(this).is(':checked')) {
          $(this).after(
            '<span class="absolute text-white top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none"><i class="fas fa-check text-[10px]"></i></span>'
          );
        } else {
          $(this).next('span').remove();
        }
      }).trigger('change');
    });
  </script>
@endpush
