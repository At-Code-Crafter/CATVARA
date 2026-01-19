@extends('catvara.layouts.app')

@section('title', 'Team Member Profile - ' . $user->name)

@section('content')
  <div class="w-full pb-24 animate-fade-in">
    {{-- Top Navigation & Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('settings.users.index', ['company' => $company->uuid]) }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Member Profile
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">{{ $user->name }}</h1>
        <p class="text-slate-500 font-medium mt-1">
          Detailed view of member access and activity within {{ $company->name }}.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('settings.users.edit', [$company->uuid, $user->id]) }}" class="btn btn-white shadow-sm">
          <i class="fas fa-edit mr-2 text-slate-400"></i> Edit Profile
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      {{-- Left Column: Identity Card --}}
      <div class="lg:col-span-4 xl:col-span-3 space-y-6">
        <div class="card p-8 flex flex-col items-center bg-white shadow-soft border-slate-100">
          <div class="w-32 h-32 rounded-3xl overflow-hidden border-4 border-white shadow-2xl bg-slate-50 relative group">
            <img
              src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : asset('theme/adminlte/dist/img/user2-160x160.jpg') }}"
              class="w-full h-full object-cover">
            @if ($user->is_active)
              <div class="absolute bottom-2 right-2 w-6 h-6 bg-emerald-500 border-4 border-white rounded-full shadow-lg">
              </div>
            @endif
          </div>

          <div class="text-center mt-6">
            <h3 class="text-xl font-black text-slate-900">{{ $user->name }}</h3>
            <p class="text-slate-400 font-bold text-sm">{{ $user->email }}</p>
          </div>

          <div class="w-full mt-8 pt-8 border-t border-slate-50 space-y-4">
            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-400 font-bold uppercase tracking-widest">Status</span>
              @if ($user->is_active)
                <span class="text-emerald-500 font-black flex items-center gap-1.5">
                  <i class="fas fa-check-circle"></i> Active
                </span>
              @else
                <span class="text-rose-500 font-black flex items-center gap-1.5">
                  <i class="fas fa-times-circle"></i> Inactive
                </span>
              @endif
            </div>
            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-400 font-bold uppercase tracking-widest">Last Login</span>
              <span class="text-slate-700 font-bold">
                {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y') : 'Never' }}
              </span>
            </div>
            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-400 font-bold uppercase tracking-widest">Joined On</span>
              <span class="text-slate-700 font-bold">
                {{ $user->created_at->format('M d, Y') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      {{-- Right Column: Detailed Information --}}
      <div class="lg:col-span-8 xl:col-span-9 space-y-8">
        {{-- Roles Section --}}
        <div class="card p-8 bg-white shadow-soft border-slate-100">
          <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-3 mb-8">
            <div class="h-8 w-8 rounded-lg bg-brand-50 text-brand-500 flex items-center justify-center text-sm">
              <i class="fas fa-shield-alt"></i>
            </div>
            Assigned Roles
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse ($roles as $role)
              <div
                class="p-6 rounded-2xl bg-slate-50 border border-slate-100 shadow-sm group hover:border-brand-200 transition-all">
                <div class="flex items-center gap-4 mb-3">
                  <div
                    class="h-10 w-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-brand-500 transition-colors">
                    <i class="fas fa-user-shield"></i>
                  </div>
                  <h4 class="font-bold text-slate-800">{{ $role->name }}</h4>
                </div>
                <p class="text-xs text-slate-500 font-medium leading-relaxed">
                  {{ $role->description ?? 'No description provided for this role.' }}
                </p>
              </div>
            @empty
              <div class="md:col-span-2 p-12 text-center border-2 border-dashed border-slate-100 rounded-3xl">
                <div
                  class="h-16 w-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4 text-slate-300">
                  <i class="fas fa-user-lock text-3xl"></i>
                </div>
                <h4 class="text-lg font-bold text-slate-400">No Roles Assigned</h4>
                <p class="text-sm text-slate-300 font-medium mt-1">Assign roles to grant permissions to this member.</p>
              </div>
            @endforelse
          </div>
        </div>

        {{-- Future sections like Activity Log, Assigned Customers etc can go here --}}
        <div
          class="card p-8 bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center opacity-75">
          <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center text-slate-300 mb-4 shadow-sm">
            <i class="fas fa-clock"></i>
          </div>
          <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest">Recent Activity</h4>
          <p class="text-[11px] text-slate-400 font-bold mt-1 uppercase tracking-wider">Coming Soon</p>
        </div>
      </div>
    </div>
  </div>
@endsection
