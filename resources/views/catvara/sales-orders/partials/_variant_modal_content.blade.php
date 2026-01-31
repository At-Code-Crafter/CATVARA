<div class="p-6">
  <div class="flex items-start gap-4">
    <div
      class="flex-shrink-0 w-16 h-16 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden border border-slate-200">
      @if ($product->image_url)
        <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
      @else
        <i class="fas fa-box text-slate-300 text-2xl"></i>
      @endif
    </div>
    <div class="flex-1 min-w-0">
      <h3 class="text-base font-bold text-slate-900 leading-6">{{ $product->name }}</h3>
      <p class="text-xs text-slate-500 mt-1">{{ $product->category->name ?? 'General' }}</p>
    </div>
  </div>

  <div class="mt-6">
    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-3">Select Variant</label>
    <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2">
      @foreach ($product->variants as $v)
        @php
          $attrStr = $v->attrs ? implode(' / ', (array) $v->attrs) : 'Default';
          $price = number_format($v->price, 2);
          $variantKey = $v->uuid ?? $v->id;
        @endphp
        <label
          class="relative flex cursor-pointer rounded-lg border border-slate-200 bg-white p-3 hover:border-brand-300 hover:bg-brand-50/10 transition-all">
          <input type="radio" name="selected_variant" value="{{ $variantKey }}" class="sr-only"
            onchange="$('#modalAddBtn').prop('disabled', false)">
          <span class="flex flex-1">
            <span class="flex flex-col">
              <span class="block text-sm font-bold text-slate-900">{{ $attrStr }}</span>
              <span class="mt-1 flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium whitespace-nowrap">Stock: {{ $v->stock ?? '-' }}</span>
                @if (($v->stock ?? 0) <= 0)
                  <span
                    class="text-[9px] font-black bg-rose-50 text-rose-600 px-1.5 py-0.5 rounded uppercase tracking-widest">Out
                    of Stock</span>
                @elseif(($v->stock ?? 0) <= ($v->safety_stock ?? 0))
                  <span
                    class="text-[9px] font-black bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded uppercase tracking-widest">Almost
                    Out</span>
                @else
                  <span
                    class="text-[9px] font-black bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded uppercase tracking-widest">In
                    Stock</span>
                @endif
              </span>
            </span>
          </span>
          <span class="flex flex-col text-right">
            <span class="text-sm font-black text-slate-900">{{ $price }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $currency_code ?? 'AED' }}</span>
          </span>
        </label>
      @endforeach
    </div>
  </div>

  <div class="flex gap-2 justify-end mt-8">
    <button type="button"
      class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm font-bold shadow-sm hover:bg-slate-50 transition"
      onclick="window.hideModal()">Cancel</button>
    <button type="button" disabled id="modalAddBtn"
      class="px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-bold shadow-sm hover:bg-brand-500 disabled:opacity-50 transition"
      onclick="submitVariantSelection('{{ $product->id }}')">Add to Cart</button>
  </div>
</div>
