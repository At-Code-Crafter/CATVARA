@extends('catvara.layouts.app')

@section('title', isset($editOrder) && $editOrder ? 'Edit Order Customer - ' . $editOrder->order_number : 'Sales Order - Step 1')

@section('content')
  <style>
    @keyframes fadeInSlide {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-entry { animation: fadeInSlide 0.4s ease-out forwards; opacity: 0; }
  </style>

  <div class="w-full px-8 pb-20 animate-fade-in">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          @if(isset($editOrder) && $editOrder)
            <a href="{{ company_route('sales-orders.edit', ['sales_order' => $editOrder->uuid]) }}"
              class="h-7 w-7 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-brand-600 hover:border-brand-200 hover:shadow-sm transition-all duration-300">
              <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <span
              class="px-2 py-0.5 rounded-[4px] bg-amber-50 text-amber-700 border border-amber-100 text-[10px] font-black uppercase tracking-widest">
              Edit Customer
            </span>
          @else
            <a href="{{ company_route('sales-orders.index') }}"
              class="h-7 w-7 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-brand-600 hover:border-brand-200 hover:shadow-sm transition-all duration-300">
              <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <span
              class="px-2 py-0.5 rounded-[4px] bg-brand-50 text-brand-700 border border-brand-100 text-[10px] font-black uppercase tracking-widest">
              Step 01 / 03
            </span>
          @endif
        </div>

        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
          {{ isset($editOrder) && $editOrder ? 'Change Customer for Order #' . $editOrder->order_number : 'Initiate Order' }}
        </h1>
      </div>

      {{-- Progress --}}
      <div class="hidden md:flex items-center gap-3 bg-white px-4 py-2.5 rounded-xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-2">
          <div
            class="w-6 h-6 rounded bg-brand-600 text-white flex items-center justify-center font-black text-[10px] shadow-lg shadow-brand-500/20 ring-2 ring-brand-100">
            01
          </div>
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Customer</span>
        </div>
        <div class="w-8 h-0.5 bg-slate-100"></div>
        <div class="flex items-center gap-2 opacity-40 grayscale">
          <div class="w-6 h-6 rounded bg-slate-100 text-slate-400 flex items-center justify-center font-black text-[10px]">02</div>
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Basket</span>
        </div>
        <div class="w-8 h-0.5 bg-slate-100"></div>
        <div class="flex items-center gap-2 opacity-40 grayscale">
          <div class="w-6 h-6 rounded bg-slate-100 text-slate-400 flex items-center justify-center font-black text-[10px]">03</div>
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Finalize</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      {{-- Main Content --}}
      <div class="lg:col-span-8 space-y-4">

        <!-- Search Card -->
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden group">
          <div class="p-4">
            <div class="flex flex-col md:flex-row gap-3">
              <div class="flex-1">
                <div class="input-icon-group group/input">
                  <i class="fas fa-search text-slate-400 group-focus-within/input:text-brand-400 transition-colors duration-300"></i>
                  <input type="text" id="customerSearch"
                    class="w-full pl-9 h-[40px] rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all duration-300 placeholder:text-slate-400"
                    placeholder="Search by name, ID, email or phone...">
                </div>
              </div>
              <div class="md:w-56">
                <select id="companyFilter"
                  class="w-full h-[40px] rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10 transition-all duration-300 text-slate-600">
                  <option value="">All Entity Types</option>
                  <option value="COMPANY">Companies Only</option>
                  <option value="INDIVIDUAL">Individuals Only</option>
                </select>
              </div>
              <button type="button" id="openCreateCustomerBtn"
                class="btn bg-brand-500 hover:bg-brand-600 text-white border-0 h-[40px] px-4 shadow-sm hover:shadow-md transition-all duration-300">
                <i class="fas fa-user-plus mr-2"></i> Add New Customer
              </button>
            </div>
          </div>
        </div>

        <!-- Results Grid -->
        <div id="sellToList" class="grid grid-cols-1 md:grid-cols-2 gap-3 min-h-[300px]">
          <div class="col-span-full py-12 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-50 mb-3 animate-pulse">
              <i class="fas fa-circle-notch fa-spin text-brand-400 text-lg"></i>
            </div>
            <p class="text-slate-400 font-medium text-xs">Retrieving customer directory...</p>
          </div>
        </div>
      </div>

      {{-- Sidebar --}}
      <div class="lg:col-span-4 space-y-4">

        <!-- Transaction Header -->
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden sticky top-6">
          <div class="p-4 border-b border-slate-50 bg-slate-50/30">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <i class="fas fa-file-invoice text-slate-400"></i> Transaction Context
            </h3>
          </div>

          <div class="p-4 space-y-4">
            <!-- Bill To -->
            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                Bill To Customer (Primary)
              </label>

              <div id="billToSummary"
                class="relative overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50/50 p-3 transition-all duration-300">
                <div class="flex flex-col items-center justify-center text-center py-3 text-slate-400">
                  <i class="fas fa-user-plus text-xl mb-1.5 opacity-50"></i>
                  <span class="text-[11px] font-bold">No Customer Selected</span>
                </div>
              </div>
            </div>

            <!-- Shipping Toggle -->
            <div class="pt-4 border-t border-slate-100">
              <div class="flex items-center justify-between mb-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ship To Customer</label>
                <div class="flex items-center gap-2">
                  <span class="text-[10px] font-bold text-slate-500" id="shippingLabel">Same as Bill To</span>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="sameAsBillTo" class="sr-only peer" checked>
                    <div
                      class="w-7 h-3.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-2.5 after:w-2.5 after:transition-all after:duration-300 peer-checked:bg-slate-800">
                    </div>
                  </label>
                </div>
              </div>

              <div id="shipToSection" class="hidden mt-2">
                <button id="selectShipToBtn" type="button"
                  class="btn btn-white w-full text-[11px] py-2 h-auto border-dashed hover:border-brand-400 hover:text-brand-600 transition-all duration-300">
                  <i class="fas fa-truck mr-1.5"></i> Select Different Ship-To
                </button>

                <div id="shipToSummary"
                  class="hidden mt-2 p-2.5 bg-indigo-50/50 rounded-lg border border-indigo-100 animate-entry">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Execution Card -->
        <div class="bg-slate-900 rounded-xl shadow-xl shadow-slate-900/10 overflow-hidden text-white relative group">
          <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity duration-500">
            <i class="fas fa-rocket text-5xl transform rotate-12"></i>
          </div>
          <div class="p-5 relative z-10">
            @if(isset($editOrder) && $editOrder)
              <h3 class="text-base font-black tracking-tight mb-1">Update Customer</h3>
              <p class="text-[11px] text-slate-400 font-medium mb-4">Change the customer for order #{{ $editOrder->order_number }}.</p>
            @else
              <h3 class="text-base font-black tracking-tight mb-1">Initiate Draft</h3>
              <p class="text-[11px] text-slate-400 font-medium mb-4">Create a new order draft and proceed to items.</p>
            @endif

            <button id="continueBtn" type="button" disabled
              class="w-full btn bg-brand-500 hover:bg-brand-400 text-white border-0 py-3 h-auto shadow-lg shadow-brand-900/50 disabled:opacity-20 disabled:grayscale disabled:cursor-not-allowed transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
              <span class="font-bold flex items-center justify-center gap-2 text-sm">
                @if(isset($editOrder) && $editOrder)
                  Update & Continue <i class="fas fa-check"></i>
                @else
                  Create & Proceed <i class="fas fa-arrow-right"></i>
                @endif
              </span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Hidden Form for Submission --}}
  @if(isset($editOrder) && $editOrder)
    <form id="createOrderForm"
      action="{{ company_route('sales-orders.update-customers', ['sales_order' => $editOrder->uuid]) }}"
      method="POST" class="hidden">
      @csrf
      @method('PUT')
      <input type="hidden" name="bill_to" id="input_billing_customer_id">
      <input type="hidden" name="ship_to" id="input_shipping_customer_id">
    </form>
  @else
    <form id="createOrderForm" action="{{ company_route('sales-orders.store') }}" method="POST" class="hidden">
      @csrf
      <input type="hidden" name="bill_to" id="input_billing_customer_id">
      <input type="hidden" name="ship_to" id="input_shipping_customer_id">
    </form>
  @endif

  {{-- Sliding Customer Create Panel --}}
  <div id="customerCreateOverlay" class="fixed inset-0 bg-black/50 z-40 hidden opacity-0 transition-opacity duration-300"></div>
  <div id="customerCreatePanel" class="fixed top-0 right-0 h-full w-full max-w-lg bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-out overflow-y-auto">
    <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between z-10">
      <div>
        <h3 class="text-lg font-bold text-slate-800">Create New Customer</h3>
        <p class="text-xs text-slate-400 font-medium">Quick customer registration</p>
      </div>
      <button type="button" id="closeCreateCustomerBtn" class="h-8 w-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <form id="quickCustomerForm" class="p-6 space-y-5">
      @csrf
      {{-- Display Name --}}
      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Display Name <span class="text-rose-500">*</span></label>
        <input type="text" name="display_name" required maxlength="255"
          class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
          placeholder="e.g. John Doe or Acme Corp">
      </div>

      {{-- Type --}}
      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Account Category <span class="text-rose-500">*</span></label>
        <select name="type" id="quick_customer_type" required class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold">
          <option value="INDIVIDUAL">Individual (B2C)</option>
          <option value="COMPANY">Company (B2B)</option>
        </select>
      </div>

      {{-- Legal Name (for Company) --}}
      <div id="quick_legal_name_container" class="space-y-1.5 hidden">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Legal Registered Name</label>
        <input type="text" name="legal_name" maxlength="255"
          class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
          placeholder="Full legal entity name">
      </div>

      <div class="grid grid-cols-2 gap-4">
        {{-- Email --}}
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Email</label>
          <input type="email" name="email" maxlength="255"
            class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
            placeholder="customer@example.com">
        </div>

        {{-- Phone --}}
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Phone</label>
          <input type="text" name="phone" maxlength="50"
            class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
            placeholder="+44 000 000 0000">
        </div>
      </div>

      {{-- Tax Number --}}
      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tax / Registration #</label>
        <input type="text" name="tax_number" maxlength="100"
          class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
          placeholder="VAT, GST or Reg No">
      </div>

      {{-- Address --}}
      <div class="space-y-1.5">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Address</label>
        <textarea name="address_line_1" rows="2" maxlength="1000"
          class="w-full rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
          placeholder="Street name, building, unit number..."></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        {{-- Country --}}
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Country</label>
          <select name="country_id" id="quick_country_id" class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold">
            <option value="">Select country...</option>
            @foreach ($countries as $country)
              <option value="{{ $country->id }}" data-uuid="{{ $country->uuid }}">{{ $country->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- State --}}
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">State / Region</label>
          <select name="state_id" id="quick_state_id" class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold" disabled>
            <option value="">Select country first...</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        {{-- City --}}
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">City</label>
          <input type="text" name="city" maxlength="100"
            class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
            placeholder="e.g. London">
        </div>

        {{-- Postal Code --}}
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Postal Code</label>
          <input type="text" name="zip_code" maxlength="20"
            class="w-full h-10 rounded-lg border-slate-200 text-sm font-semibold focus:border-brand-400 focus:ring-4 focus:ring-brand-400/10"
            placeholder="e.g. SW1E 5JL">
        </div>
      </div>

      {{-- Active Status --}}
      <input type="hidden" name="is_active" value="1">

      {{-- Submit Button --}}
      <div class="pt-4 border-t border-slate-100">
        <button type="submit" id="quickCustomerSubmitBtn"
          class="w-full btn bg-brand-500 hover:bg-brand-600 text-white border-0 py-3 h-auto shadow-lg shadow-brand-500/25 transition-all duration-300">
          <span class="font-bold flex items-center justify-center gap-2">
            <i class="fas fa-user-plus"></i> Create Customer
          </span>
        </button>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    const customersDataUrl = "{{ company_route('load-customers') }}";

    // Preselect order customers in edit mode (UUIDs)
    const preBillToUuid = @json(isset($editOrder) && $editOrder ? optional($editOrder->customer)->uuid : null);
    const preShipToUuid = @json(isset($editOrder) && $editOrder ? optional($editOrder->shippingCustomer)->uuid : null);

    let allCustomers = [];
    let selectedBillTo = null;
    let selectedShipTo = null;
    let isShippingSame = true;

    // Selection mode: bill_to / ship_to
    let selectionMode = 'bill_to';

    $(document).ready(function() {
      loadCustomers();

      $('#customerSearch').on('input', function() {
        renderCustomers(this.value, $('#companyFilter').val());
      });

      $('#companyFilter').on('change', function() {
        renderCustomers($('#customerSearch').val(), this.value);
      });

      $('#sameAsBillTo').on('change', function() {
        isShippingSame = this.checked;

        if (isShippingSame) {
          $('#shippingLabel').text('Same as Bill To');
          $('#shipToSection').addClass('hidden');
          // When same, we don't need selectedShipTo (will be derived)
          selectedShipTo = null;
          selectionMode = 'bill_to';
          $('#sellToList').removeClass('ring-4 ring-indigo-100 rounded-xl p-2 bg-indigo-50/20 transition-all');
        } else {
          $('#shippingLabel').text('Custom Ship To');
          $('#shipToSection').removeClass('hidden');
        }

        updateSummary();
        renderCustomers($('#customerSearch').val(), $('#companyFilter').val());
      });

      $('#selectShipToBtn').on('click', function() {
        selectionMode = 'ship_to';

        $('#sellToList').addClass('ring-4 ring-indigo-100 rounded-xl p-2 bg-indigo-50/20 transition-all');
        $('html, body').animate({ scrollTop: $("#customerSearch").offset().top - 100 }, 500);
        $('#customerSearch').focus();

        const toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2500,
          timerProgressBar: true
        });

        toast.fire({ icon: 'info', title: 'Select the Ship-To Customer from the list' });
      });

      $(document).on('click', '.customer-card', function() {
        const uuid = $(this).data('uuid');
        const customer = allCustomers.find(c => (c.uuid + '') === (uuid + ''));

        if (!customer) return;

        if (selectionMode === 'bill_to') {
          selectedBillTo = customer;

          // If shipping same -> keep ship-to derived, else keep current ship-to selection if set
          if (isShippingSame) {
            selectedShipTo = null;
          }
        } else {
          selectedShipTo = customer;
          selectionMode = 'bill_to';
          $('#sellToList').removeClass('ring-4 ring-indigo-100 rounded-xl p-2 bg-indigo-50/20 transition-all');
        }

        updateSummary();
        renderCustomers($('#customerSearch').val(), $('#companyFilter').val());
      });

      $('#continueBtn').on('click', function() {
        if (!selectedBillTo) return;

        // bill_to always required
        $('#input_billing_customer_id').val(selectedBillTo.uuid);

        /**
         * ship_to behavior:
         * - If same as bill_to: send EMPTY so backend will default to bill_to (your controller does that)
         *   OR if you prefer: send bill_to uuid here.
         * Current backend:
         *   store(): if ship_to filled -> find else -> billToCustomer
         * So leaving ship_to empty is correct.
         */
        if (isShippingSame) {
          $('#input_shipping_customer_id').val('');
        } else {
          $('#input_shipping_customer_id').val(selectedShipTo ? selectedShipTo.uuid : '');
        }

        const isEditMode = {{ isset($editOrder) && $editOrder ? 'true' : 'false' }};
        const loadingText = isEditMode
          ? '<i class="fas fa-circle-notch fa-spin"></i> Updating...'
          : '<i class="fas fa-circle-notch fa-spin"></i> Creating Draft...';

        $(this).prop('disabled', true).html(loadingText);
        $('#createOrderForm').submit();
      });
    });

    function normalizeCustomer(row) {
      // ensures your template never breaks if API changes keys slightly
      return {
        id: row.id ?? null,
        uuid: row.uuid ?? null,
        name: row.name ?? row.display_name ?? row.legal_name ?? 'Unknown Entity',
        legal_name: row.legal_name ?? null,
        email: row.email ?? null,
        phone: row.phone ?? null,
        customerType: row.customerType ?? row.type ?? row.entity_type ?? null,
        address: row.address ?? row.full_address ?? null,
      };
    }

    function loadCustomers() {
      $.ajax({
        url: customersDataUrl,
        method: 'GET',
        success: function(response) {
          allCustomers = (response || []).map(normalizeCustomer);

          // Preselect in edit mode (after data loaded)
          if (preBillToUuid) {
            selectedBillTo = allCustomers.find(c => (c.uuid + '') === (preBillToUuid + '')) || null;
          }

          if (preShipToUuid && preBillToUuid && (preShipToUuid + '') !== (preBillToUuid + '')) {
            // different ship-to
            isShippingSame = false;
            $('#sameAsBillTo').prop('checked', false);
            $('#shippingLabel').text('Custom Ship To');
            $('#shipToSection').removeClass('hidden');

            selectedShipTo = allCustomers.find(c => (c.uuid + '') === (preShipToUuid + '')) || null;
          } else {
            // same as bill-to (or null)
            isShippingSame = true;
            $('#sameAsBillTo').prop('checked', true);
            $('#shippingLabel').text('Same as Bill To');
            $('#shipToSection').addClass('hidden');
            selectedShipTo = null;
          }

          updateSummary();
          renderCustomers();
        },
        error: function(xhr, status, error) {
          console.error('Failed to load customers:', error);
          $('#sellToList').html(
            '<div class="col-span-full text-center text-red-500 py-8 text-xs font-bold">Failed to load directory. Please refresh.</div>'
          );
        }
      });
    }

    function renderCustomers(search = '', type = '') {
      const container = $('#sellToList');
      container.empty();

      let filtered = allCustomers;

      if (search) {
        const lowerSearch = search.toLowerCase();
        filtered = filtered.filter(c =>
          (c.name && c.name.toLowerCase().includes(lowerSearch)) ||
          (c.email && c.email.toLowerCase().includes(lowerSearch)) ||
          (c.phone && (c.phone + '').includes(search)) ||
          (c.legal_name && c.legal_name.toLowerCase().includes(lowerSearch)) ||
          (c.uuid && (c.uuid + '').toLowerCase().includes(lowerSearch))
        );
      }

      if (type) {
        filtered = filtered.filter(c => (c.customerType + '') === (type + ''));
      }

      if (filtered.length === 0) {
        const isSearch = search !== '' || type !== '';
        container.html(`
          <div class="col-span-full text-center py-10 fade-in">
            <div class="bg-slate-50 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2">
              <i class="fas fa-search text-slate-300 text-lg"></i>
            </div>
            <p class="text-slate-500 font-medium text-xs">${isSearch ? 'No customers found.' : 'No customers in directory.'}</p>
          </div>
        `);
        return;
      }

      filtered.forEach((c, index) => {
        const isBillTo = selectedBillTo && (selectedBillTo.uuid + '') === (c.uuid + '');
        const isShipTo = (!isShippingSame) && selectedShipTo && (selectedShipTo.uuid + '') === (c.uuid + '');
        const isSelected = isBillTo || isShipTo;

        const displayName = c.name || c.legal_name || 'Unknown Entity';
        const initials = displayName.substring(0, 2).toUpperCase();

        const activeClass = isSelected
          ? (isBillTo
              ? 'border-brand-500 ring-2 ring-brand-500/10 bg-brand-50/30 shadow-md transform scale-[1.01]'
              : 'border-indigo-500 ring-2 ring-indigo-500/10 bg-indigo-50/20 shadow-md')
          : 'border-slate-200 hover:border-brand-300 hover:shadow-md bg-white hover:-translate-y-1';

        const typeBadge = (c.customerType === 'COMPANY')
          ? '<span class="px-1.5 py-0.5 rounded-[4px] text-[9px] font-black bg-indigo-50 text-indigo-600 uppercase tracking-wide"><i class="fas fa-building mr-1"></i> Corp</span>'
          : '<span class="px-1.5 py-0.5 rounded-[4px] text-[9px] font-black bg-amber-50 text-amber-600 uppercase tracking-wide"><i class="fas fa-user mr-1"></i> Indiv</span>';

        let checkmark = '';
        if (isBillTo) checkmark = '<div class="absolute top-3 right-3 text-brand-500 animate-entry" title="Billing"><i class="fas fa-check-circle text-lg"></i></div>';
        if (isShipTo) checkmark = '<div class="absolute top-3 right-3 text-indigo-500 animate-entry" title="Shipping"><i class="fas fa-truck text-lg"></i></div>';

        const delay = index * 0.05;
        const style = `animation-delay: ${delay}s`;
        const address = c.address || 'No Address';

        const card = `
          <div class="customer-card relative cursor-pointer rounded-xl p-4 border transition-all duration-300 group flex items-start gap-3 ${activeClass} animate-entry"
               style="${style}"
               data-uuid="${c.uuid}">
            <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-black text-sm shadow-sm border border-slate-200 group-hover:bg-white group-hover:text-brand-600 group-hover:border-brand-200 transition-colors">
              ${initials}
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 mb-1">
                <h4 class="font-bold text-slate-800 text-sm group-hover:text-brand-600 transition-colors truncate">${displayName}</h4>
                ${typeBadge}
              </div>
              <p class="text-[11px] text-slate-500 truncate font-semibold">${c.email || 'No Email'} ${c.phone ? ' • ' + c.phone : ''}</p>
              <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1.5">
                <i class="fas fa-map-marker-alt text-slate-300"></i> ${address}
              </p>
            </div>
            ${checkmark}
          </div>
        `;

        container.append(card);
      });
    }

    function updateSummary() {
      // Bill To summary
      if (selectedBillTo) {
        const displayName = selectedBillTo.name || selectedBillTo.legal_name || 'Unknown Entity';
        const address = selectedBillTo.address || 'No Address';

        $('#billToSummary').html(`
          <div class="flex items-start gap-3 animate-entry">
            <div class="w-10 h-10 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center font-black text-[10px] shadow-sm shrink-0">
              ${displayName.substring(0, 2).toUpperCase()}
            </div>
            <div class="min-w-0 flex-1">
              <div class="font-bold text-slate-800 text-xs truncate mb-0.5">${displayName}</div>
              <div class="text-[10px] text-slate-500 truncate w-full mb-1">${selectedBillTo.email || 'No Email'}</div>
              <div class="text-[10px] text-slate-400 leading-tight bg-slate-50 p-1.5 rounded border border-slate-100">
                <i class="fas fa-map-marker-alt mr-1 text-slate-300"></i> ${address}
              </div>
            </div>
          </div>
        `);

        $('#billToSummary')
          .removeClass('border-dashed bg-slate-50/50')
          .addClass('bg-white shadow-sm border-brand-100 border-solid');
      } else {
        $('#billToSummary').html(`
          <div class="flex flex-col items-center justify-center text-center py-4 text-slate-400">
            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mb-2">
              <i class="fas fa-file-invoice-dollar text-lg opacity-50"></i>
            </div>
            <span class="text-[11px] font-bold">Select Billing</span>
          </div>
        `);

        $('#billToSummary')
          .addClass('border-dashed bg-slate-50/50')
          .removeClass('bg-white shadow-sm border-brand-100 border-solid');
      }

      // Ship To summary
      const shipTo = isShippingSame ? null : selectedShipTo;

      if (shipTo) {
        const displayName = shipTo.name || shipTo.legal_name || 'Unknown Entity';

        $('#shipToSummary').html(`
          <div class="flex items-center gap-3 animate-entry">
            <div class="w-8 h-8 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-[10px]">
              ${displayName.substring(0, 2).toUpperCase()}
            </div>
            <div class="min-w-0">
              <div class="font-bold text-slate-800 text-xs truncate">${displayName}</div>
              <div class="text-[10px] text-slate-500 truncate">${shipTo.email || ''}</div>
            </div>
          </div>
        `).removeClass('hidden');
      } else {
        if ($('#shipToSection').is(':visible') && !isShippingSame && !selectedShipTo) {
          $('#shipToSummary')
            .html(`<div class="text-center text-[10px] text-indigo-400 font-bold italic py-2">Select a receiver above</div>`)
            .removeClass('hidden');
        } else {
          $('#shipToSummary').addClass('hidden');
        }
      }

      // Enable Continue only when billing selected
      $('#continueBtn').prop('disabled', !selectedBillTo);
    }

    // ========================================
    // Quick Customer Create Panel Functions
    // ========================================
    const $overlay = $('#customerCreateOverlay');
    const $panel = $('#customerCreatePanel');

    function openCustomerPanel() {
      $overlay.removeClass('hidden');
      setTimeout(() => {
        $overlay.removeClass('opacity-0').addClass('opacity-100');
        $panel.removeClass('translate-x-full');
      }, 10);
      document.body.style.overflow = 'hidden';
    }

    function closeCustomerPanel() {
      $overlay.removeClass('opacity-100').addClass('opacity-0');
      $panel.addClass('translate-x-full');
      setTimeout(() => {
        $overlay.addClass('hidden');
        document.body.style.overflow = '';
      }, 300);
    }

    // Open panel
    $('#openCreateCustomerBtn').on('click', openCustomerPanel);

    // Close panel
    $('#closeCreateCustomerBtn').on('click', closeCustomerPanel);
    $overlay.on('click', closeCustomerPanel);

    // Toggle Legal Name for Company type
    $('#quick_customer_type').on('change', function() {
      if ($(this).val() === 'COMPANY') {
        $('#quick_legal_name_container').removeClass('hidden').hide().fadeIn(200);
      } else {
        $('#quick_legal_name_container').fadeOut(150, function() {
          $(this).addClass('hidden');
        });
      }
    });

    // Country -> State cascading for quick form
    $('#quick_country_id').on('change', function() {
      const countryUuid = $(this).find(':selected').data('uuid');
      const $stateSelect = $('#quick_state_id');

      $stateSelect.prop('disabled', true).html('<option value="">Loading...</option>');

      if (countryUuid) {
        $.get(`/settings/countries/${countryUuid}/states`, function(states) {
          let options = '<option value="">Select state...</option>';
          states.forEach(state => {
            options += `<option value="${state.id}">${state.name}</option>`;
          });
          $stateSelect.html(options).prop('disabled', false);
        });
      } else {
        $stateSelect.html('<option value="">Select country first...</option>').prop('disabled', true);
      }
    });

    // Quick Customer Form Submission
    $('#quickCustomerForm').on('submit', function(e) {
      e.preventDefault();

      const $form = $(this);
      const $submitBtn = $('#quickCustomerSubmitBtn');
      const originalBtnHtml = $submitBtn.html();

      $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Creating...');

      $.ajax({
        url: "{{ company_route('customers.store') }}",
        method: 'POST',
        data: $form.serialize(),
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
          if (response.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Customer Created!',
              text: response.message || 'The customer has been added successfully.',
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              window.location.reload();
            });
          } else {
            Swal.fire('Error', response.message || 'Failed to create customer.', 'error');
            $submitBtn.prop('disabled', false).html(originalBtnHtml);
          }
        },
        error: function(xhr) {
          let errorMessage = 'An error occurred while creating the customer.';

          if (xhr.responseJSON) {
            if (xhr.responseJSON.errors) {
              const errors = Object.values(xhr.responseJSON.errors).flat();
              errorMessage = errors.join('<br>');
            } else if (xhr.responseJSON.message) {
              errorMessage = xhr.responseJSON.message;
            }
          }

          Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: errorMessage
          });

          $submitBtn.prop('disabled', false).html(originalBtnHtml);
        }
      });
    });
  </script>
@endpush
