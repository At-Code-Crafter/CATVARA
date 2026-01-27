@extends('catvara.layouts.app')

@section('title', 'My Profile')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Account Settings
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">My Profile</h1>
        <p class="text-slate-500 font-medium mt-1">Manage your account settings and password.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      {{-- Left Column: Profile & Password --}}
      <div class="lg:col-span-2 space-y-8">

        {{-- Profile Information Card --}}
        <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden">
          <div class="absolute top-0 left-0 w-1 h-full bg-brand-400"></div>
          <div class="flex items-center gap-4 mb-8">
            <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center shadow-sm">
              <i class="fas fa-user"></i>
            </div>
            <div>
              <h3 class="text-lg font-black text-slate-800 tracking-tight">Profile Information</h3>
              <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Update your details</p>
            </div>
          </div>

          <form method="post" action="{{ route('profile.update') }}" id="profileForm">
            @csrf
            @method('patch')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Name --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Full Name <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-user"></i>
                  <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="Your full name" required>
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
                  <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="your@email.com" required>
                </div>
                @error('email')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="flex items-center justify-end mt-6 pt-6 border-t border-slate-100">
              @if (session('status') === 'profile-updated')
                <span class="text-sm text-emerald-600 font-medium mr-4">
                  <i class="fas fa-check-circle mr-1"></i> Profile updated!
                </span>
              @endif
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i> Save Changes
              </button>
            </div>
          </form>
        </div>

        {{-- Change Password Card --}}
        <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden" id="password">
          <div class="absolute top-0 left-0 w-1 h-full bg-amber-400"></div>
          <div class="flex items-center gap-4 mb-8">
            <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shadow-sm">
              <i class="fas fa-key"></i>
            </div>
            <div>
              <h3 class="text-lg font-black text-slate-800 tracking-tight">Change Password</h3>
              <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Update your security</p>
            </div>
          </div>

          <form method="post" action="{{ route('password.update') }}" id="passwordForm">
            @csrf
            @method('put')

            <div class="space-y-6">
              {{-- Current Password --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Current Password <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="current_password"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="Enter current password" autocomplete="current-password">
                </div>
                @error('current_password', 'updatePassword')
                  <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- New Password --}}
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                    New Password <span class="text-rose-500">*</span>
                  </label>
                  <div class="input-icon-group">
                    <i class="fas fa-key"></i>
                    <input type="password" name="password"
                      class="w-full py-2.5 font-semibold placeholder:font-normal"
                      placeholder="Enter new password" autocomplete="new-password">
                  </div>
                  @error('password', 'updatePassword')
                    <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                  @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                    Confirm Password <span class="text-rose-500">*</span>
                  </label>
                  <div class="input-icon-group">
                    <i class="fas fa-key"></i>
                    <input type="password" name="password_confirmation"
                      class="w-full py-2.5 font-semibold placeholder:font-normal"
                      placeholder="Confirm new password" autocomplete="new-password">
                  </div>
                  @error('password_confirmation', 'updatePassword')
                    <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end mt-6 pt-6 border-t border-slate-100">
              @if (session('status') === 'password-updated')
                <span class="text-sm text-emerald-600 font-medium mr-4">
                  <i class="fas fa-check-circle mr-1"></i> Password updated!
                </span>
              @endif
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-shield-alt mr-2"></i> Update Password
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Right Column: Info --}}
      <div class="space-y-8">

        {{-- Account Info Card --}}
        <div class="card p-6 bg-white border-slate-100 shadow-soft">
          <div class="flex items-center gap-4 mb-6 pb-4 border-b border-slate-100">
            <div class="h-16 w-16 rounded-2xl bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-2xl">
              {{ substr($user->name, 0, 1) }}
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-800">{{ $user->name }}</h3>
              <p class="text-sm text-slate-500">{{ $user->email }}</p>
            </div>
          </div>

          <div class="space-y-4">
            <div class="flex items-center justify-between text-sm">
              <span class="text-slate-500">Member since</span>
              <span class="font-semibold text-slate-700">{{ $user->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-slate-500">Email verified</span>
              @if($user->email_verified_at)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                  <i class="fas fa-check mr-1"></i> Verified
                </span>
              @else
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-100">
                  <i class="fas fa-clock mr-1"></i> Pending
                </span>
              @endif
            </div>
          </div>
        </div>

        {{-- Security Tips --}}
        <div class="bg-sky-50/50 rounded-2xl p-6 border border-sky-100">
          <h4 class="text-[11px] font-black text-sky-600 uppercase tracking-widest mb-4 flex items-center gap-2">
            <i class="fas fa-shield-alt"></i> Security Tips
          </h4>
          <ul class="text-xs text-sky-700 leading-relaxed font-medium space-y-2">
            <li class="flex items-start gap-2">
              <i class="fas fa-check text-sky-500 mt-0.5"></i>
              Use a strong, unique password
            </li>
            <li class="flex items-start gap-2">
              <i class="fas fa-check text-sky-500 mt-0.5"></i>
              Don't share your password with anyone
            </li>
            <li class="flex items-start gap-2">
              <i class="fas fa-check text-sky-500 mt-0.5"></i>
              Change your password regularly
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
@endsection
