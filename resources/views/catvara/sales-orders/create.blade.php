@extends('catvara.layouts.app')

@section('title', 'Sales Order - Step 1')

@section('content')
  <style>
    @keyframes fadeInSlide {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-entry {
      animation: fadeInSlide 0.4s ease-out forwards;
      opacity: 0;
      /* Start hidden for animation */
    }
  </style>

  <div class="w-full px-8 pb-20 animate-fade-in">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <a href="{{ company_route('sales-orders.index') }}"
            class="h-7 w-7 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-brand-600 hover:border-brand-200 hover:shadow-sm transition-all duration-300">
            <i class="fas fa-arrow-left text-xs"></i>
          </a>
          <span
            class="px-2 py-0.5 rounded-[4px] bg-brand-50 text-brand-700 border border-brand-100 text-[10px] font-black uppercase tracking-widest">
            Step 01 / 03
          </span>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Initiate Order</h1>
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
          <div
            class="w-6 h-6 rounded bg-slate-100 text-slate-400 flex items-center justify-center font-black text-[10px]">02
          </div>
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Basket</span>
        </div>
        <div class="w-8 h-0.5 bg-slate-100"></div>
        <div class="flex items-center gap-2 opacity-40 grayscale">
          <div
            class="w-6 h-6 rounded bg-slate-100 text-slate-400 flex items-center justify-center font-black text-[10px]">03
          </div>
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">Finalize</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      {{-- Main Content: Selection --}}
      <div class="lg:col-span-8 space-y-4">

        <!-- Search Card -->
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden group">
          <div class="p-4">
            <div class="flex flex-col md:flex-row gap-3">
              <div class="flex-1">
                <div class="input-icon-group group/input">
                  <i
                    class="fas fa-search text-slate-400 group-focus-within/input:text-brand-400 transition-colors duration-300"></i>
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
            </div>
          </div>
        </div>

        <!-- Results Grid -->
        <div id="sellToList" class="grid grid-cols-1 md:grid-cols-2 gap-3 min-h-[300px]">
          {{-- Loaded via JS --}}
          <div class="col-span-full py-12 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-50 mb-3 animate-pulse">
              <i class="fas fa-circle-notch fa-spin text-brand-400 text-lg"></i>
            </div>
            <p class="text-slate-400 font-medium text-xs">Retrieving customer directory...</p>
          </div>
        </div>
      </div>

      {{-- Sidebar: Context & Actions --}}
      <div class="lg:col-span-4 space-y-4">

        <!-- Transaction Header -->
        <div class="card bg-white border-slate-100 shadow-soft overflow-hidden sticky top-6">
          <div class="p-4 border-b border-slate-50 bg-slate-50/30">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <i class="fas fa-file-invoice text-slate-400"></i> Transaction Context
            </h3>
          </div>

          <div class="p-4 space-y-4">
            <!-- Billing Customer (Primary) -->
            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Bill To Customer
                (Primary)</label>

              <div id="billToSummary"
                class="relative overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50/50 p-3 transition-all duration-300">
                <div class="flex flex-col items-center justify-center text-center py-3 text-slate-400">
                  <i class="fas fa-user-plus text-xl mb-1.5 opacity-50"></i>
                  <span class="text-[11px] font-bold">No Customer Selected</span>
                </div>
              </div>
            </div>

            <!-- Shipping Toggle (Optional) -->
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
                <button id="selectShipToBtn"
                  class="btn btn-white w-full text-[11px] py-2 h-auto border-dashed hover:border-brand-400 hover:text-brand-600 transition-all duration-300">
                  <i class="fas fa-truck mr-1.5"></i> Select Different Ship-To
                </button>

                <div id="shipToSummary"
                  class="hidden mt-2 p-2.5 bg-indigo-50/50 rounded-lg border border-indigo-100 animate-entry">
                  {{-- JS Populated --}}
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
            <h3 class="text-base font-black tracking-tight mb-1">Initiate Draft</h3>
            <p class="text-[11px] text-slate-400 font-medium mb-4">Create a new order draft and proceed to items.</p>

            <button id="continueBtn" disabled
              class="w-full btn bg-brand-500 hover:bg-brand-400 text-white border-0 py-3 h-auto shadow-lg shadow-brand-900/50 disabled:opacity-20 disabled:grayscale disabled:cursor-not-allowed transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
              <span class="font-bold flex items-center justify-center gap-2 text-sm">
                Create & Proceed <i class="fas fa-arrow-right"></i>
              </span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Hidden Form for Submission --}}
  <form id="createOrderForm" action="{{ company_route('sales-orders.store') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="bill_to" id="input_billing_customer_id">
    <input type="hidden" name="ship_to" id="input_shipping_customer_id">
  </form>
@endsection

