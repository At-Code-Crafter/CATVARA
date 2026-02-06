@extends('catvara.layouts.app')

@section('title', isset($module) ? 'Edit Module' : 'Add Module')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('modules.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Modules
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($module) ? 'Edit Module' : 'Create New Module' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Configure system module details.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('modules.index') }}" class="btn btn-white shadow-soft">
          Cancel
        </a>
        <button type="submit" form="moduleForm" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-save mr-2"></i> {{ isset($module) ? 'Update Module' : 'Save Module' }}
        </button>
      </div>
    </div>

    <form id="moduleForm"
      action="{{ isset($module) ? route('modules.update', $module->id) : route('modules.store') }}"
      method="POST">
      @csrf
      @if (isset($module))
        @method('PUT')
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Main Form --}}
        <div class="lg:col-span-2 space-y-8">

          {{-- Module Information Card --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-purple-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-cubes"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Module Information</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Basic Details</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Module Name --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Module Name <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-cube"></i>
                  <input type="text" name="name" value="{{ old('name', $module->name ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="e.g. Sales Management" required>
                </div>
                @error('name')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Module Slug --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Slug <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-link"></i>
                  <input type="text" name="slug" value="{{ old('slug', $module->slug ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal font-mono"
                    placeholder="e.g. sales-management" required>
                </div>
                @error('slug')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Description --}}
              <div class="md:col-span-2 space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Description
                </label>
                <textarea name="description" rows="3"
                  class="w-full rounded-xl border-slate-200 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 shadow-sm placeholder-slate-400 text-sm py-3 px-4 font-medium transition-all resize-none"
                  placeholder="Brief description of this module...">{{ old('description', $module->description ?? '') }}</textarea>
                @error('description')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>
        </div>

        {{-- Right Column: Status & Info --}}
        <div class="space-y-8">

          {{-- Status Card --}}
          <div class="card p-6 bg-white border-slate-100 shadow-soft">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4">
              Status
            </h3>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-bold text-slate-700 text-sm">Module Active</p>
                <p class="text-xs text-slate-400">Enable this module</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                  {{ old('is_active', $module->is_active ?? true) ? 'checked' : '' }}>
                <div
                  class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                </div>
              </label>
            </div>
          </div>

          {{-- Info Card --}}
          <div class="bg-purple-50/50 rounded-2xl p-6 border border-purple-100">
            <h4 class="text-[11px] font-black text-purple-600 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-info-circle"></i> About Modules
            </h4>
            <p class="text-xs text-purple-700 leading-relaxed font-medium">
              Modules group related permissions together for easier management.
              Each module can contain multiple permissions that control access
              to specific features.
            </p>
          </div>

          {{-- Permissions Count (Edit Mode) --}}
          @if(isset($module))
            <div class="card p-6 bg-white border-slate-100 shadow-soft">
              <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-50 pb-4">
                Statistics
              </h3>
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center">
                  <i class="fas fa-key text-brand-500"></i>
                </div>
                <div>
                  <p class="text-2xl font-bold text-slate-800">{{ $module->permissions()->count() }}</p>
                  <p class="text-xs text-slate-400 font-medium">Permissions in this module</p>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </form>
  </div>
@endsection
