@extends('catvara.layouts.app')

@section('title', 'Login Activities - ' . $user->name)

@section('content')
  <div class="space-y-8 animate-fade-in pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Login Activities</h1>
        <p class="text-slate-400 text-sm font-bold flex items-center gap-2">
          Last 10 days records for {{ $user->name }}
        </p>
      </div>
      <div>
        <a href="{{ route('settings.users.index', ['company' => $company->uuid]) }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
      </div>
    </div>

    {{-- Activities List --}}
    <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
      <div class="p-0 overflow-x-auto">
        <table class="table-premium w-full text-left">
          <thead>
            <tr>
              <th class="px-8!">Logged In At</th>
              <th>IP Address</th>
              <th>Location</th>
              <th>User Agent</th>
            </tr>
          </thead>
          <tbody>
            @forelse($activities as $activity)
              <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-8 py-4 font-bold text-slate-700">
                  {{ $activity->logged_at->format('M d, Y h:i A') }}
                  <span class="block text-[10px] text-slate-400 font-bold uppercase">{{ $activity->logged_at->diffForHumans() }}</span>
                </td>
                <td class="py-4 font-mono text-xs text-brand-600">{{ $activity->ip_address }}</td>
                <td class="py-4">
                  @if($activity->location)
                    <span class="px-2 py-1 rounded bg-slate-100 text-slate-600 text-[10px] font-bold">
                      <i class="fas fa-map-marker-alt mr-1"></i> {{ $activity->location }}
                    </span>
                  @else
                    <span class="text-slate-300 font-bold text-[10px]">UNKNOWN</span>
                  @endif
                </td>
                <td class="py-4">
                  <span class="text-[10px] text-slate-500 font-medium truncate block max-w-xs" title="{{ $activity->user_agent }}">
                    {{ Str::limit($activity->user_agent, 100) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-8 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-slate-400">
                    <i class="fas fa-history text-4xl mb-4 opacity-20"></i>
                    <p class="font-bold">No login records found for the last 10 days.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
