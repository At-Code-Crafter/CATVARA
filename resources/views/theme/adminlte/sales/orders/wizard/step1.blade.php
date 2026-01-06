@extends('theme.adminlte.sales.orders.wizard.layout')

@section('wizard-content')
  <form action="{{ route('sales-orders.wizard.storeStep1') }}" method="POST" id="step1-form">
    @csrf
    @if (isset($order) && $order->uuid)
      <input type="hidden" name="uuid" value="{{ $order->uuid }}">
    @endif

    <div class="glass-header d-flex justify-content-between align-items-center">
      <h3 class="m-0 font-weight-bold text-dark"><i class="fas fa-user-circle mr-2 text-primary"></i> Customer Details</h3>
      <span class="badge badge-light text-muted border px-3 py-2 rounded-pill">Step 1 of 3</span>
    </div>

    <div class="p-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">

          <!-- Customer Select -->
          <div class="form-group mb-5">
            <label class="h5 font-weight-bold mb-3 text-dark">Find Customer</label>
            <select class="form-control form-control-lg" id="customer_select" name="customer_id" style="width: 100%;"
              required>
              @if (isset($order) && $order->customer)
                <option value="{{ $order->customer_id }}" selected>{{ $order->customer->display_name }}</option>
              @endif
            </select>
            <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle mr-1"></i> Search by Name, Company, Phone,
              or Email</small>
          </div>

          <!-- Addresses Section (Hidden until Customer Selected) -->
          <div id="address-section" style="display: {{ isset($order) ? 'block' : 'none' }}; animation: fadeIn 0.5s;">

            <div class="row g-4">
              <!-- Bill To -->
              <div class="col-md-6">
                <div class="card bg-light border-0 h-100 rounded-lg">
                  <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                      <div class="bg-white p-2 rounded-circle shadow-sm mr-3 text-primary">
                        <i class="fas fa-file-invoice-dollar fa-lg"></i>
                      </div>
                      <h5 class="font-weight-bold m-0">Bill To</h5>
                    </div>
                    <p class="text-muted small mb-3">Who is paying for this order?</p>

                    <select class="form-control" name="bill_to" id="bill_to_select" required>
                      <!-- Populated via JS -->
                    </select>

                    <div id="bill_to_preview" class="mt-3 p-3 bg-white rounded border-left-primary shadow-sm small">
                      <!-- Address Detail -->
                    </div>
                  </div>
                </div>
              </div>

              <!-- Ship To -->
              <div class="col-md-6">
                <div class="card bg-light border-0 h-100 rounded-lg">
                  <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                      <div class="bg-white p-2 rounded-circle shadow-sm mr-3 text-info">
                        <i class="fas fa-truck fa-lg"></i>
                      </div>
                      <h5 class="font-weight-bold m-0">Ship To / Sell To</h5>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                      <input type="checkbox" class="custom-control-input" id="same_as_bill_to" checked>
                      <label class="custom-control-label small font-weight-bold pt-1" for="same_as_bill_to">Same as
                        Billing Address</label>
                    </div>

                    <div id="ship_to_container" style="display: none;">
                      <select class="form-control" name="ship_to" id="ship_to_select">
                        <!-- Populated via JS -->
                      </select>
                    </div>

                    <div id="ship_to_preview" class="mt-3 p-3 bg-white rounded border-left-info shadow-sm small">
                      <!-- Address Detail -->
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>

    <div class="card-footer bg-white border-top p-4 d-flex justify-content-end">
      <button type="submit" class="btn btn-next shadow-lg">
        Proceed to Products <i class="fas fa-arrow-right ml-2"></i>
      </button>
    </div>
  </form>
@endsection

@section('js')
  <script>
    let customerAddresses = [];

    $(document).ready(function() {
      // Select2 Init
      $('#customer_select').select2({
        placeholder: 'Search for a client...',
        ajax: {
          url: "{{ company_route('sales-orders.searchCustomers') }}",
          dataType: 'json',
          delay: 250,
          processResults: function(data) {
            return {
              results: data.results
            };
          }
        },
        templateResult: formatCustomer,
        templateSelection: formatCustomerSelection
      });

      function formatCustomer(repo) {
        if (repo.loading) return repo.text;
        return $(`
                <div class='d-flex align-items-center py-1'>
                    <div class='bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3' style='width: 35px; height: 35px; font-weight:bold;'>${repo.initials}</div>
                    <div>
                        <div class='font-weight-bold'>${repo.display_name}</div>
                        <div class='small text-muted'>${repo.email || ''}</div>
                    </div>
                </div>
            `);
      }

      function formatCustomerSelection(repo) {
        return repo.display_name || repo.text;
      }

      // Handle Selection
      $('#customer_select').on('select2:select', function(e) {
        let data = e.params.data;
        customerAddresses = data.addresses || [];

        // If no addresses, mock one for UI (should be handled better in prod)
        if (customerAddresses.length === 0) {
          customerAddresses.push({
            id: 'default',
            type: 'BOTH',
            address_line_1: 'No address on file',
            city: '',
            state: '',
            postal_code: '',
            country: ''
          });
        }

        populateAddressDropdowns();
        $('#address-section').slideDown();
      });

      function populateAddressDropdowns() {
        let options = '';
        customerAddresses.forEach((addr, index) => {
          let label = addr.label || (addr.address_line_1 + ', ' + addr.city);
          options +=
          `<option value='${JSON.stringify(addr)}'>${label}__${index}</option>`; // appending index to uniqueify if needed, but value is object
        });

        // Re-render select options
        // We use JSON string as value for easy backend handling
        $('#bill_to_select').html(options);
        $('#ship_to_select').html(options);

        updatePreviews();
      }

      function renderAddressHtml(addr) {
        if (!addr) return '<span class="text-muted italic">No address selected</span>';
        // Parse if string
        if (typeof addr === 'string') addr = JSON.parse(addr);

        return `
                <div class="font-weight-bold text-dark">${addr.contact_name || 'Main Contact'}</div>
                <div>${addr.address_line_1 || ''}</div>
                <div>${addr.city || ''}, ${addr.state || ''} ${addr.postal_code || ''}</div>
                <div>${addr.country || ''}</div>
            `;
      }

      function updatePreviews() {
        let billVal = $('#bill_to_select').val();
        $('#bill_to_preview').html(renderAddressHtml(billVal));

        if ($('#same_as_bill_to').is(':checked')) {
          $('#ship_to_container').hide();
          $('#ship_to_preview').html(renderAddressHtml(billVal));
          // Also set the hidden input or name logic? 
          // Actually, if checked, we handle backend or just copy value on submit?
          // For now, let's keep ship_to_select synced value
          $('#ship_to_select').val(billVal);
        } else {
          $('#ship_to_container').show();
          $('#ship_to_preview').html(renderAddressHtml($('#ship_to_select').val()));
        }
      }

      $('#bill_to_select').change(updatePreviews);
      $('#ship_to_select').change(updatePreviews);

      $('#same_as_bill_to').change(function() {
        updatePreviews();
      });

      // If editing existing order, we might need to trigger population...
      @if (isset($order) && $order->customer)
        // This part is tricky without an API call to get addresses again or passing them to view
        // Ideally we pass $customerAddresses to view
      @endif
    });
  </script>
@endsection
