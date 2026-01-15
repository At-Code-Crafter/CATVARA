@extends('catvara.layouts.app')

@section('title', 'User Profile')

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">User Profile</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Review user identity and manage company-wide access permissions.
        </p>
      </div>
      <div class="flex gap-3">
        <a href="{{ route('users.index') }}" class="btn btn-white min-w-[120px]">
          <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary min-w-[120px]">
          <i class="fas fa-edit mr-2"></i> Edit Profile
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- Left Column: Identity -->
      <div class="lg:col-span-4 space-y-6">
        <div class="card border-slate-100 bg-white shadow-soft overflow-hidden text-center p-8">
          <div class="relative inline-block mb-6">
            <div
              class="h-32 w-32 rounded-3xl bg-slate-50 border-2 border-slate-100 flex items-center justify-center overflow-hidden shadow-inner">
              <img
                src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : asset('theme/adminlte/dist/img/user2-160x160.jpg') }}"
                class="h-full w-full object-cover" alt="User profile">
            </div>
            @if ($user->is_active)
              <div
                class="absolute -top-2 -right-2 h-6 px-2 bg-emerald-500 text-white rounded-lg text-[10px] font-bold flex items-center shadow-lg shadow-emerald-500/30">
                ACTIVE
              </div>
            @else
              <div
                class="absolute -top-2 -right-2 h-6 px-2 bg-red-500 text-white rounded-lg text-[10px] font-bold flex items-center shadow-lg shadow-red-500/30">
                INACTIVE
              </div>
            @endif
          </div>

          <h3 class="text-xl font-bold text-slate-800 tracking-tight">{{ $user->name }}</h3>
          <p class="text-slate-400 text-sm font-medium mb-6">{{ $user->email }}</p>

          <div class="flex flex-wrap justify-center gap-2 mb-8">
            <span
              class="inline-flex items-center gap-1.5 py-1 px-3 bg-slate-100/50 border border-slate-100 rounded-lg text-[11px] font-bold text-slate-600 uppercase tracking-widest">
              <i class="fas fa-shield-alt text-brand-400"></i> {{ str_replace('_', ' ', $user->user_type) }}
            </span>
            <span
              class="inline-flex items-center gap-1.5 py-1 px-3 bg-slate-100/50 border border-slate-100 rounded-lg text-[11px] font-bold text-slate-600 uppercase tracking-widest">
              <i class="fas fa-building text-brand-400"></i> {{ $user->companies->count() }} Companies
            </span>
          </div>

          <div class="space-y-3 pt-6 border-t border-slate-50 text-left">
            <div class="flex justify-between items-center text-xs font-medium">
              <span class="text-slate-400">Last Login:</span>
              <span
                class="text-slate-800 font-bold">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d M, Y h:i A') : 'Never' }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-medium">
              <span class="text-slate-400">Member Since:</span>
              <span class="text-slate-800 font-bold">{{ $user->created_at?->format('d M, Y') }}</span>
            </div>
          </div>
        </div>

        <!-- Role Guidelines Warning -->
        <div class="p-5 bg-amber-50 border border-amber-100 rounded-2xl">
          <div class="flex gap-3">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <div class="space-y-1">
              <p class="text-xs font-bold text-amber-800 uppercase tracking-wider">Security Awareness</p>
              <p class="text-[10px] text-amber-700 font-medium leading-relaxed">
                Assigning multiple roles will grant the user combined permissions from all selected roles. Ensure the
                user's access level matches company policy.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Access Management -->
      <div class="lg:col-span-8 space-y-8">
        <!-- Provision Access Card -->
        <div class="card border-slate-100 bg-white shadow-soft overflow-hidden">
          <div class="p-6 border-b border-slate-50 bg-slate-50/20">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
              <i class="fas fa-link text-brand-400"></i> Provision Company Access
            </h3>
          </div>
          <div class="p-8">
            <form id="assignForm" action="{{ route('users.assignCompany', $user->id) }}" method="POST">
              @csrf
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Select
                    Company</label>
                  <select name="company_id" id="companySelect"
                    class="w-full rounded-xl border-slate-200 text-sm h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all"
                    required>
                    <option value="">-- Choose Company --</option>
                    @foreach ($companies as $c)
                      <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                  </select>
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Assign Roles
                    (Multiple)</label>
                  <select name="role_ids[]" id="roleSelect"
                    class="w-full rounded-xl border-slate-200 text-sm h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all"
                    multiple required>
                    <option value="">-- First Select Company --</option>
                  </select>
                </div>
              </div>

              <div
                class="flex flex-col sm:flex-row justify-between items-center bg-slate-50/50 p-6 rounded-2xl border border-slate-50 gap-6">
                <div class="flex gap-8">
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_owner" value="1" class="sr-only peer" id="isOwner">
                    <div
                      class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-500">
                    </div>
                    <span class="ml-3 text-sm font-bold text-slate-600">Company Owner</span>
                  </label>

                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" id="isActive" checked>
                    <div
                      class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-400">
                    </div>
                    <span class="ml-3 text-sm font-bold text-slate-600">Access Enabled</span>
                  </label>
                </div>

                <button class="btn btn-primary min-w-[160px]" type="submit">
                  <i class="fas fa-save mr-2"></i> Save Access
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Existing Access List -->
        <div class="card border-slate-100 bg-white shadow-soft overflow-hidden">
          <div class="p-6 border-b border-slate-50 bg-slate-50/20">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
              <i class="fas fa-list text-brand-400"></i> Active Company Permissions
            </h3>
          </div>
          <div class="p-0">
            <table class="table-premium w-full text-left">
              <thead>
                <tr>
                  <th class="px-8!">Company Name</th>
                  <th>Assigned Roles</th>
                  <th class="text-center">Flags</th>
                  <th class="text-right px-8!">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($user->companies as $c)
                  @php
                    $appliedRoles = $user->allCompanyRoles->where('pivot.company_id', $c->id);
                  @endphp
                  <tr>
                    <td class="px-8! py-5">
                      <div class="flex items-center gap-3">
                        <div
                          class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                          <i class="fas fa-building text-sm"></i>
                        </div>
                        <div class="flex flex-col">
                          <span class="font-bold text-slate-800 text-sm">{{ $c->name }}</span>
                          <span
                            class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $c->code }}</span>
                        </div>
                      </div>
                    </td>
                    <td class="py-5">
                      <div class="flex flex-wrap gap-1.5">
                        @forelse($appliedRoles as $role)
                          <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-50 text-brand-500 border border-brand-100/50 rounded-md text-[10px] font-bold">
                            <i class="fas fa-user-tag text-[8px]"></i> {{ strtoupper($role->name) }}
                          </span>
                        @empty
                          <span class="text-slate-300 text-[10px] font-medium italic">No roles assigned</span>
                        @endforelse
                      </div>
                    </td>
                    <td class="text-center py-5">
                      <div class="flex justify-center gap-2">
                        @if ($c->pivot->is_owner)
                          <span class="p-1.5 bg-blue-50 text-blue-500 rounded-lg" title="Owner"><i
                              class="fas fa-crown text-[10px]"></i></span>
                        @endif
                        @if ($c->pivot->is_active)
                          <span class="p-1.5 bg-emerald-50 text-emerald-500 rounded-lg" title="Active"><i
                              class="fas fa-check text-[10px]"></i></span>
                        @else
                          <span class="p-1.5 bg-red-50 text-red-500 rounded-lg" title="Disabled"><i
                              class="fas fa-times text-[10px]"></i></span>
                        @endif
                      </div>
                    </td>
                    <td class="text-right px-8! py-5">
                      <form method="POST" action="{{ route('users.removeCompany', $user->id) }}">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $c->id }}">
                        <button
                          class="h-9 w-9 bg-slate-50 text-red-400 hover:bg-red-50 hover:text-red-500 rounded-xl transition-all border border-slate-100 revoke-btn"
                          type="button">
                          <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-16">
                      <div class="flex flex-col items-center gap-3">
                        <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-200">
                          <i class="fas fa-building text-3xl"></i>
                        </div>
                        <p class="text-slate-400 text-sm font-medium">No company access assigned yet.</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Initialize Select2 if available
      if (typeof $.fn.select2 !== 'undefined') {
        $('#companySelect').select2({
          placeholder: "-- Choose Company --"
        });
        $('#roleSelect').select2({
          placeholder: "-- Pick Roles --"
        });
      }

      $('#companySelect').on('change', function() {
        const companyId = $(this).val();
        const $roleSelect = $('#roleSelect');

        if (!companyId) {
          $roleSelect.html('<option value="">-- Choose Roles --</option>');
          return;
        }

        $.get('{{ route('users.roles.byCompany') }}', {
            company_id: companyId
          })
          .done(function(roles) {
            let html = '';
            roles.forEach(r => html += `<option value="${r.id}">${r.name}</option>`);
            $roleSelect.html(html);
            if (typeof $.fn.select2 !== 'undefined') {
              $roleSelect.trigger('change');
            }
          })
          .fail(function() {
            // 
          });
      });

      // Handle Revoke Button manually to ensure form submits
      $(document).on('click', '.revoke-btn', function(e) {
        e.preventDefault();
        const $form = $(this).closest('form');
        if (confirm('Are you sure you want to revoke all access for this company?')) {
          $form.submit();
        }
      });
    });
  </script>
@endpush
