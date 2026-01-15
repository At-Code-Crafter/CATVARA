<div class="flex items-center justify-end space-x-2">
  @if (!empty($showUrl))
    <a href="{{ $showUrl }}" class="p-2 text-slate-400 hover:text-accent hover:bg-slate-50 rounded-lg transition-all"
      title="View">
      <i data-lucide="eye" class="w-4 h-4"></i>
    </a>
  @endif

  @if (empty($row->deleted_at))
    @if (!empty($editUrl))
      @if (!empty($editSidebar))
        <button type="button" data-url="{{ $editUrl }}" onclick="getAside()"
          class="p-2 text-slate-400 hover:text-amber-500 hover:bg-slate-50 rounded-lg transition-all" title="Edit">
          <i data-lucide="edit-3" class="w-4 h-4"></i>
        </button>
      @else
        <a href="{{ $editUrl }}"
          class="p-2 text-slate-400 hover:text-amber-500 hover:bg-slate-50 rounded-lg transition-all" title="Edit">
          <i data-lucide="edit-3" class="w-4 h-4"></i>
        </a>
      @endif
    @endif

    @if (!empty($deleteUrl))
      <button type="button" data-url="{{ $deleteUrl }}"
        class="p-2 text-slate-400 hover:text-rose-500 hover:bg-slate-50 rounded-lg transition-all btn-delete"
        title="Delete">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    @endif
  @elseif(!empty($restoreUrl))
    <button type="button" data-url="{{ $restoreUrl }}"
      class="p-2 text-slate-400 hover:text-emerald-500 hover:bg-slate-50 rounded-lg transition-all btn-delete"
      title="Restore">
      <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
    </button>
  @endif
</div>
