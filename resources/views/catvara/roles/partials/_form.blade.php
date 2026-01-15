@php
  $isEdit = !empty($role);
@endphp

<div class="card border-slate-100 bg-white shadow-soft overflow-hidden">
  <div class="p-6 border-b border-slate-50 bg-slate-50/20">
    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
      <i class="fas fa-user-shield text-brand-400"></i> Role Details
    </h3>
  </div>

  <div class="p-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Role Name <span
            class="text-red-400">*</span></label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i
              class="fas fa-signature text-slate-400 group-focus-within:text-brand-400 transition-colors text-[10px]"></i>
          </div>
          <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}"
            class="w-full rounded-xl border-slate-200 text-sm pl-10 h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all @error('name') border-red-400 @enderror"
            placeholder="e.g. Sales Manager">
        </div>
        @error('name')
          <p class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">System Slug</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i class="fas fa-code text-slate-400 group-focus-within:text-brand-400 transition-colors text-[10px]"></i>
          </div>
          <input type="text" name="slug" value="{{ old('slug', $role->slug ?? '') }}"
            class="w-full rounded-xl border-slate-200 text-sm pl-10 h-[44px] focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all @error('slug') border-red-400 @enderror"
            placeholder="e.g. sales-manager">
        </div>
        <p class="text-[10px] text-slate-400 font-medium ml-1 mt-1">Leave blank for auto-generation.</p>
        @error('slug')
          <p class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status</label>
        <div class="flex items-center h-[44px] px-4 bg-slate-50/50 rounded-xl border border-slate-100">
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" value="1" class="sr-only peer" id="isActiveSwitch"
              {{ old('is_active', $role->is_active ?? true) ? 'checked' : '' }}>
            <div
              class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500">
            </div>
            <span class="ml-3 text-sm font-bold text-slate-600">Active Role</span>
          </label>
        </div>
      </div>
    </div>

    <div class="pt-8 border-t border-slate-50">
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
        <div>
          <h4 class="text-sm font-bold text-slate-800 tracking-tight">System Permissions</h4>
          <p class="text-[11px] text-slate-400 font-medium mt-0.5">Define granular access for this role.</p>
        </div>
        <div class="flex gap-2">
          <button type="button" class="btn btn-white btn-xs px-3 font-bold text-slate-500" id="btnSelectAll">
            <i class="fas fa-check-double mr-1.5 opacity-50"></i> Select All
          </button>
          <button type="button" class="btn btn-white btn-xs px-3 font-bold text-slate-500" id="btnClearAll">
            <i class="fas fa-eraser mr-1.5 opacity-50"></i> Clear
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($modules as $module)
          <div class="card border-slate-100 bg-slate-50/30 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 bg-white flex justify-between items-center">
              <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-layer-group text-slate-400"></i> {{ $module->name }}
              </span>
              <button type="button"
                class="h-6 px-2 text-[10px] font-bold text-brand-400 hover:bg-brand-50 rounded-md transition-colors moduleToggle"
                data-module="{{ $module->id }}">
                TOGGLE
              </button>
            </div>
            <div class="p-4 space-y-2.5">
              @forelse($module->permissions as $perm)
                @php
                  $checked = in_array($perm->id, old('permissions', $selected ?? []), true);
                @endphp
                <label class="relative inline-flex items-center cursor-pointer group w-full">
                  <input type="checkbox" class="sr-only peer perm-checkbox perm-module-{{ $module->id }}"
                    id="perm{{ $perm->id }}" name="permissions[]" value="{{ $perm->id }}"
                    {{ $checked ? 'checked' : '' }}>
                  <div
                    class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-brand-400">
                  </div>
                  <span
                    class="ml-2.5 text-[12px] font-medium text-slate-500 group-hover:text-slate-700 transition-colors">{{ $perm->name }}</span>
                </label>
              @empty
                <p class="text-[10px] text-slate-400 font-medium italic">No permissions available.</p>
              @endforelse
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="card-footer bg-slate-50/50 border-t border-slate-100 p-6 flex items-center justify-end gap-3">
    <a href="{{ route('settings.roles.index', ['company' => $company->uuid]) }}" class="btn btn-white min-w-[120px]">
      Cancel
    </a>

    <button type="submit" class="btn btn-primary min-w-[160px]">
      <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Update Role Data' : 'Create Access Role' }}
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
