<div class="p-6">
  <div class="flex items-start justify-between gap-3 mb-6">
    <div>
      <h3 class="text-base font-black text-slate-900">Add Custom Item</h3>
      <p class="text-xs text-slate-500 mt-1">Use when item is not in catalog.</p>
    </div>
    <button type="button" class="text-slate-400 hover:text-slate-500" onclick="window.hideModal()">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="space-y-4">
    <div>
      <label class="text-[10px] font-black text-slate-600 uppercase">Item Name *</label>
      <input id="customName" type="text"
        class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold shadow-sm focus:border-brand-400 focus:ring-0">
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-[10px] font-black text-slate-600 uppercase">SKU (optional)</label>
        <input id="customSku" type="text"
          class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold shadow-sm focus:border-brand-400 focus:ring-0">
      </div>
      <div>
        <label class="text-[10px] font-black text-slate-600 uppercase">Qty *</label>
        <input id="customQty" type="number" min="1" value="1"
          class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold shadow-sm focus:border-brand-400 focus:ring-0 text-center">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-[10px] font-black text-slate-600 uppercase">Unit Price *</label>
        <input id="customPrice" type="number" min="0" step="0.01"
          class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold shadow-sm focus:border-brand-400 focus:ring-0 text-center">
      </div>
      <div>
        <label class="text-[10px] font-black text-slate-600 uppercase">Discount %</label>
        <input id="customDisc" type="number" min="0" max="100" step="0.1" value="0"
          class="mt-1 w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold shadow-sm focus:border-brand-400 focus:ring-0 text-center">
      </div>
    </div>
  </div>

  <div class="flex gap-2 justify-end mt-8">
    <button type="button" onclick="window.hideModal()"
      class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-black hover:bg-slate-50 transition">Cancel</button>
    <button type="button" onclick="addCustomItemToCart()"
      class="h-10 px-4 rounded-xl bg-brand-600 text-white text-sm font-black hover:bg-brand-700 transition flex items-center gap-2">
      <i class="fas fa-plus"></i> Add
    </button>
  </div>
</div>
