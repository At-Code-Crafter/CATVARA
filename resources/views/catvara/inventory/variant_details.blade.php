@extends('catvara.layouts.app')

@section('title', 'Variant Inventory')

@section('content')

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-6 animate-fade-in">
    <div class="flex items-start gap-4">
      <div
        class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-white flex items-center justify-center shadow-lg shadow-brand-400/20">
        <i class="fas fa-boxes text-xl"></i>
      </div>
      <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight flex items-center gap-3">
          {{ $variant->sku }}
        </h1>
        <p class="text-slate-500 mt-1 font-medium text-lg">{{ $variant->product->name }}</p>

        <div class="flex flex-wrap gap-2 mt-3">
          @foreach ($variant->attributeValues as $val)
            <span
              class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
              <span class="text-slate-400 mr-1 uppercase">{{ $val->attribute->name }}:</span> {{ $val->value }}
            </span>
          @endforeach
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3 mt-4 sm:mt-0">
      <a href="{{ company_route('catalog.products.edit', ['product' => $variant->product_id]) }}" class="btn btn-white">
        <i class="fas fa-arrow-left mr-2"></i> Back to Product
      </a>
      <button type="button" class="btn btn-primary btn-transfer-stock">
        <i class="fas fa-exchange-alt mr-2"></i> Transfer Stock
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

    {{-- LEFT COLUMN: Stats & Details --}}
    <div class="lg:col-span-4 space-y-6">

      {{-- Total Stock Card --}}
      @php $totalQty = (float) $balances->sum('quantity'); @endphp
      <div
        class="card p-6 bg-gradient-to-br from-slate-800 to-slate-900 text-white shadow-xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
          <i class="fas fa-cubes text-9xl transform rotate-12"></i>
        </div>
        <p class="text-sm font-bold text-slate-300 uppercase tracking-widest mb-1">Total Stock on Hand</p>
        <h2 class="text-5xl font-black tracking-tight mb-4">{{ $totalQty }}</h2>

        <div class="flex items-center gap-3">
          <span
            class="badge {{ $totalQty > 0 ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-red-500/20 text-red-300 border-red-500/30' }}">
            {{ $totalQty > 0 ? 'In Stock' : 'Out of Stock' }}
          </span>
          <span class="text-slate-400 text-xs font-medium">Across all locations</span>
        </div>
      </div>

      {{-- Variant Details Card --}}
      <div class="card bg-white border-slate-100 shadow-soft">
        <div class="p-5 border-b border-slate-50 bg-slate-50/50">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
            <i class="fas fa-info-circle mr-2 text-slate-400"></i> Variant Information
          </h3>
        </div>
        <div class="p-0">
          <table class="w-full text-sm text-left">
            <tbody class="divide-y divide-slate-50">
              @foreach ($variant->attributeValues as $val)
                <tr class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-5 py-4 font-semibold text-slate-500">{{ $val->attribute->name }}</td>
                  <td class="px-5 py-4 font-bold text-slate-700 text-right">{{ $val->value }}</td>
                </tr>
              @endforeach
              <tr class="hover:bg-slate-50/50 transition-colors bg-brand-50/30">
                <td class="px-5 py-4 font-semibold text-brand-600">Cost Price</td>
                <td class="px-5 py-4 font-bold text-brand-700 text-right font-mono text-base">
                  @php
                    $currency = $variant->prices->first()->currency->symbol ?? '$';
                  @endphp
                  {{ $currency }}{{ number_format((float) $variant->cost_price, 2) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- RIGHT COLUMN: Locations & History --}}
    <div class="lg:col-span-8 space-y-8">

      {{-- Inventory By Location --}}
      <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fas fa-warehouse text-brand-400"></i> Inventory by Location
          </h3>
          <span class="badge badge-secondary">
            {{ $locations->count() }} Locations
          </span>
        </div>
        <div class="p-0">
          <div class="overflow-x-auto">
            <table class="table-premium w-full text-left">
              <thead>
                <tr>
                  <th>Location</th>
                  <th>Type</th>
                  <th class="text-center">On Hand</th>
                  <th class="text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                @foreach ($locations as $loc)
                  @php
                    $bal = $balances->where('inventory_location_id', $loc->id)->first();
                    $qty = (float) ($bal ? $bal->quantity : 0);
                  @endphp
                  <tr class="group hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                      <div class="font-bold text-slate-700">{{ $loc->locatable->name }}</div>
                      @if ($loc->locatable->code)
                        <div class="text-xs text-slate-400 font-mono">{{ $loc->locatable->code }}</div>
                      @endif
                    </td>
                    <td class="px-6 py-4">
                      <span
                        class="text-xs font-semibold uppercase text-slate-500 tracking-wider">{{ ucfirst($loc->type) }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                      <span
                        class="inline-flex items-center justify-center px-3 py-1 rounded-lg font-mono font-bold text-sm {{ $qty > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                        {{ $qty }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <div
                        class="flex justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="btn btn-sm btn-white text-xs btn-add-stock h-8"
                          data-location-id="{{ $loc->id }}" data-location-name="{{ $loc->locatable->name }}">
                          <i class="fas fa-plus mr-1 text-brand-500"></i> Add
                        </button>
                        <button class="btn btn-sm btn-white text-xs btn-adjust-stock h-8 w-8 p-0"
                          data-location-id="{{ $loc->id }}" title="Adjust">
                          <i class="fas fa-cog text-slate-400"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Movement History --}}
      <div class="card bg-white border-slate-100 shadow-soft overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fas fa-history text-slate-400"></i> Movement History
          </h3>
        </div>
        <div class="p-0">
          <table id="movements-table" class="table-premium w-full text-left" style="width: 100%;">
            <thead>
              <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Reference</th>
                <th>Location</th>
                <th>Qty</th>
                <th>User</th>
              </tr>
            </thead>
            {{-- Body via Ajax --}}
          </table>
        </div>
      </div>

    </div>
  </div>

  {{-- ADJUST STOCK MODAL --}}
  <div class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" id="adjustStockModal">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-slate-900/75 transition-opacity" aria-hidden="true"
        onclick="closeModal('adjustStockModal')"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <form action="{{ company_route('inventory.store') }}" method="POST"
        class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
        <input type="hidden" name="product_variant_id" value="{{ $variant->id }}">

        <div class="px-6 py-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
          <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Adjust Stock</h3>
          <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors"
            onclick="closeModal('adjustStockModal')">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <div class="px-6 py-6 space-y-5">

          {{-- Location --}}
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Location <span
                class="text-red-500">*</span></label>
            <select class="w-full" name="inventory_location_id" id="modal_location_id" required>
              @foreach ($locations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->locatable->name }} ({{ ucfirst($loc->type) }})</option>
              @endforeach
            </select>
          </div>

          <div class="grid grid-cols-2 gap-5">
            {{-- Action Type --}}
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Action <span
                  class="text-red-500">*</span></label>
              <select class="w-full" name="type" id="modal_type" required>
                <option value="add">Add Stock (+)</option>
                <option value="remove">Remove Stock (-)</option>
              </select>
            </div>
            {{-- Quantity --}}
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Quantity <span
                  class="text-red-500">*</span></label>
              <input type="number" step="0.01" name="quantity" min="0.01" required placeholder="0.00"
                class="w-full font-mono font-bold text-lg">
            </div>
          </div>

          {{-- Reason --}}
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Reason / Reference <span
                class="text-red-500">*</span></label>
            <input type="text" name="reason" placeholder="e.g. Purchase Order, Stock Check..." required
              class="w-full">
          </div>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
          <button type="button" class="btn btn-white" onclick="closeModal('adjustStockModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-2"></i> Update Stock
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- TRANSFER STOCK MODAL --}}
  <div class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" id="transferStockModal">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-slate-900/75 transition-opacity" aria-hidden="true"
        onclick="closeModal('transferStockModal')"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <form action="{{ company_route('inventory.transfer') }}" method="POST"
        class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
        <input type="hidden" name="product_variant_id" value="{{ $variant->id }}">

        <div class="px-6 py-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
          <h3 class="text-lg font-bold text-slate-800">Quick Transfer</h3>
          <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors"
            onclick="closeModal('transferStockModal')">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <div class="px-6 py-6 space-y-5">

          {{-- From Location --}}
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">From Location <span
                class="text-red-500">*</span></label>
            <select class="w-full" name="from_location_id" required>
              @foreach ($locations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->locatable->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- To Location --}}
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">To Location <span
                class="text-red-500">*</span></label>
            <select class="w-full" name="to_location_id" required>
              @foreach ($locations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->locatable->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Quantity --}}
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Quantity <span
                class="text-red-500">*</span></label>
            <input type="number" step="0.01" name="quantity" min="0.01" required placeholder="0.00"
              class="w-full font-mono font-bold text-lg">
          </div>

        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
          <button type="button" class="btn btn-white" onclick="closeModal('transferStockModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-exchange-alt mr-2"></i> Execute Transfer
          </button>
        </div>
      </form>
    </div>
  </div>


