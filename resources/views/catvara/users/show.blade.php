@extends('catvara.layouts.app')

@section('title', 'User Profile - ' . $user->name)

@section('content')
  @php
    $photo = $user->profile_photo
        ? asset('storage/' . $user->profile_photo)
        : asset('theme/adminlte/dist/img/user2-160x160.jpg');
  @endphp

  <div class="w-full pb-24 animate-fade-in">
    {{-- Top Navigation & Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">User
            Profile</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">{{ $user->name }}</h1>
        <p class="text-slate-500 font-medium mt-1">Manage company access and security roles for this account.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-white shadow-sm">
          <i class="fas fa-edit mr-2 text-slate-400"></i> Edit Profile
        </a>
        @if (auth()->user()->id !== $user->id)
          <button
            class="btn bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 transition-colors shadow-sm">
            <i class="fas fa-ban mr-2 opacity-50"></i> Suspend Access
          </button>
        @endif
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      {{-- Left Column: Identity Card --}}
      <div class="lg:col-span-4 xl:col-span-3 space-y-6">
        <div class="card p-8 flex flex-col items-center bg-white">
          <div class="w-32 h-32 rounded-3xl overflow-hidden border-4 border-white shadow-2xl bg-slate-50 relative group">
            <img src="{{ $photo }}" class="w-full h-full object-cover">
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
              <span class="text-slate-400 font-bold uppercase tracking-widest">Type</span>
              <span
                class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-black">{{ str_replace('_', ' ', $user->user_type) }}</span>
            </div>
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
              <span
                class="text-slate-700 font-bold">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, H:i') : 'Never' }}</span>
            </div>
          </div>
        </div>

        {{-- Quick Stats Card --}}
        <div class="card p-6 bg-gradient-to-br from-slate-800 to-slate-900 text-white border-0">
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4 opacity-50">Active Access</p>
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl">
              <i class="fas fa-building text-brand-400"></i>
            </div>
            <div>
              <p class="text-2xl font-black">{{ $user->companies->count() }}</p>
              <p class="text-xs font-bold text-slate-400">Companies Joined</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Right Column: Company Access Management --}}
      <div class="lg:col-span-8 xl:col-span-9 space-y-8">
        {{-- Right Column: Company Access Management --}}
        <div class="lg:col-span-8 xl:col-span-9 space-y-8">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-3">
              <div class="h-8 w-8 rounded-lg bg-brand-50 text-brand-500 flex items-center justify-center text-sm">
                <i class="fas fa-link"></i>
              </div>
              Company Access & Roles
            </h3>
            <div
              class="px-3 py-1 bg-slate-100 rounded-lg text-slate-500 text-[10px] font-black uppercase tracking-widest">
              Available Companies: {{ $companies->count() }}
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($companies as $company)
              @php
                $access = $user->companies->where('id', $company->id)->first();
                $hasAccess = !empty($access);
                $isActive = $hasAccess ? $access->pivot->is_active : false;
                $isOwner = $hasAccess ? $access->pivot->is_owner : false;
                $assignedRoles = $user->allCompanyRoles
                    ->where('pivot.company_id', $company->id)
                    ->pluck('id')
                    ->toArray();
              @endphp
              <div
                class="card p-0 bg-white border-slate-100 overflow-hidden group/company transition-all duration-300 {{ $isActive ? '' : 'opacity-75 grayscale-[0.5]' }}"
                id="card-{{ $company->id }}">

                {{-- Card Header --}}
                <div class="p-5 border-b border-slate-50 bg-slate-50/20 flex justify-between items-start">
                  <div class="flex items-center gap-3">
                    <div
                      class="h-10 w-10 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 font-black text-[10px]">
                      {{ $company->code }}
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-800 text-sm leading-tight">{{ $company->name }}</h4>
                      <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Company ID:
                        #{{ $company->id }}</span>
                    </div>
                  </div>

                  {{-- Access Toggle --}}
                  <label class="relative inline-flex items-center cursor-pointer scale-90">
                    <input type="checkbox" class="sr-only peer company-access-toggle"
                      data-company-id="{{ $company->id }}" {{ $isActive ? 'checked' : '' }}>
                    <div
                      class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                    </div>
                  </label>
                </div>

                {{-- Roles & Ownership Layer --}}
                <div
                  class="p-5 space-y-5 transition-all duration-300 role-container {{ $isActive ? '' : 'hidden pointer-events-none opacity-50' }}">
                  <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Assign
                      Roles</label>
                    <div class="space-y-1.5 max-h-[160px] overflow-y-auto pr-2 custom-scrollbar">
                      @forelse($company->roles as $role)
                        <label
                          class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer group/role">
                          <div class="relative flex items-center">
                            <input type="checkbox" name="roles[{{ $company->id }}][]" value="{{ $role->id }}"
                              class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-slate-300 transition-all checked:bg-brand-500 checked:border-brand-500 role-checkbox"
                              {{ in_array($role->id, $assignedRoles) ? 'checked' : '' }}>
                            <span
                              class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                              <i class="fas fa-check text-[8px]"></i>
                            </span>
                          </div>
                          <span
                            class="text-xs font-bold text-slate-600 group-hover/role:text-slate-800 transition-colors">{{ $role->name }}</span>
                        </label>
                      @empty
                        <div class="py-4 text-center border-2 border-dashed border-slate-100 rounded-xl">
                          <p class="text-[10px] text-slate-300 font-black uppercase tracking-widest">No Roles Defined</p>
                        </div>
                      @endforelse
                    </div>
                  </div>

                  {{-- Ownership Toggle --}}
                  <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                    <div>
                      <h5 class="font-bold text-slate-700 text-[11px] uppercase tracking-wider">Company Owner</h5>
                      <p class="text-[9px] text-slate-400 font-medium">Grants full admin control</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer scale-75">
                      <input type="checkbox" name="is_owner[{{ $company->id }}]" value="1"
                        class="sr-only peer owner-toggle" {{ $isOwner ? 'checked' : '' }}>
                      <div
                        class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500">
                      </div>
                    </label>
                  </div>

                  {{-- Save Changes within card --}}
                  <button type="button"
                    class="w-full btn btn-primary py-2 text-xs shadow-md shadow-brand-500/20 btn-save-access"
                    data-company-id="{{ $company->id }}">
                    <i class="fas fa-save mr-2 opacity-50"></i> Update Access
                  </button>
                </div>

                {{-- Disabled Overlay Text --}}
                <div
                  class="p-5 text-center transition-all duration-300 disabled-message {{ $isActive ? 'hidden' : '' }}">
                  <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-lock text-slate-300 text-xs"></i>
                  </div>
                  <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Access Disabled</p>
                  <p class="text-[10px] text-slate-300 mt-1">Enable to assign roles</p>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  @endsection

  @push('scripts')
    <script>
      $(function() {
        // Toggle Company Access Visibility
        $('.company-access-toggle').on('change', function() {
          const companyId = $(this).data('company-id');
          const isChecked = $(this).is(':checked');
          const $card = $(`#card-${companyId}`);
          const $roles = $card.find('.role-container');
          const $msg = $card.find('.disabled-message');

          if (isChecked) {
            $card.removeClass('opacity-75 grayscale-[0.5]');
            $roles.removeClass('hidden').hide().fadeIn(300).removeClass('pointer-events-none opacity-50');
            $msg.fadeOut(200, () => $msg.addClass('hidden'));
          } else {
            $card.addClass('opacity-75 grayscale-[0.5]');
            $roles.fadeOut(200, () => $roles.addClass('hidden pointer-events-none opacity-50'));
            $msg.removeClass('hidden').hide().fadeIn(300);
          }
        });

        // Save Access via AJAX
        $('.btn-save-access').on('click', function() {
          const $btn = $(this);
          const companyId = $btn.data('company-id');
          const $card = $(`#card-${companyId}`);

          // Gather data
          const isActive = $card.find('.company-access-toggle').is(':checked');
          const isOwner = $card.find('.owner-toggle').is(':checked');
          const roleIds = $card.find('.role-checkbox:checked').map(function() {
            return $(this).val();
          }).get();

          // Visual Feedback
          const originalHtml = $btn.html();
          $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Updating...');

          $.post('{{ route('users.assignCompany', $user->id) }}', {
              _token: '{{ csrf_token() }}',
              company_id: companyId,
              role_ids: roleIds,
              is_active: isActive ? 1 : 0,
              is_owner: isOwner ? 1 : 0
            })
            .done(function(response) {
              Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
              });
            })
            .fail(function(xhr) {
              const error = xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong';
              Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: error
              });
            })
            .always(function() {
              $btn.prop('disabled', false).html(originalHtml);
            });
        });
      });
    </script>
  @endpush
