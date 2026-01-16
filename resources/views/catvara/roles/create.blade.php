@extends('catvara.layouts.app')

@section('title', 'Create Access Role')

@section('content')
  <div class="w-full pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('settings.roles.index', ['company' => $company->uuid]) }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Settings</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Create Access Role</h1>
        <p class="text-slate-500 font-medium mt-1">Define new permission sets for the company <span
            class="text-brand-500">{{ $company->name }}</span>.</p>
      </div>
    </div>

    <form action="{{ route('settings.roles.store', ['company' => $company->uuid]) }}" method="POST">
      @csrf
      @include('catvara.roles.partials._form')
    </form>
  </div>
@endsection
