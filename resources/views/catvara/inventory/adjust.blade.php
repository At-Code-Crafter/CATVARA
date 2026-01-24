@extends('catvara.layouts.app')

@section('title', 'Adjust Stock')

@section('content')
  <div class="w-full mx-auto pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ company_route('inventory.inventory.index') }}"
            class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">
            Inventory Management
          </span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Adjust Stock</h1>
        <p class="text-slate-500 font-medium mt-1">Manually add or remove inventory quantities for any product variant.</p>
      </div>
      <div>
        <a href="{{ company_route('inventory.inventory.index') }}" class="btn btn-white shadow-soft">
          <i class="fas fa-arrow-left mr-2 opacity-50"></i> Back
        </a>
      </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
      <div class="card p-4 mb-6 bg-rose-50 border-rose-200">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center text-rose-500 flex-shrink-0">
            <i class="fas fa-exclamation-circle"></i>
          </div>
          <div>
            <h4 class="font-bold text-rose-700 text-sm">Validation Error</h4>
            <ul class="mt-1 text-sm text-rose-600 list-disc list-inside">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    @endif

    <form action="{{ route('inventory.store', ['company' => request()->company->uuid]) }}" method="POST" class="ajax-form">
      @csrf

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Main Form --}}
        <div class="lg:col-span-2 space-y-8">

          {{-- Section 1: Adjustment Details --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-amber-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-sliders-h"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Stock Adjustment</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Select Product & Location</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              {{-- Adjustment Type --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Adjustment Type <span class="text-rose-500">*</span>
                </label>
                <select name="type" class="w-full pt-1" required>
                  <option value="add" {{ old('type') == 'add' ? 'selected' : '' }}>Add Stock</option>
                  <option value="remove" {{ old('type') == 'remove' ? 'selected' : '' }}>Remove Stock</option>
                </select>
              </div>

              {{-- Location --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Location <span class="text-rose-500">*</span>
                </label>
                <select name="inventory_location_id" id="location_select" class="w-full pt-1 select2" required>
                  @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" {{ old('inventory_location_id') == $loc->id ? 'selected' : '' }}>
                      {{ $loc->locatable->name ?? $loc->type . ' #' . $loc->id }}
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- Product Variant --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Product Variant <span class="text-rose-500">*</span>
                </label>
                <select name="product_variant_id" id="variant_select" class="w-full pt-1 select2" required>
                  <option value="">Select Product...</option>
                  @foreach ($variants as $v)
                    <option value="{{ $v->id }}" {{ old('product_variant_id') == $v->id ? 'selected' : '' }}>
                      {{ $v->sku }} | {{ $v->product->name }}
                      @if($v->attributeValues->count())
                        ({{ $v->attributeValues->pluck('value')->join(', ') }})
                      @endif
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          {{-- Section 2: Quantity & Notes --}}
          <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
            <div class="flex items-center gap-4 mb-8">
              <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-sm">
                <i class="fas fa-calculator"></i>
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Quantity & Details</h3>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Adjustment Amount</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              {{-- Quantity --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                  Quantity <span class="text-rose-500">*</span>
                </label>
                <div class="input-icon-group">
                  <i class="fas fa-cubes"></i>
                  <input type="number" step="0.000001" min="0.01" name="quantity"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="e.g. 10" value="{{ old('quantity') }}" required>
                </div>
                <p class="text-[10px] text-slate-400 ml-1">Enter the quantity to add or remove.</p>
              </div>

              {{-- Reason --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Reason</label>
                <div class="input-icon-group">
                  <i class="fas fa-tag"></i>
                  <input type="text" name="reason"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="Optional reason" value="{{ old('reason') }}">
                </div>
              </div>

              {{-- Notes --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notes</label>
                <div class="input-icon-group">
                  <i class="fas fa-sticky-note"></i>
                  <input type="text" name="notes"
                    class="w-full py-2.5 font-semibold placeholder:font-normal"
                    placeholder="Optional reference" value="{{ old('notes') }}">
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Right Column: Summary & Actions --}}
        <div class="space-y-8">

          {{-- Current Stock Info (Dynamic) --}}
          <div class="card p-6 bg-white border-slate-100 shadow-soft" id="stock-info-card">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-50 pb-4">
              Current Stock
            </h3>
            <div id="current-stock-display" class="text-center py-8">
              <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-boxes text-2xl text-slate-300"></i>
              </div>
              <p class="text-slate-400 text-sm font-medium">Select a product and location to view current stock</p>
            </div>
          </div>

          {{-- Execution Card --}}
          <div class="card p-8 bg-slate-900 border-none shadow-2xl shadow-slate-900/40 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
              <i class="fas fa-exchange-alt text-6xl text-white"></i>
            </div>
            <h3 class="text-white font-black text-lg mb-4 relative z-10">Process Adjustment</h3>
            <p class="text-slate-400 text-xs font-bold mb-8 leading-relaxed relative z-10">
              This will immediately update the inventory balance for the selected product at the specified location.
            </p>

            <div class="space-y-4">
              <button type="submit"
                class="w-full btn btn-primary py-4 shadow-xl shadow-brand-500/20 font-black tracking-tight flex items-center justify-center gap-3 bg-amber-500 hover:bg-amber-600 border-amber-500">
                <i class="fas fa-save opacity-50"></i> Process Adjustment
              </button>
              <a href="{{ company_route('inventory.inventory.index') }}"
                class="w-full flex items-center justify-center py-4 text-xs font-black text-slate-400 hover:text-white transition-colors uppercase tracking-widest">
                Cancel
              </a>
            </div>
          </div>

          {{-- Help Widget --}}
          <div class="bg-amber-50/50 rounded-2xl p-6 border border-amber-100">
            <h4 class="text-[11px] font-black text-amber-600 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-lightbulb"></i> Quick Tips
            </h4>
            <ul class="text-[11px] text-slate-600 font-bold leading-relaxed space-y-2">
              <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-amber-500 mt-0.5"></i>
                <span><strong>Add Stock:</strong> Use for receiving new inventory or corrections.</span>
              </li>
              <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-amber-500 mt-0.5"></i>
                <span><strong>Remove Stock:</strong> Use for damages, losses, or manual corrections.</span>
              </li>
              <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-amber-500 mt-0.5"></i>
                <span>All adjustments are logged in the movement history.</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Initialize Select2
      $('#location_select, #variant_select').select2({
        placeholder: 'Select an option',
        allowClear: true,
        width: '100%'
      });

      // Fetch current stock when product/location changes
      function fetchCurrentStock() {
        const variantId = $('#variant_select').val();
        const locationId = $('#location_select').val();

        if (variantId && locationId) {
          $('#current-stock-display').html(`
            <div class="flex items-center justify-center py-4">
              <i class="fas fa-spinner fa-spin text-brand-400 text-xl"></i>
            </div>
          `);

          $.get('{{ company_route('inventory.balances.data') }}', {
            product_variant_id: variantId,
            inventory_location_id: locationId
          }, function(data) {
            const balance = data.data && data.data.length > 0 ? data.data[0] : null;
            const onHand = balance ? parseFloat(balance.on_hand).toFixed(2) : '0.00';

            $('#current-stock-display').html(`
              <div class="text-center">
                <div class="text-4xl font-black text-slate-800 mb-2">${onHand}</div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Units On Hand</p>
              </div>
            `);
          }).fail(function() {
            $('#current-stock-display').html(`
              <div class="text-center py-4">
                <div class="text-2xl font-black text-slate-800 mb-2">0.00</div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">No Stock Record</p>
              </div>
            `);
          });
        }
      }

      $('#variant_select, #location_select').on('change', fetchCurrentStock);
    });
  </script>
@endpush
