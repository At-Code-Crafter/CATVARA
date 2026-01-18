@extends('catvara.layouts.app')

@section('title', isset($brand) ? 'Edit Brand' : 'Create Brand')

@section('content')
  <!-- Header -->
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
        {{ isset($brand) ? 'Edit Brand' : 'Create Brand' }}
      </h1>
      <p class="text-slate-500 mt-1">
        {{ isset($brand) ? 'Update brand details and hierarchy.' : 'Add a new brand to your catalog.' }}
      </p>
    </div>
    <a href="{{ company_route('catalog.brands.index') }}"
      class="text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
      <i class="fas fa-arrow-left mr-1"></i> Back to List
    </a>
  </div>

  <form
    action="{{ isset($brand) ? company_route('catalog.brands.update', ['brand' => $brand->id]) : company_route('catalog.brands.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @if (isset($brand))
      @method('PUT')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      {{-- Left Side: Main Info (Span 8) --}}
      <div class="lg:col-span-8 space-y-8">
        <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
          <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Brand Details</h3>

            <div class="flex items-center gap-3">
              <label for="is_active" class="text-sm font-medium text-slate-600 cursor-pointer">Active</label>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" id="is_active" value="1" class="sr-only peer"
                  {{ old('is_active', $brand->is_active ?? true) ? 'checked' : '' }}>
                <div
                  class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                </div>
              </label>
            </div>
          </div>

          <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 gap-6">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Brand Name <span
                    class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $brand->name ?? '') }}"
                  class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                  placeholder="e.g. Nike, Apple" required>
                @error('name')
                  <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>

              <!-- Parent Brand -->
              <div>
                <label for="parent_id" class="block text-sm font-semibold text-slate-700 mb-2">Parent Brand</label>
                <select name="parent_id" id="parent_id" class="select2 w-full"
                  data-placeholder="Select a parent brand (Optional)">
                  <option value="">None (Top Level Brand)</option>
                  @foreach ($brands as $b)
                    <option value="{{ $b->id }}"
                      {{ old('parent_id', $brand->parent_id ?? '') == $b->id ? 'selected' : '' }}>
                      {{ $b->name }}
                    </option>
                  @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500">Enable "Parent Brand Option" by selecting a brand here if this is a sub-brand.</p>
                @error('parent_id')
                  <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>

              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="4"
                  class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                  placeholder="Tell us about this brand...">{{ old('description', $brand->description ?? '') }}</textarea>
                @error('description')
                  <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Right Side: Logo & Actions --}}
      <div class="lg:col-span-4 space-y-8">
        {{-- Logo --}}
        <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Brand Logo</h3>
          
          <div class="flex flex-col items-center">
            <div class="mb-4 w-32 h-32 rounded-2xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50 relative group">
              @if(isset($brand) && $brand->logo)
                <img src="{{ asset('storage/' . $brand->logo) }}" id="logo-preview" class="w-full h-full object-cover">
              @else
                <div id="logo-placeholder" class="text-slate-400 flex flex-col items-center">
                  <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                  <span class="text-xs font-semibold">Upload Logo</span>
                </div>
                <img id="logo-preview" class="w-full h-full object-cover hidden">
              @endif
            </div>
            
            <input type="file" name="logo" id="logo-input" class="hidden" accept="image/*">
            <button type="button" onclick="document.getElementById('logo-input').click()" class="btn btn-white btn-sm w-full">
              <i class="fas fa-image mr-2 text-brand-500"></i> Change Logo
            </button>
            <p class="text-[10px] text-slate-400 mt-3 text-center">Max size 2MB. Recommended 200x200px.</p>
          </div>
        </div>

        {{-- Actions --}}
        <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6 sticky top-6">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Actions</h3>

          <div class="space-y-4">
            <button type="submit"
              class="w-full px-5 py-3 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
              <i class="fas fa-save mr-2"></i>
              {{ isset($brand) ? 'Update Brand' : 'Create Brand' }}
            </button>
            <a href="{{ company_route('catalog.brands.index') }}"
              class="w-full flex items-center justify-center px-5 py-3 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors shadow-sm">
              Cancel
            </a>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
    document.getElementById('logo-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('logo-preview');
                const placeholder = document.getElementById('logo-placeholder');
                preview.src = event.target.result;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