@endsection

@push('scripts')
  <script>
    // Modal Helpers
    function openModal(id) {
      document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
    }

    $(function() {

      // --- DataTable ---
      if ($('#movements-table').length) {
        $('#movements-table').DataTable({
          processing: true,
          serverSide: true,
          autoWidth: false,
          searching: false,
          lengthChange: false,
          pageLength: 10,
          ajax: {
            url: "{{ company_route('inventory.movements') }}",
            data: function(d) {
              d.product_variant_id = "{{ $variant->id }}";
            }
          },
          columns: [{
              data: 'date',
              name: 'occurred_at',
              className: 'px-6 py-4 text-xs font-medium text-slate-500'
            },
            {
              data: 'reason_name',
              name: 'reason.name',
              className: 'px-6 py-4 font-semibold text-slate-700'
            },
            {
              data: 'reference',
              name: 'reference_type',
              orderable: false,
              className: 'px-6 py-4 text-xs text-slate-500'
            },
            {
              data: 'location_name',
              name: 'location.locatable.name',
              className: 'px-6 py-4'
            },
            {
              data: 'quantity',
              name: 'quantity',
              className: 'px-6 py-4 font-mono font-bold'
            },
            {
              data: 'performed_by_name',
              name: 'performer.name',
              className: 'px-6 py-4 text-xs text-slate-500'
            }
          ],
          order: [
            [0, 'desc']
          ],
          language: {
            emptyTable: "No movement history found for this variant."
          }
        });
      }

      // --- Interaction Handlers ---

      // Add Stock Click
      $('.btn-add-stock').click(function() {
        var locId = $(this).data('location-id');
        var locName = $(this).data('location-name');

        $('#modalTitle').text('Add Stock: ' + locName);
        $('#modal_location_id').val(locId).trigger('change'); // trigger select2 if active
        $('#modal_type').val('add').trigger('change');

        openModal('adjustStockModal');
      });

      // Adjust Stock Click
      $('.btn-adjust-stock').click(function() {
        var locId = $(this).data('location-id');

        $('#modalTitle').text('Adjust Stock');
        if (locId) {
          $('#modal_location_id').val(locId).trigger('change');
        }
        $('#modal_type').val('add').trigger('change'); // Default to Add, user can change

        openModal('adjustStockModal');
      });

      // Transfer Stock Click
      $('.btn-transfer-stock').click(function() {
        openModal('transferStockModal');
      });

    });
  </script>
@endpush
