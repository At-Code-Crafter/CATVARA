@extends('catvara.layouts.app')

@section('title', isset($permission) ? 'Edit Permission' : 'Add Permission')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('permissions.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Permissions
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($permission) ? 'Edit Permission' : 'Create New Permission' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">Configure permission access control settings.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('permissions.index') }}" class="btn btn-white shadow-soft">
          Cancel
        </a>
        <button type="submit" form="permissionForm" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-save mr-2"></i> {{ isset($permission) ? 'Update Permission' : 'Save Permission' }}
        </button>
      </div>
    </div>

    <form id="permissionForm"
      action="{{ isset($permission) ? route('permissions.update', $permission->id) : route('permissions.store') }}"
      method="POST">
      @csrf
      @if (isset($permission))
        @method('PUT')
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Main Form --}}
        <div class="lg:col-span-2 space-y-8">

          {{-- Permission Information Card --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-orange-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-key"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Permission Information</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Basic Details</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Permission Name --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Permission Name <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-tag"></i>
                  <input type="text" name="name" value="{{ old('name', $permission->name ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="e.g. View Products" required>
                </div>
                @error('name')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Permission Slug --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Slug <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-link"></i>
                  <input type="text" name="slug" value="{{ old('slug', $permission->slug ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal font-mono"
                    placeholder="e.g. products.view" required>
                </div>
                @error('slug')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Module --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Module <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-cubes"></i>
                  <select name="module_id"
                    class="w-full py-2.5 font-semibold rounded-xl border-slate-200 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 shadow-sm pl-10 pr-4 appearance-none bg-no-repeat"
                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns%3D%22http://www.w3.org/2000/svg%22 viewBox%3D%220 0 4 5%22%3E%3Cpath fill%3D%22%236b7280%22 d%3D%22M2 0L0 2h4zm0 5L0 3h4z%22/%3E%3C/svg%3E'); background-position: right 12px center; background-size: 8px 10px;"
                    required>
                    <option value="">Select Module</option>
                    @foreach ($modules as $module)
                      <option value="{{ $module->id }}"
                        {{ old('module_id', $permission->module_id ?? '') == $module->id ? 'selected' : '' }}>
                        {{ $module->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                @error('module_id')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Guard Name --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Guard Name
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-shield-alt"></i>
                  <input type="text" name="guard_name" value="{{ old('guard_name', $permission->guard_name ?? 'web') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="e.g. web">
                </div>
                @error('guard_name')
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
                  placeholder="Brief description of what this permission allows...">{{ old('description', $permission->description ?? '') }}</textarea>
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
                <p class="font-bold text-slate-700 text-sm">Permission Active</p>
                <p class="text-xs text-slate-400">Enable this permission</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                  {{ old('is_active', $permission->is_active ?? true) ? 'checked' : '' }}>
                <div
                  class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                </div>
              </label>
            </div>
          </div>

          {{-- Info Card --}}
          <div class="bg-orange-50/50 rounded-2xl p-6 border border-orange-100">
            <h4 class="text-[11px] font-black text-orange-600 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-info-circle"></i> About Permissions
            </h4>
            <p class="text-xs text-orange-700 leading-relaxed font-medium">
              Permissions define specific actions users can perform within the system.
              Use a consistent naming convention like <span class="font-mono bg-orange-100 px-1 rounded">resource.action</span>
              (e.g., products.view, orders.create).
            </p>
          </div>

          {{-- Roles Count (Edit Mode) --}}
          @if(isset($permission))
            <div class="card p-6 bg-white border-slate-100 shadow-soft">
              <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-50 pb-4">
                Usage
              </h3>
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                  <i class="fas fa-user-shield text-blue-500"></i>
                </div>
                <div>
                  <p class="text-2xl font-bold text-slate-800">{{ $permission->roles()->count() }}</p>
                  <p class="text-xs text-slate-400 font-medium">Roles with this permission</p>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </form>
  </div>
@endsection
