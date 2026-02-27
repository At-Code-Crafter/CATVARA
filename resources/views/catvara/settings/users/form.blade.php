@extends('catvara.layouts.app')

@section('title', isset($user) ? 'Edit Team Member' : 'Add Team Member')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('settings.users.index', ['company' => $company->uuid]) }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Team Management
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
          {{ isset($user) ? 'Edit Team Member' : 'Add New Team Member' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1">
          {{ isset($user) ? 'Update account details and company roles.' : 'Create a new user account and assign roles.' }}
        </p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('settings.users.index', ['company' => $company->uuid]) }}" class="btn btn-white shadow-soft">
          Cancel
        </a>
        <button type="submit" form="userForm" class="btn btn-primary shadow-lg shadow-brand-500/30">
          <i class="fas fa-save mr-2"></i> {{ isset($user) ? 'Update Member' : 'Save Member' }}
        </button>
      </div>
    </div>

    <form id="userForm"
      action="{{ isset($user) ? route('settings.users.update', [$company->uuid, $user->id]) : route('settings.users.store', ['company' => $company->uuid]) }}"
      method="POST">
      @csrf
      @if (isset($user))
        @method('PUT')
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Main Form --}}
        <div class="lg:col-span-2 space-y-8">

          {{-- General Information Card --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-brand-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-user"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">General Information</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Account Details</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Full Name --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Full Name <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-user"></i>
                  <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal @error('name') border-rose-400 @enderror"
                    placeholder="John Doe" required>
                </div>
                @error('name')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Email --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Email Address <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-envelope"></i>
                  <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal @error('email') border-rose-400 @enderror"
                    placeholder="john@example.com" required>
                </div>
                @error('email')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Password --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Password @if(!isset($user))<span class="text-rose-500">*</span>@endif
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="password"
                    class="w-full py-2.5 font-semibold placeholder:font-normal @error('password') border-rose-400 @enderror"
                    placeholder="{{ isset($user) ? 'Leave blank to keep current' : 'Enter strong password' }}"
                    {{ isset($user) ? '' : 'required' }}>
                </div>
                @error('password')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Confirm Password --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Confirm Password
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="password_confirmation"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="Repeat password">
                </div>
              </div>
            </div>
          </div>

          {{-- Role Assignment Card --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-purple-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-shield-alt"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Company Roles</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Access Permissions</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              @foreach ($roles as $role)
                <label
                  class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer group has-[:checked]:bg-brand-50 has-[:checked]:border-brand-200">
                  <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                    class="h-5 w-5 rounded border-slate-300 text-brand-500 focus:ring-brand-500"
                    {{ in_array($role->id, old('role_ids', $userRoleIds ?? [])) ? 'checked' : '' }}>
                  <div>
                    <p class="text-sm font-bold text-slate-700 group-hover:text-slate-900">{{ $role->name }}</p>
                    @if ($role->description)
                      <p class="text-[11px] text-slate-400 font-medium">{{ $role->description }}</p>
                    @endif
                  </div>
                </label>
              @endforeach
            </div>
            @error('role_ids')
              <p class="text-xs text-rose-500 font-bold mt-4 ml-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        {{-- Right Column: Status & Actions --}}
        <div class="space-y-8">

          {{-- Status Card --}}
          <div class="card p-6 bg-white border-slate-100 shadow-soft">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4">
              Account Status
            </h3>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-bold text-slate-700 text-sm">Active Account</p>
                <p class="text-xs text-slate-400">Allow user to login</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                  {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                <div
                  class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                </div>
              </label>
            </div>
          </div>

          {{-- Info Card --}}
          <div class="bg-sky-50/50 rounded-2xl p-6 border border-sky-100">
            <h4 class="text-[11px] font-black text-sky-600 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-info-circle"></i> Team Members
            </h4>
            <p class="text-xs text-sky-700 leading-relaxed font-medium">
              Team members can access company data based on their assigned roles.
              Roles control what actions they can perform in the system.
            </p>
          </div>

          {{-- Danger Zone (Edit Mode Only) --}}
          @if (isset($user))
            <div class="card p-6 bg-white border-rose-100 shadow-soft">
              <h3 class="text-sm font-black text-rose-500 uppercase tracking-widest mb-4 border-b border-rose-50 pb-4">
                Danger Zone
              </h3>
              <p class="text-xs text-slate-500 font-medium mb-4">
                Removing this user will revoke all their access to this company and its resources.
              </p>
              <button type="button" onclick="confirmDelete()"
                class="w-full btn bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all">
                <i class="fas fa-user-minus mr-2"></i> Remove From Company
              </button>
            </div>
          @endif
        </div>
      </div>
    </form>

    @if (isset($user))
      <form id="delete-form" action="{{ route('settings.users.destroy', [$company->uuid, $user->id]) }}" method="POST"
        class="hidden">
        @csrf
        @method('DELETE')
      </form>
    @endif
  </div>
@endsection

@push('scripts')
  <script>
    function confirmDelete() {
      Swal.fire({
        title: 'Are you sure?',
        text: "You are about to remove this user from {{ $company->name }}. They will lose all access to this company's data.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, remove them!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('delete-form').submit();
        }
      })
    }
  </script>
@endpush
