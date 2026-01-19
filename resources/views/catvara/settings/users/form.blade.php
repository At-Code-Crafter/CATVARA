@extends('catvara.layouts.app')

@section('title', isset($user) ? 'Edit Team Member' : 'Add Team Member')

@section('content')
  <div class="space-y-8 animate-fade-in pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
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
          {{ isset($user) ? 'Edit Member: ' . $user->name : 'Add New Team Member' }}
        </h1>
        <p class="text-slate-400 text-sm font-bold">
          {{ isset($user) ? 'Update account details and company roles.' : 'Create a new user account and assign roles for ' . $company->name . '.' }}
        </p>
      </div>
    </div>

    <form
      action="{{ isset($user) ? route('settings.users.update', [$company->uuid, $user->id]) : route('settings.users.store', ['company' => $company->uuid]) }}"
      method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      @csrf
      @if (isset($user))
        @method('PUT')
      @endif

      {{-- Left Side: Main Form --}}
      <div class="lg:col-span-8 space-y-8">
        {{-- General Information --}}
        <div class="card p-8 bg-white shadow-soft border-slate-100">
          <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-sm">
              <i class="fas fa-user"></i>
            </div>
            General Information
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
              <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
                class="w-full @error('name') border-rose-400 @enderror" placeholder="John Doe" required>
              @error('name')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="space-y-2">
              <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
              <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                class="w-full @error('email') border-rose-400 @enderror" placeholder="john@example.com" required>
              @error('email')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="space-y-2">
              <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
              <input type="password" name="password" class="w-full @error('password') border-rose-400 @enderror"
                placeholder="{{ isset($user) ? 'Leave blank to keep current' : 'Enter strong password' }}"
                {{ isset($user) ? '' : 'required' }}>
              @error('password')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="space-y-2">
              <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Confirm Password</label>
              <input type="password" name="password_confirmation" class="w-full" placeholder="Repeat password">
            </div>
          </div>
        </div>

        {{-- Role Assignment --}}
        <div class="card p-8 bg-white shadow-soft border-slate-100">
          <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-purple-50 text-purple-500 flex items-center justify-center text-sm">
              <i class="fas fa-shield-alt"></i>
            </div>
            Company Roles
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($roles as $role)
              <label
                class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:bg-slate-50 transition-all cursor-pointer group">
                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                  class="h-5 w-5 rounded border-slate-300 text-brand-500 focus:ring-brand-500"
                  {{ in_array($role->id, old('role_ids', $userRoleIds ?? [])) ? 'checked' : '' }}>
                <div>
                  <p class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition-colors">
                    {{ $role->name }}</p>
                  @if ($role->description)
                    <p class="text-[11px] text-slate-400 font-medium">{{ $role->description }}</p>
                  @endif
                </div>
              </label>
            @endforeach
          </div>
          @error('role_ids')
            <p class="text-[10px] text-rose-500 font-bold mt-4 ml-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Right Side: Secondary Actions --}}
      <div class="lg:col-span-4 space-y-6">
        {{-- Save Card --}}
        <div class="card p-6 bg-slate-900 text-white border-0 shadow-xl shadow-slate-200">
          <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-6">Actions</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/10">
              <div>
                <p class="text-xs font-black uppercase tracking-widest text-slate-400">Account Status</p>
                <p class="text-[10px] text-slate-500 font-bold">Active users can login</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                  {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                <div
                  class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                </div>
              </label>
            </div>

            <button type="submit" class="w-full btn btn-primary py-4 shadow-lg shadow-brand-500/20">
              <i class="fas fa-save mr-2 opacity-50"></i> {{ isset($user) ? 'Update Member' : 'Create Member' }}
            </button>
            <a href="{{ route('settings.users.index', ['company' => $company->uuid]) }}"
              class="w-full btn btn-white py-4 bg-white/5 border-white/10 text-white hover:bg-white/10">
              Cancel
            </a>
          </div>
        </div>

        @if (isset($user))
          {{-- Delete Card --}}
          <div class="card p-6 bg-white border-rose-100">
            <h3 class="text-xs font-black uppercase tracking-widest text-rose-400 mb-4">Danger Zone</h3>
            <p class="text-[11px] text-slate-500 font-bold mb-4">
              Removing this user will revoke all their access to this company and its resources.
            </p>
            <button type="button" onclick="confirmDelete()"
              class="w-full px-4 py-3 rounded-xl bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-wider border border-rose-100 hover:bg-rose-600 hover:text-white transition-all">
              <i class="fas fa-user-minus mr-2"></i> Remove From Company
            </button>
          </div>
        @endif
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
