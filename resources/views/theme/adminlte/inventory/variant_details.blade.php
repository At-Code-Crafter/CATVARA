@extends('theme.adminlte.layouts.app')

@section('title', 'Variant Inventory')

@section('content-header')
<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8 col-md-12">
      <div class="d-flex align-items-start">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-boxes"></i>
          </span>
        </div>

        <div>
          <h1 class="m-0">
            {{ $variant->sku }}
            <small class="text-muted ml-2" style="font-size: 1rem;">{{ $variant->product->name }}</small>
          </h1>

          <div class="d-flex flex-wrap mt-2" style="gap:8px;">
            @foreach ($variant->attributeValues as $val)
              <span class="ent-chip">
                <b>{{ strtoupper($val->attribute->name) }}:</b> {{ $val->value }}
              </span>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end mt-3 mt-lg-0" style="gap:10px;">
      <a href="{{ company_route('catalog.products.edit', ['product' => $variant->product_id]) }}"
        class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-arrow-left mr-1"></i> Back to Product
      </a>
      <button type="button" class="btn btn-primary btn-ent btn-transfer-stock">
        <i class="fas fa-exchange-alt mr-1"></i> Transfer Stock
      </button>
    </div>
  </div>
@endsection

@section('content')
  {{-- FLASH / VALIDATION --}}
  <div class="row">
    <div class="col-12">
      @if (session('success'))
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h5><i class="icon fas fa-check"></i> Success!</h5>
          {{ session('success') }}
        </div>
      @endif

      @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h5><i class="icon fas fa-ban"></i> Error!</h5>
          {{ session('error') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h5><i class="icon fas fa-exclamation-triangle"></i> Validation Error!</h5>
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </div>

  <div class="row">
    {{-- LEFT COLUMN --}}
    <div class="col-lg-3 col-md-12">

      {{-- TOTAL STOCK (Enterprise stat style) --}}
      @php $totalQty = (float) $balances->sum('quantity'); @endphp
      <div class="ent-stat mb-3">
        <div class="ent-stat-body">
          <div>
            <p class="ent-stat-title mb-0">Total Stock on Hand</p>
            <p class="ent-stat-value mb-0">{{ $totalQty }}</p>
          </div>
          <div class="ent-stat-icon">
            <i class="fas fa-cubes"></i>
          </div>
        </div>
        <div class="ent-stat-foot">
          <span>{{ $totalQty > 0 ? 'In stock across locations' : 'No stock available' }}</span>
          <span class="ent-stat-badge">{{ $totalQty > 0 ? 'OK' : 'LOW' }}</span>
        </div>
      </div>

      {{-- DETAILS --}}
      <div class="card ent-card mb-3">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-info-circle"></i> Variant Details</h3>
        </div>

        <div class="card-body p-0">
          <table class="table table-enterprise mb-0">
            <tbody>
              @foreach ($variant->attributeValues as $val)
                <tr>
                  <td class="font-weight-bold">{{ $val->attribute->name }}</td>
                  <td class="text-right">{{ $val->value }}</td>
                </tr>
              @endforeach

              <tr>
                <td class="font-weight-bold">Cost Price</td>
                <td class="text-right">
                  @php
                    $currency = $variant->prices->first()->currency->symbol ?? '$';
                  @endphp
                  {{ $currency }}{{ number_format((float) $variant->cost_price, 2) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card-footer">
          <div class="help-hint mb-0">
            Stock actions below will log movements into the history table.
          </div>
        </div>
      </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-9 col-md-12">

      {{-- LOCATION BALANCES --}}
      <div class="card ent-card mb-3">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-warehouse"></i> Inventory by Location</h3>
            <span class="ent-chip">
              <i class="fas fa-map-marker-alt"></i> {{ $locations->count() }} Locations
            </span>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-enterprise mb-0">
              <thead>
                <tr>
                  <th>Location</th>
                  <th>Type</th>
                  <th class="text-center">On Hand</th>
                  <th class="text-right" style="width: 210px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($locations as $loc)
                  @php
                    $bal = $balances->where('inventory_location_id', $loc->id)->first();
                    $qty = (float) ($bal ? $bal->quantity : 0);
                  @endphp
                  <tr>
                    <td class="align-middle">
                      <div class="font-weight-bold">{{ $loc->locatable->name }}</div>
                      @if ($loc->locatable->code)
                        <div class="text-muted small">{{ $loc->locatable->code }}</div>
                      @endif
                    </td>

                    <td class="align-middle">
                      <span class="ent-stat-badge">{{ ucfirst($loc->type) }}</span>
                    </td>

                    <td class="text-center align-middle">
                      <span class="badge {{ $qty > 0 ? 'badge-success' : 'badge-light border' }}"
                        style="font-size: 1.05rem; padding: 0.55em 0.85em;">
                        {{ $qty }}
                      </span>
                    </td>

                    <td class="text-right align-middle">
                      <div class="d-inline-flex" style="gap:8px;">
                        <button class="btn btn-sm btn-primary btn-ent btn-add-stock"
                          data-location-id="{{ $loc->id }}" data-location-name="{{ $loc->locatable->name }}"
                          data-toggle="tooltip" title="Add stock into this location">
                          <i class="fas fa-plus mr-1"></i> Add
                        </button>

                        <button class="btn btn-sm btn-outline-secondary btn-ent btn-adjust-stock"
                          data-location-id="{{ $loc->id }}" data-toggle="tooltip" title="Adjust / remove stock">
                          <i class="fas fa-cog"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer">
          <div class="help-hint mb-0">
            Use <b>Add</b> for inbound updates, and <b>Adjust</b> to remove or correct quantities.
          </div>
        </div>
      </div>

      {{-- MOVEMENT HISTORY --}}
      <div class="card ent-card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-history"></i> Movement History</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <table id="movements-table" class="table table-enterprise" style="width: 100%;">
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
          </table>
        </div>
      </div>

    </div>
  </div>

  {{-- ADJUSTMENT MODAL --}}
  <div class="modal fade ent-modal" id="adjustStockModal">
    <div class="modal-dialog">
      <form action="{{ company_route('inventory.store') }}" method="POST" class="modal-content ent-modal-content">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
        <input type="hidden" name="product_variant_id" value="{{ $variant->id }}">

        <div class="modal-header">
          <h4 class="modal-title" id="modalTitle">Adjust Stock</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>Location <span class="req">*</span></label>
            <select class="form-control ent-control" name="inventory_location_id" id="modal_location_id" required>
              @foreach ($locations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->locatable->name }} ({{ ucfirst($loc->type) }})</option>
              @endforeach
            </select>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Action <span class="req">*</span></label>
                <select class="form-control ent-control" name="type" id="modal_type" required>
                  <option value="add">Add Stock (+)</option>
                  <option value="remove">Remove Stock (-)</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Quantity <span class="req">*</span></label>
                <input type="number" step="0.01" class="form-control ent-control" name="quantity" min="0.01"
                  required placeholder="0.00">
              </div>
            </div>
          </div>

          <div class="form-group mb-0">
            <label>Reason / Reference <span class="req">*</span></label>
            <input type="text" class="form-control ent-control" name="reason"
              placeholder="e.g. Purchase Order, Stock count, Broken..." required>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary btn-ent" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-ent" id="btnSubmitAdjust">
            <i class="fas fa-save mr-1"></i> Update Stock
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- TRANSFER MODAL --}}
  <div class="modal fade ent-modal" id="transferStockModal">
    <div class="modal-dialog">
      <form action="{{ company_route('inventory.transfer') }}" method="POST" class="modal-content ent-modal-content">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
        <input type="hidden" name="product_variant_id" value="{{ $variant->id }}">

        <div class="modal-header">
          <h4 class="modal-title">Quick Transfer</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>From Location <span class="req">*</span></label>
            <select class="form-control ent-control" name="from_location_id" required>
              @foreach ($locations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->locatable->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>To Location <span class="req">*</span></label>
            <select class="form-control ent-control" name="to_location_id" required>
              @foreach ($locations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->locatable->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group mb-0">
            <label>Quantity <span class="req">*</span></label>
            <input type="number" step="0.01" class="form-control ent-control" name="quantity" min="0.01"
              required>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary btn-ent" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-ent">
            <i class="fas fa-exchange-alt mr-1"></i> Execute Transfer
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {

      // Tooltips
      if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
      }

      // DataTable
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
              name: 'occurred_at'
            },
            {
              data: 'reason_name',
              name: 'reason.name'
            },
            {
              data: 'reference',
              name: 'reference_type',
              orderable: false
            },
            {
              data: 'location_name',
              name: 'location.locatable.name'
            },
            {
              data: 'quantity',
              name: 'quantity'
            },
            {
              data: 'performed_by_name',
              name: 'performer.name'
            }
          ],
          order: [
            [0, 'desc']
          ]
        });
      }

      // Add Stock (prefill)
      $('.btn-add-stock').click(function() {
        var locId = $(this).data('location-id');
        var locName = $(this).data('location-name');

        $('#modalTitle').text('Add Stock: ' + locName);
        $('#modal_location_id').val(locId);
        $('#modal_type').val('add');

        $('#adjustStockModal').modal('show');
      });

      // Adjust (prefill location)
      $('.btn-adjust-stock').click(function() {
        var locId = $(this).data('location-id');

        $('#modalTitle').text('Adjust Stock');
        if (locId) $('#modal_location_id').val(locId);
        $('#modal_type').val('add');

        $('#adjustStockModal').modal('show');
      });

      // Transfer
      $('.btn-transfer-stock').click(function() {
        $('#transferStockModal').modal('show');
      });
    });
  </script>
@endpush
