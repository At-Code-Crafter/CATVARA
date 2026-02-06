@extends('catvara.layouts.app')

@section('title', 'Change Password')

@section('content')
  <div class="flex flex-col items-center justify-center min-h-[60vh] animate-fade-in">
    <div class="w-full max-w-md">
      <div class="text-center mb-10">
        <div
          class="h-16 w-16 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-3xl mx-auto shadow-sm mb-4">
          <i class="fas fa-key"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Change Password</h1>
        <p class="text-slate-400 text-sm font-bold px-8 mt-2">
          Your password has expired or needs to be updated. Please choose a new strong password.
        </p>
      </div>

      <div class="card p-8 bg-white border-slate-100 shadow-xl overflow-hidden relative">
        {{-- Progress bar top --}}
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-400 to-brand-600"></div>

        <form action="{{ route('auth.password.update', ['company' => $company->uuid]) }}" method="POST" class="space-y-6">
          @csrf

          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Current
              Password</label>
            <div class="relative group">
              <span
                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                <i class="fas fa-lock text-sm"></i>
              </span>
              <input type="password" name="current_password" required
                class="w-full pl-11 pr-4 py-3 bg-slate-50 border-2 border-slate-50 rounded-xl focus:bg-white focus:border-brand-500/20 focus:ring-4 focus:ring-brand-500/5 transition-all outline-none text-slate-700 font-bold"
                placeholder="••••••••">
            </div>
            @error('current_password')
              <p class="mt-2 text-rose-500 text-[10px] font-black uppercase tracking-wider pl-1">{{ $message }}</p>
            @enderror
          </div>

          <hr class="border-slate-100">

          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">New
              Password</label>
            <div class="relative group">
              <span
                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                <i class="fas fa-shield-alt text-sm"></i>
              </span>
              <input type="password" name="password" required
                class="w-full pl-11 pr-4 py-3 bg-slate-50 border-2 border-slate-50 rounded-xl focus:bg-white focus:border-brand-500/20 focus:ring-4 focus:ring-brand-500/5 transition-all outline-none text-slate-700 font-bold"
                placeholder="Minimum 8 characters">
            </div>
            @error('password')
              <p class="mt-2 text-rose-500 text-[10px] font-black uppercase tracking-wider pl-1">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Confirm New
              Password</label>
            <div class="relative group">
              <span
                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                <i class="fas fa-check-circle text-sm"></i>
              </span>
              <input type="password" name="password_confirmation" required
                class="w-full pl-11 pr-4 py-3 bg-slate-50 border-2 border-slate-50 rounded-xl focus:bg-white focus:border-brand-500/20 focus:ring-4 focus:ring-brand-500/5 transition-all outline-none text-slate-700 font-bold"
                placeholder="Repeat new password">
            </div>
          </div>

          <button type="submit"
            class="w-full py-4 bg-slate-800 text-white rounded-xl font-black uppercase tracking-widest text-xs hover:bg-slate-900 transition-all hover:shadow-lg active:scale-[0.98] flex items-center justify-center gap-2">
            Update Password & Continue <i class="fas fa-arrow-right text-[10px]"></i>
          </button>

          <p class="text-[9px] text-slate-400 font-bold text-center uppercase tracking-widest">
            Must contain uppercase, lowercase, numbers & symbols
          </p>
        </form>
      </div>
    </div>
  </div>
@endsection
