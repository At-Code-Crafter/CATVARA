@extends('catvara.layouts.app')

@section('title', 'Create Role')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Create Role</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Define a new access role for <b>{{ $company->name }}</b>.</p>
      </div>
      <div>
        <a href="{{ route('settings.roles.index', ['company' => $company->uuid]) }}" class="btn btn-white min-w-[120px]">
          <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
      </div>
    </div>

    <form action="{{ route('settings.roles.store', ['company' => $company->uuid]) }}" method="POST" id="roleForm">
      @csrf

      @include('catvara.roles.partials._form', [
          'role' => null,
          'modules' => $modules,
          'selected' => [],
      ])

    </form>
  </div>
@endsection