@push('scripts')
  <script>
    const customersDataUrl = "{{ company_route('load-customers') }}";
    let allCustomers = [];
    let selectedBillTo = null;
    let selectedShipTo = null;
    let isShippingSame = true;

    $(document).ready(function() {
      // Force initial load check
      loadCustomers();

      // Search Listener
      $('#customerSearch').on('input', function() {
        renderCustomers(this.value, $('#companyFilter').val());
      });

      // Type Filter Listener
      $('#companyFilter').on('change', function() {
        renderCustomers($('#customerSearch').val(), this.value);
      });

      // Shipping Toggle
      $('#sameAsBillTo').on('change', function() {
        isShippingSame = this.checked;
        if (isShippingSame) {
          $('#shippingLabel').text('Same as Bill To');
          $('#shipToSection').addClass('hidden');
          selectedShipTo = null;
        } else {
          $('#shippingLabel').text('Custom Ship To');
          $('#shipToSection').removeClass('hidden');
        }
        updateSummary();
      });

      // Selection mode
      let selectionMode = 'bill_to';

      $('#selectShipToBtn').on('click', function() {
        selectionMode = 'ship_to';
        // Visual cue
        $('#sellToList').addClass('ring-4 ring-indigo-100 rounded-xl p-2 bg-indigo-50/20 transition-all');
        $('html, body').animate({
          scrollTop: $("#customerSearch").offset().top - 100
        }, 500);
        $('#customerSearch').focus();

        const toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
        toast.fire({
          icon: 'info',
          title: 'Select the Ship-To Customer from the list'
        });
      });

      $(document).on('click', '.customer-card', function() {
        const id = $(this).data('id');
        const customer = allCustomers.find(c => c.id == id);

        if (!customer) return;

        if (selectionMode === 'bill_to') {
          selectedBillTo = customer;
        } else {
          selectedShipTo = customer;
          selectionMode = 'bill_to'; // Revert back
          $('#sellToList').removeClass('ring-4 ring-indigo-100 rounded-xl p-2 bg-indigo-50/20 transition-all');
        }

        updateSummary();
        renderCustomers($('#customerSearch').val(), $('#companyFilter').val()); // Re-render to show active states
      });

      $('#continueBtn').on('click', function() {
        if (!selectedBillTo) return;

        $('#input_billing_customer_id').val(selectedBillTo.uuid);
        $('#input_shipping_customer_id').val(isShippingSame ? null : (selectedShipTo ? selectedShipTo.uuid :
          null));

        $(this).prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Creating Draft...');
        $('#createOrderForm').submit();
      });
    });

    function loadCustomers() {
      $.ajax({
        url: customersDataUrl,
        method: 'GET',
        success: function(response) {
          console.log('Customers loaded:', response.length); // Debug
          allCustomers = response;
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
          (c.phone && c.phone.includes(search)) ||
          (c.legal_name && c.legal_name.toLowerCase().includes(lowerSearch))
        );
      }

      if (type) {
        filtered = filtered.filter(c => c.customerType === type);
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
        const isBillTo = selectedBillTo && selectedBillTo.id === c.id;
        const isShipTo = selectedShipTo && selectedShipTo.id === c.id;
        const isSelected = isBillTo || isShipTo;

        // Use 'name' from API
        const displayName = c.name || c.legal_name || 'Unknown Entity';

        const activeClass = isSelected ?
          (isBillTo ? 'border-brand-500 ring-2 ring-brand-500/10 bg-brand-50/30 shadow-md transform scale-[1.01]' :
            'border-indigo-500 ring-2 ring-indigo-500/10 bg-indigo-50/20 shadow-md') :
          'border-slate-200 hover:border-brand-300 hover:shadow-md bg-white hover:-translate-y-1';

        const initials = displayName.substring(0, 2).toUpperCase();

        // Use 'customerType' from API
        const typeBadge = c.customerType === 'COMPANY' ?
          '<span class="px-1.5 py-0.5 rounded-[4px] text-[9px] font-black bg-indigo-50 text-indigo-600 uppercase tracking-wide"><i class="fas fa-building mr-1"></i> Corp</span>' :
          '<span class="px-1.5 py-0.5 rounded-[4px] text-[9px] font-black bg-amber-50 text-amber-600 uppercase tracking-wide"><i class="fas fa-user mr-1"></i> Indiv</span>';

        // Checkmark logic
        let checkmark = '';
        if (isBillTo) checkmark =
          '<div class="absolute top-3 right-3 text-brand-500 animate-entry" title="Billing"><i class="fas fa-check-circle text-lg"></i></div>';
        if (isShipTo) checkmark =
          '<div class="absolute top-3 right-3 text-indigo-500 animate-entry" title="Shipping"><i class="fas fa-truck text-lg"></i></div>';

        // Add stagger effect via style
        const delay = index * 0.05;
        const style = `animation-delay: ${delay}s`;

        const address = c.address || 'No Address';

        const card = `
                <div class="customer-card relative cursor-pointer rounded-xl p-4 border transition-all duration-300 group flex items-start gap-3 ${activeClass} animate-entry" 
                     style="${style}"
                     data-id="${c.id}">
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
      // Update Bill To
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
        $('#billToSummary').removeClass('border-dashed bg-slate-50/50').addClass(
          'bg-white shadow-sm border-brand-100 border-solid');
      } else {
        $('#billToSummary').html(`
                <div class="flex flex-col items-center justify-center text-center py-4 text-slate-400">
                   <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                       <i class="fas fa-file-invoice-dollar text-lg opacity-50"></i>
                   </div>
                   <span class="text-[11px] font-bold">Select Billing</span>
                 </div>
             `);
        $('#billToSummary').addClass('border-dashed bg-slate-50/50').removeClass(
          'bg-white shadow-sm border-brand-100 border-solid');
      }

      // Update Ship To
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
          $('#shipToSummary').html(
              `<div class="text-center text-[10px] text-indigo-400 font-bold italic py-2">Select a receiver above</div>`)
            .removeClass('hidden');
        } else {
          $('#shipToSummary').addClass('hidden');
        }
      }

      // Enable/Disable Continue
      $('#continueBtn').prop('disabled', !selectedBillTo);
    }
  </script>
@endpush
