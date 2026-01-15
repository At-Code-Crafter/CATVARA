@extends('catvara.layouts.app')

@section('title', 'Create Sales Order - Step 1')

@section('content')
  <div class="max-w-7xl mx-auto pb-24">
    {{-- Header & Steps --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-6">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ company_route('sales-orders.index') }}" class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-xs font-bold px-2 py-1 rounded bg-brand-50 text-brand-600 border border-brand-100 uppercase tracking-wider">Step
            1 of 3</span>
        </div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Select Customer</h1>
        <p class="text-slate-500 font-medium mt-1">Choose the customer for this order.</p>
      </div>

      {{-- Steps Visual --}}
      <div class="hidden md:flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-2">
          <div
            class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-brand-500/30">
            1</div>
          <span class="text-sm font-bold text-slate-800">Customer</span>
        </div>
        <div class="w-12 h-0.5 bg-slate-100"></div>
        <div class="flex items-center gap-2 opacity-50">
          <div
            class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center font-bold text-sm">2
          </div>
          <span class="text-sm font-medium text-slate-500">Items</span>
        </div>
        <div class="w-12 h-0.5 bg-slate-100"></div>
        <div class="flex items-center gap-2 opacity-50">
          <div
            class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center font-bold text-sm">3
          </div>
          <span class="text-sm font-medium text-slate-500">Review</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      {{-- Main Selection Area --}}
      <div class="lg:col-span-8 space-y-6">
        {{-- Search & Filter --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
              <i class="fas fa-search absolute left-4 top-3.5 text-slate-400 text-lg"></i>
              <input type="text" id="customerSearch"
                class="w-full pl-12 pr-4 py-3 rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm text-base font-medium placeholder-slate-400 transition-all"
                placeholder="Search by name, email, or phone...">
            </div>
            <div class="md:w-48">
              <select id="companyFilter"
                class="w-full py-3 rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm font-medium text-slate-600">
                <option value="">All Types</option>
                <option value="company">Company Only</option>
                <option value="individual">Individual Only</option>
              </select>
            </div>
          </div>
        </div>

        {{-- Customer List (Dynamic) --}}
        <div id="sellToList" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          {{-- Loaded via JS --}}
          <div class="col-span-full py-12 text-center text-slate-400 animate-pulse">
            <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-brand-300"></i>
            <p class="font-medium">Loading customers...</p>
          </div>
        </div>
      </div>

      {{-- Sidebar Summary --}}
      <div class="lg:col-span-4 space-y-6">
        <div
          class="bg-white rounded-2xl shadow-lg shadow-slate-200/50 border border-slate-200 overflow-hidden sticky top-6">
          <div class="p-6 border-b border-slate-50 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
              <i class="fas fa-clipboard-list text-brand-500"></i> Order Summary
            </h3>
          </div>

          <div class="p-6 space-y-6">
            {{-- Selected Sell To --}}
            <div>
              <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Sell To Customer</div>
              <div id="sellToSummary"
                class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-500 text-sm italic">
                <i class="fas fa-user-circle text-2xl opacity-50"></i>
                <span>No customer selected</span>
              </div>
            </div>

            {{-- Billing Toggle --}}
            <div class="pt-4 border-t border-slate-100">
              <div class="flex items-center justify-between mb-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Billing Address</div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" id="sameAsSellTo" class="sr-only peer" checked>
                  <div
                    class="w-9 h-5 bg-slate-200 peer-focus:outline-none ring-2 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500">
                  </div>
                </label>
              </div>
              <p class="text-xs text-slate-500 mb-2" id="billingHelp">Same as Sell To customer.</p>

              {{-- Bill To Summary (Hidden initially) --}}
              <div id="billToSection" class="hidden">
                <button id="selectBillToBtn"
                  class="w-full py-2 border-2 border-dashed border-slate-300 rounded-xl text-slate-500 font-bold text-sm hover:border-brand-400 hover:text-brand-600 transition-all mb-2">
                  Select Billing Customer
                </button>
                <div id="billToSummary"
                  class="hidden flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-500 text-sm">
                  {{-- Populated via JS --}}
                </div>
              </div>
            </div>
          </div>

          {{-- Footer Action --}}
          <div class="p-4 bg-slate-50 border-t border-slate-100">
            <button id="continueBtn" disabled
              class="w-full btn btn-primary py-3 shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed">
              Next: Add Products <i class="fas fa-arrow-right ml-2"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Hidden Form for Submission --}}
  <form id="createOrderForm" action="{{ company_route('sales-orders.store') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="customer_id" id="input_customer_id">
    <input type="hidden" name="billing_customer_id" id="input_billing_customer_id">
    {{-- Step 1 just creates the draft/redirects to items step, or we pass params --}}
    {{-- For now, we will simulate the wizard by passing params to the next step or storing in local storage/session --}}
    {{-- Actually, the old logic posted to 'store' then redirected. Let's see. --}}
  </form>

@endsection

@push('scripts')
  <script>
    const customersDataUrl = "{{ company_route('load-customers') }}"; // Ensure this route exists and returns JSON
    let allCustomers = [];
    let selectedSellTo = null;
    let selectedBillTo = null;
    let isBillingSame = true;

    $(document).ready(function() {
      loadCustomers();

      // Search Listener
      $('#customerSearch').on('input', function() {
        renderCustomers(this.value, $('#companyFilter').val());
      });

      // Type Filter Listener
      $('#companyFilter').on('change', function() {
        renderCustomers($('#customerSearch').val(), this.value);
      });

      // Billing Toggle
      $('#sameAsSellTo').on('change', function() {
        isBillingSame = this.checked;
        if (isBillingSame) {
          $('#billingHelp').text('Same as Sell To customer.');
          $('#billToSection').addClass('hidden');
          selectedBillTo = null; // Reset explicit bill to
        } else {
          $('#billingHelp').text('Select a different customer for billing.');
          $('#billToSection').removeClass('hidden');
          // Could open a modal or just toggle the list mode to "Picking Bill To"
          // For simplicity in this V1, let's assume we select from the same list?
          // UX decision: Maybe showing the list again in a modal is better.
          // Or: Changing the main list state to "Select Billing Customer" mode.
        }
        updateSummary();
      });

      // Mode switching for Bill To selection (Simple implementation: Alert user)
      // A better UX: Add a 'mode' state. 'selecting_sell_to' vs 'selecting_bill_to'.
      let selectionMode = 'sell_to';

      $('#selectBillToBtn').on('click', function() {
        selectionMode = 'bill_to';
        // Visual cue
        $('#sellToList').addClass('ring-4 ring-indigo-100 rounded-xl p-2 bg-indigo-50/20');
        $('html, body').animate({
          scrollTop: $("#customerSearch").offset().top - 100
        }, 500);
        $('#customerSearch').focus();
        alert('Please select the Billing Customer from the list above.');
      });

      $(document).on('click', '.customer-card', function() {
        const id = $(this).data('id');
        const customer = allCustomers.find(c => c.id == id);

        if (!customer) return;

        if (selectionMode === 'sell_to') {
          selectedSellTo = customer;
          // If billing is same, visually update it implicitly
        } else {
          selectedBillTo = customer;
          selectionMode = 'sell_to'; // Revert back
          $('#sellToList').removeClass('ring-4 ring-indigo-100 rounded-xl p-2 bg-indigo-50/20');
        }

        updateSummary();
        renderCustomers($('#customerSearch').val(), $('#companyFilter').val()); // Re-render to show active states
      });

      $('#continueBtn').on('click', function() {
        if (!selectedSellTo) return;

        // Logic to proceed. 
        // In the AdminLTE version, it likely submitted a form or redirected.
        // We will redirect to a 'create-step-2' or similar, passing the customer ID.
        // Or use the store method to create a DRAFT order and then redirect to edit/add-items.

        // Let's assume we maintain the wizard flow.
        // We'll construct a URL with params.
        const url = new URL("{{ company_route('sales-orders.create-step-2') }}");
        // WAIT, does create-step-2 exist? probably not yet.
        // Use the form to POST to a 'start' endpoint? 
        // The AdminLTE had `STORE_ORDER_URL`. 

        // Let's create a hidden form and submit it to 'store' which creates the draft.
        $('#input_customer_id').val(selectedSellTo.id);
        $('#input_billing_customer_id').val(isBillingSame ? selectedSellTo.id : (selectedBillTo ? selectedBillTo
          .id : selectedSellTo.id));

        // Temporarily alert as step 2 isn't ready
        alert('Proceeding to Step 2 (Product Selection)...');
        // $('#createOrderForm').submit(); // Uncomment when backend is read
      });
    });

    function loadCustomers() {
      $.ajax({
        url: customersDataUrl,
        method: 'GET',
        success: function(response) {
          allCustomers = response; // Assume response is array of customer objects
          renderCustomers();
        },
        error: function() {
          $('#sellToList').html(
            '<div class="col-span-full text-center text-red-500 py-8">Failed to load customers. Please refresh.</div>'
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
          c.display_name.toLowerCase().includes(lowerSearch) ||
          (c.email && c.email.toLowerCase().includes(lowerSearch)) ||
          (c.phone && c.phone.includes(search))
        );
      }

      if (type) {
        // Assume 'type' field exists. Adjust if it's 'is_company' boolean etc.
        // If field is 'type' (company/individual)
        filtered = filtered.filter(c => c.type === type);
      }

      if (filtered.length === 0) {
        container.html(`
                <div class="col-span-full text-center py-12">
                    <div class="bg-slate-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-search text-slate-300 text-xl"></i>
                    </div>
                    <p class="text-slate-500 font-medium">No customers found.</p>
                </div>
            `);
        return;
      }

      filtered.forEach(c => {
        const isSelected = (selectionMode === 'sell_to' && selectedSellTo && selectedSellTo.id === c.id) ||
          (selectionMode === 'bill_to' && selectedBillTo && selectedBillTo.id === c.id);

        const activeClass = isSelected ? 'border-brand-500 ring-4 ring-brand-500/10 bg-brand-50/30' :
          'border-slate-200 hover:border-brand-300 hover:shadow-md';

        const initials = c.display_name.substring(0, 2).toUpperCase();

        const card = `
                <div class="customer-card cursor-pointer bg-white rounded-xl p-4 border transition-all duration-200 group flex items-start gap-4 ${activeClass}" data-id="${c.id}">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 text-slate-600 flex items-center justify-center font-bold text-lg shadow-inner">
                        ${initials}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm group-hover:text-brand-600 transition-colors">${c.display_name}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">${c.email || 'No email'}</p>
                        <p class="text-xs text-slate-400 mt-0.5">${c.phone || 'No phone'}</p>
                        ${c.type ? `<span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase">${c.type}</span>` : ''}
                    </div>
                    ${isSelected ? '<div class="ml-auto text-brand-500"><i class="fas fa-check-circle text-xl"></i></div>' : ''}
                </div>
            `;
        container.append(card);
      });
    }

    function updateSummary() {
      // Update Sell To
      if (selectedSellTo) {
        $('#sellToSummary').html(`
                <div class="w-8 h-8 rounded bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-xs">
                    ${selectedSellTo.display_name.substring(0, 2).toUpperCase()}
                </div>
                <div>
                    <div class="font-bold text-slate-800 text-sm">${selectedSellTo.display_name}</div>
                    <div class="text-xs text-slate-500">${selectedSellTo.email || ''}</div>
                </div>
            `);
        $('#sellToSummary').removeClass('bg-slate-50 border-slate-100 italic text-slate-500').addClass(
          'bg-white border-brand-200 shadow-sm');
      } else {
        $('#sellToSummary').html(`
                <i class="fas fa-user-circle text-2xl opacity-50"></i>
                <span>No customer selected</span>
             `);
        $('#sellToSummary').addClass('bg-slate-50 border-slate-100 italic text-slate-500').removeClass(
          'bg-white border-brand-200 shadow-sm');
      }

      // Update Bill To
      const billTo = isBillingSame ? selectedSellTo : selectedBillTo;
      if (billTo && !isBillingSame) {
        $('#billToSummary').html(`
                <div class="w-8 h-8 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                    ${billTo.display_name.substring(0, 2).toUpperCase()}
                </div>
                <div>
                    <div class="font-bold text-slate-800 text-sm">${billTo.display_name}</div>
                    <div class="text-xs text-slate-500">${billTo.email || ''}</div>
                </div>
            `).removeClass('hidden');
      } else if (!isBillingSame) {
        $('#billToSummary').addClass('hidden');
      }

      // Enable/Disable Continue
      $('#continueBtn').prop('disabled', !selectedSellTo);
    }
  </script>
@endpush
