@extends('theme.adminlte.sales.orders.wizard.layout')

@section('wizard-content')
  <div class="row justify-content-center py-5">
    <div class="col-lg-7 col-md-9">
      <div class="text-center mb-5">
        <h2 class="font-weight-bold text-dark">Create New Sales Order</h2>
        <p class="text-muted">Begin by selecting the customer for this transaction.</p>
      </div>

      <div class="card glass-panel border-0 mx-auto" style="max-width: 700px;">
        <div class="card-body p-5">

          <!-- Search Mode -->
          <div id="customer-search-mode" style="{{ isset($order) && $order->customer_id ? 'display:none;' : '' }}">
            <div class="text-center mb-4">
              <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3">
                <i class="fas fa-search fa-2x"></i>
              </div>
              <h4 class="font-weight-bold">Search Customer</h4>
            </div>

            <div class="form-group mb-4">
              <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-white border-0 pl-4"><i class="fas fa-search text-muted"></i></span>
                </div>
                <!-- Select2 -->
                <select class="form-control select2 border-0" id="customer_select" name="customer_id"
                  style="width: 100%; height: 50px;"></select>
              </div>
              <small class="text-muted text-center d-block mt-3">Try searching by Name, Email, or Phone Number</small>
            </div>
          </div>

          <!-- Selected Mode -->
          <div id="customer-selected-mode" style="{{ isset($order) && $order->customer_id ? '' : 'display:none;' }}">
            @php
              $customerName = $order->customer->display_name ?? 'Unknown';
              $email = $order->customer->email ?? 'N/A';
              $phone = $order->customer->phone ?? 'N/A';
              $initials = collect(preg_split('/\s+/', $customerName))
                  ->map(fn($s) => mb_substr($s, 0, 1))
                  ->take(2)
                  ->implode('');
            @endphp

            <div class="d-flex flex-column align-items-center mb-5">
              <div
                class="customer-avatar-large rounded-circle d-flex align-items-center justify-content-center shadow mb-3"
                id="displayed-avatar">
                {{ $initials }}
              </div>
              <h2 class="mb-1 font-weight-bold display-4" style="font-size: 2rem;" id="displayed-name">
                {{ $customerName }}</h2>
              <div class="d-flex text-muted gap-3">
                <span><i class="fas fa-envelope mr-1"></i> <span id="displayed-email">{{ $email }}</span></span>
                <span class="mx-2">|</span>
                <span><i class="fas fa-phone mr-1"></i> <span id="displayed-phone">{{ $phone }}</span></span>
              </div>
            </div>

            <!-- Hidden Inputs for Form Submission -->
            <form action="{{ route('company.sales-orders.wizard.storeStep1') }}" method="POST" id="step1-form">
              @csrf
              @if (isset($order) && $order->uuid)
                <input type="hidden" name="uuid" value="{{ $order->uuid }}">
              @endif
              <input type="hidden" name="customer_id" id="final_customer_id" value="{{ $order->customer_id ?? '' }}">

              <!-- These are populated via JS before submit or default to logic in controller -->
              <input type="hidden" name="bill_to" id="final_bill_to">
              <input type="hidden" name="ship_to" id="final_ship_to">

              <div class="text-center">
                <button type="button" class="btn btn-primary btn-lg px-5 shadow-lg rounded-pill" onclick="submitStep1()">
                  Start Product Selection <i class="fas fa-arrow-right ml-2"></i>
                </button>
                <br>
                <button type="button" class="btn btn-link text-muted btn-sm mt-3" onclick="resetCustomer()">
                  Select Different Customer
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
@endsection

@section('js')
  <script>
    const ENDPOINT_SEARCH_CUSTOMERS = "{{ company_route('sales-orders.searchCustomers') }}";

    $(document).ready(function() {
      // Init Select2
      $('#customer_select').select2({
        ajax: {
          url: ENDPOINT_SEARCH_CUSTOMERS,
          dataType: 'json',
          delay: 250,
          processResults: function(data) {
            return {
              results: data.results || data
            };
          }
        },
        placeholder: 'Search Customer...',
        theme: 'bootstrap4',
        minimumInputLength: 0,
        templateResult: formatCustomerResult
      }).on('select2:select', function(e) {
        let data = e.params.data;
        selectCustomer(data);
      });
    });

    function formatCustomerResult(c) {
      if (!c.id) return c.text;
      return $(`
            <div class="d-flex align-items-center py-1">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold; color: #555;">
                    ${c.initials || '??'}
                </div>
                <div>
                    <div class="font-weight-bold">${c.display_name}</div>
                    <div class="small text-muted">${c.email || ''}</div>
                </div>
            </div>
        `);
    }

    function selectCustomer(data) {
      // Update UI
      $('#displayed-name').text(data.display_name);
      $('#displayed-email').text(data.email || 'N/A');
      $('#displayed-phone').text(data.phone || 'N/A');
      $('#displayed-avatar').text(data.initials || '??');

      $('#final_customer_id').val(data.id);

      // Address Handling (Optimistic: Default to first Billing/Shipping)
      let bill = null;
      let ship = null;
      if (data.addresses && data.addresses.length > 0) {
        bill = data.addresses.find(a => a.type === 'BILLING') || data.addresses[0];
        ship = data.addresses.find(a => a.type === 'SHIPPING') || bill;
      }

      // If no address, construct basic one
      if (!bill) bill = {
        contact_name: data.display_name
      };
      if (!ship) ship = bill;

      $('#final_bill_to').val(JSON.stringify(bill));
      $('#final_ship_to').val(JSON.stringify(ship));

      // Switch Mode
      $('#customer-search-mode').hide();
      $('#customer-selected-mode').fadeIn();

      // Auto submit if creating new
      // submitStep1(); // Optional: User might want to verify. Let's wait for click.
    }

    function resetCustomer() {
      $('#final_customer_id').val('');
      $('#customer_select').val(null).trigger('change');
      $('#customer-selected-mode').hide();
      $('#customer-search-mode').fadeIn();
    }

    function submitStep1() {
      if (!$('#final_customer_id').val()) {
        if (window.toastr) toastr.error('Please select a customer');
        else alert('Please select a customer');
        return;
      }
      // Submit form
      $('#step1-form').submit();
    }
  </script>
@endsection
