@extends('theme.adminlte.sales.orders.wizard.layout')

@section('wizard-content')
  <div class="panel-card mb-4">
    <div class="panel-header">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h4 class="mb-1 font-weight-bold">Select Customer</h4>
          <div class="panel-subtitle">Choose from recent customers or search instantly to continue.</div>
        </div>
        <div class="text-right">
          <span class="badge badge-light px-3 py-2">
            Draft: {{ $order->order_number ?? 'NEW' }}
          </span>
        </div>
      </div>
    </div>

    <div class="card-body p-4">
      {{-- Search --}}
      <div class="row mb-4">
        <div class="col-lg-8">
          <div class="input-group input-group-lg search-bar">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
            </div>
            <select class="form-control" id="customer_search" style="width:100%"></select>
          </div>
          <small class="text-muted d-block mt-2">Search by Name, Email, or Phone. Select2 will fetch live results.</small>
        </div>

        <div class="col-lg-4 mt-3 mt-lg-0">
          <form id="customerSelectForm" action="{{ route('company.sales-orders.wizard.storeStep1', $order->uuid) }}" method="POST">
            @csrf
            <input type="hidden" name="customer_id" id="selected_customer_id" value="">
            <button type="submit" class="btn btn-primary btn-lg btn-block rounded-pill font-weight-bold py-3" id="btnContinue" disabled>
              Continue to Items <i class="fas fa-arrow-right ml-2"></i>
            </button>
            <div class="text-center mt-2">
              <small class="text-muted" id="selected_customer_hint">No customer selected.</small>
            </div>
          </form>
        </div>
      </div>

      {{-- Default customer cards --}}
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 font-weight-bold">Quick Select</h5>
        <small class="text-muted">Tap a card to select.</small>
      </div>

      <div class="customer-grid">
        @forelse($customers as $c)
          @php
            $initials = collect(preg_split('/\s+/', trim((string) $c->display_name)))
              ->filter()->map(fn($p) => mb_substr($p,0,1))->take(2)->implode('') ?: '??';

            // If you have a logo field, map it here. Otherwise it will fall back to initials.
            $logoUrl = $c->logo_url ?? null;

            $typeLabel = strtoupper((string) $c->type) === 'BUSINESS' ? 'BUSINESS' : 'INDIVIDUAL';
            $typeClass = $typeLabel === 'BUSINESS' ? 'badge-info' : 'badge-secondary';

            $addressLine = trim(implode(', ', array_filter([
              $c->address,
              $c->state?->name ?? null,
              $c->postal_code,
              $c->country?->name ?? null,
            ])));
          @endphp

          <div class="customer-card js-pick-customer"
               data-id="{{ $c->id }}"
               data-name="{{ e($c->display_name) }}">
            <div class="cust-top">
              <div class="cust-avatar">
                @if($logoUrl)
                  <img src="{{ $logoUrl }}" alt="Logo">
                @else
                  {{ $initials }}
                @endif
              </div>
              <div class="flex-grow-1">
                <p class="cust-name">{{ $c->display_name }}</p>
                <p class="cust-meta">
                  {{ $c->email ?: 'No email' }} · {{ $c->phone ?: 'No phone' }}
                </p>
              </div>
              <div class="cust-badges">
                <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
              </div>
            </div>

            <div class="cust-body">
              {{ $addressLine ?: 'No address on file' }}
            </div>

            <div class="cust-foot">
              <span class="text-muted">
                <i class="fas fa-file-invoice mr-1"></i>
                {{ $c->paymentTerm?->name ?? 'No terms' }}
              </span>
              <span class="cust-select-pill">
                Select <i class="fas fa-arrow-right ml-1"></i>
              </span>
            </div>
          </div>
        @empty
          <div class="text-muted p-4">No customers found.</div>
        @endforelse
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  const ENDPOINT_CUSTOMERS = "{{ company_route('sales-orders.searchCustomers') }}";

  function setSelectedCustomer(id, name) {
    $('#selected_customer_id').val(id);
    $('#btnContinue').prop('disabled', !id);
    $('#selected_customer_hint').text(id ? ('Selected: ' + (name || 'Customer')) : 'No customer selected.');
  }

  $(document).ready(function () {
    // Card click
    $(document).on('click', '.js-pick-customer', function() {
      const id = $(this).data('id');
      const name = $(this).data('name');
      setSelectedCustomer(id, name);

      // Nice UX: auto scroll to Continue button on small screens
      if (window.innerWidth < 992) {
        document.getElementById('btnContinue')?.scrollIntoView({behavior:'smooth', block:'center'});
      }
    });

    // Select2 search
    $('#customer_search').select2({
      ajax: {
        url: ENDPOINT_CUSTOMERS,
        dataType: 'json',
        delay: 250,
        data: function(params) {
          return { q: params.term || '' };
        },
        processResults: function(data) {
          return { results: data.results || [] };
        },
        cache: true
      },
      placeholder: 'Search customer (Name, Email, Phone)',
      theme: 'bootstrap4',
      minimumInputLength: 0,
      templateResult: function(c){
        if (!c.id) return c.text;
        return $(`
          <div class="d-flex align-items-center py-1">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3"
                 style="width:40px;height:40px;font-weight:800;color:#555;">
              ${c.initials || '??'}
            </div>
            <div>
              <div class="font-weight-bold">${_.escape(c.display_name || '')}</div>
              <div class="small text-muted">${_.escape(c.email || '')} ${c.phone ? (' · ' + _.escape(c.phone)) : ''}</div>
            </div>
          </div>
        `);
      },
      templateSelection: function(c){
        return c.display_name || c.text || 'Select customer';
      },
      escapeMarkup: function(m){ return m; }
    }).on('select2:select', function(e) {
      const c = e.params.data;
      setSelectedCustomer(c.id, c.display_name);
    });
  });
</script>
@endpush
