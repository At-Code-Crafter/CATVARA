@extends('catvara.layouts.app')

@section('title', 'Create User')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Create User</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Register a new administrative user and define access level.</p>
      </div>
      <div>
        <a href="{{ route('users.index') }}" class="btn btn-white min-w-[120px]">
          <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
      </div>
    </div>

    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar: Identity & Info -->
        <div class="lg:col-span-4 space-y-6">
          <div class="card border-slate-100 bg-white shadow-soft overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/20">
              <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-id-badge text-brand-400"></i> Identity
              </h3>
            </div>
            <div class="p-8 text-center">
              <div class="relative group inline-block mb-6">
                <div
                  class="h-32 w-32 rounded-3xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all group-hover:border-brand-400">
                  <img id="previewImage" src="{{ asset('theme/adminlte/dist/img/user2-160x160.jpg') }}"
                    class="h-full w-full object-cover" alt="Preview">
                </div>
                <label for="photoInput"
                  class="absolute -bottom-2 -right-2 h-10 w-10 bg-brand-400 text-white rounded-xl shadow-lg shadow-brand-400/30 flex items-center justify-center cursor-pointer hover:bg-brand-500 transition-all active:scale-90">
                  <i class="fas fa-camera text-sm"></i>
                  <input type="file" name="profile_photo" id="photoInput" class="hidden" accept="image/*">
                </label>
              </div>
              <div class="text-left space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Profile Photo</p>
                <p class="text-[10px] text-slate-400 font-medium ml-1">Recommended: 250×250 (JPG/PNG)</p>
              </div>
            </div>
          </div>

          <div class="p-6 bg-brand-50/50 border border-brand-100/50 rounded-2xl">
            <div class="flex gap-3">
              <div class="h-8 w-8 rounded-lg bg-brand-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-info-circle text-brand-400 text-xs"></i>
              </div>
              <div class="space-y-2">
                <p class="text-xs font-bold text-slate-800">Role Guidelines</p>
                <div class="space-y-1.5">
                  <p class="text-[10px] leading-relaxed text-slate-500 font-medium">
                    <span class="text-brand-500 font-bold uppercase tracking-wider">Super Admin:</span> Full system
                    access, including settings and user management.
                  </p>
                  <p class="text-[10px] leading-relaxed text-slate-500 font-medium">
                    <span class="text-slate-700 font-bold uppercase tracking-wider">Admin:</span> Focuses on operational
                    modules like Sales, Catalog, and Inventory.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Form -->
        <div class="lg:col-span-8 space-y-6">
          <div class="card border-slate-100 bg-white shadow-soft overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/20">
              <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-user-plus text-brand-400"></i> Account Details
              </h3>
            </div>
            <div class="p-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Full Name <span
                      class="text-red-400">*</span></label>
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <i class="far fa-user text-slate-400 group-focus-within:text-brand-400 transition-colors"></i>
                    </div>
                    <input type="text" name="name" value="{{ old('name') }}"
                      class="w-full rounded-xl border-slate-200 text-sm pl-10 h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all @error('name') border-red-400 @enderror"
                      placeholder="e.g. John Doe">
                  </div>
                  @error('name')
                    <p class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ $message }}</p>
                  @enderror
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Email Address <span
                      class="text-red-400">*</span></label>
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <i class="far fa-envelope text-slate-400 group-focus-within:text-brand-400 transition-colors"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}"
                      class="w-full rounded-xl border-slate-200 text-sm pl-10 h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all @error('email') border-red-400 @enderror"
                      placeholder="john@company.com">
                  </div>
                  @error('email')
                    <p class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">User Type</label>
                  <select name="user_type"
                    class="w-full rounded-xl border-slate-200 text-sm h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all">
                    <option value="ADMIN" {{ old('user_type', 'ADMIN') === 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                    <option value="SUPER_ADMIN" {{ old('user_type') === 'SUPER_ADMIN' ? 'selected' : '' }}>SUPER ADMIN
                    </option>
                  </select>
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Password <span
                      class="text-red-400">*</span></label>
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <i
                        class="fas fa-lock text-slate-400 group-focus-within:text-brand-400 transition-colors text-[10px]"></i>
                    </div>
                    <input type="password" name="password"
                      class="w-full rounded-xl border-slate-200 text-sm pl-10 h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all @error('password') border-red-400 @enderror"
                      placeholder="Min. 8 characters">
                  </div>
                  @error('password')
                    <p class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ $message }}</p>
                  @enderror
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status</label>
                  <div class="flex items-center h-[44px] px-4 bg-slate-50/50 rounded-xl border border-slate-100">
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                        {{ old('is_active', 1) ? 'checked' : '' }}>
                      <div
                        class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-400">
                      </div>
                      <span class="ml-3 text-sm font-bold text-slate-600">Active Account</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-50">
                <a href="{{ route('users.index') }}" class="btn btn-white min-w-[120px]">Cancel</a>
                <button type="submit" class="btn btn-primary min-w-[160px]">
                  <i class="fas fa-check-circle mr-2"></i> Create Account
                </button>
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
      $('#photoInput').on('change', function() {
        const file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
          $('#previewImage').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
      });
    });
  </script>
@endpush
