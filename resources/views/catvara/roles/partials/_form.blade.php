@php
  $isEdit = !empty($role);
@endphp

<div class="space-y-8">
  <div class="card p-8 bg-white border-slate-100">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      {{-- Role Name --}}
      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Role Name <span
            class="text-rose-500">*</span></label>
        <div class="input-icon-group">
          <i class="fas fa-user-shield"></i>
          <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required
            class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. Sales Manager">
        </div>
        @error('name')
          <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
        @enderror
      </div>

      {{-- Role Slug --}}
      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Role Slug (Optional)</label>
        <div class="input-icon-group">
          <i class="fas fa-fingerprint"></i>
          <input type="text" name="slug" value="{{ old('slug', $role->slug ?? '') }}"
            class="w-full py-2.5 font-semibold placeholder:font-normal" placeholder="e.g. sales-manager">
        </div>
        <p class="text-[10px] text-slate-400 font-medium ml-1">Leave blank to auto-generate.</p>
        @error('slug')
          <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
        @enderror
      </div>

      {{-- Status --}}
      <div class="md:col-span-2 pt-4 border-t border-slate-50 flex items-center justify-between">
        <div>
          <h4 class="font-bold text-slate-800 text-sm">Role Status</h4>
          <p class="text-xs text-slate-400 font-medium">Deactivated roles cannot be assigned to users.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" name="is_active" value="1" class="sr-only peer"
            {{ old('is_active', $role->is_active ?? true) ? 'checked' : '' }}>
          <div
            class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500">
          </div>
        </label>
      </div>
    </div>
  </div>

  {{-- Permissions Section --}}
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-black text-slate-800 tracking-tight flex items-center gap-3">
        <div class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center text-sm">
          <i class="fas fa-lock-open"></i>
        </div>
        Module Permissions
      </h3>
      <div class="flex items-center gap-2">
        <button type="button" id="btnSelectAll" class="btn btn-white btn-sm text-xs border-slate-200">
          <i class="fas fa-check-double mr-1.5 text-emerald-500"></i> Select All
        </button>
        <button type="button" id="btnClearAll" class="btn btn-white btn-sm text-xs border-slate-200">
          <i class="fas fa-eraser mr-1.5 text-rose-500"></i> Clear All
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach ($modules as $module)
        <div class="card p-0 bg-white border-slate-100 overflow-hidden group/module">
          <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i class="fas fa-cube text-slate-400 text-xs"></i>
              <span class="text-[11px] font-black text-slate-600 uppercase tracking-widest">{{ $module->name }}</span>
            </div>
            <button type="button"
              class="moduleToggle h-6 w-6 rounded bg-white border border-slate-200 text-slate-400 hover:text-brand-500 hover:border-brand-200 transition-all flex items-center justify-center"
              data-module="{{ $module->id }}">
              <i class="fas fa-sync-alt text-[10px]"></i>
            </button>
          </div>
          <div class="p-4 space-y-3">
            @forelse($module->permissions as $perm)
              @php
                $checked = in_array($perm->id, old('permissions', $selected ?? []), true);
              @endphp
              <label
                class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer group/perm">
                <div class="relative flex items-center">
                  <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                    class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-brand-500 checked:border-brand-500 perm-checkbox perm-module-{{ $module->id }}"
                    {{ $checked ? 'checked' : '' }}>
                  <span
                    class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                    <i class="fas fa-check text-[10px]"></i>
                  </span>
                </div>
                <span
                  class="text-sm font-bold text-slate-600 group-hover/perm:text-slate-800 transition-colors">{{ $perm->name }}</span>
              </label>
            @empty
              <p class="text-[10px] text-slate-400 font-bold uppercase py-4 text-center">No Permissions</p>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Footer Actions --}}
  <div class="flex items-center justify-end gap-3 pt-8 border-t border-slate-100">
    <a href="{{ route('settings.roles.index', ['company' => $company->uuid]) }}" class="btn btn-white min-w-[120px]">
      Cancel
    </a>
    <button type="submit" class="btn btn-primary min-w-[180px] shadow-lg shadow-brand-500/30 font-black">
      <i class="fas fa-check-circle mr-2 opacity-50"></i> {{ $isEdit ? 'Update Role' : 'Create Access Role' }}
    </button>
  </div>
</div>

@push('scripts')
  <script>
    $(function() {
      $('#btnSelectAll').on('click', function() {
        $('.perm-checkbox').prop('checked', true);
      });

      $('#btnClearAll').on('click', function() {
        $('.perm-checkbox').prop('checked', false);
      });

      $('.moduleToggle').on('click', function() {
        const moduleId = $(this).data('module');
        const $items = $('.perm-module-' + moduleId);
        const anyUnchecked = $items.toArray().some(el => !el.checked);
        $items.prop('checked', anyUnchecked);
      });
    });
  </script>
@endpush
