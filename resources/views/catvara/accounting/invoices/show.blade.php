@extends('catvara.layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
  <div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div>
        <div class="flex items-center gap-3">
          <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Invoice #{{ $invoice->invoice_number }}</h2>
          @php
            $statusCode = $invoice->status->code ?? 'DRAFT';
            $statusColor = match ($statusCode) {
              'DRAFT' => 'badge-warning',
              'ISSUED' => 'badge-info',
              'PAID' => 'badge-success',
              'PARTIALLY_PAID' => 'badge-primary',
              'VOIDED' => 'badge-danger',
              default => 'badge-secondary',
            };
          @endphp
          <span class="badge {{ $statusColor }}">
            {{ $invoice->status->name ?? 'Draft' }}
          </span>
          @if ($invoice->posted_at)
            <span class="badge badge-success">
              <i class="fas fa-check-circle mr-1"></i> Posted
            </span>
          @endif
        </div>
        <p class="text-slate-400 text-sm mt-1 font-medium italic">
          Generated from Order #{{ $invoice->source_id }} on {{ $invoice->created_at->format('M d, Y') }}
        </p>
      </div>
      <div class="flex items-center gap-3 flex-wrap">
        @if (!$invoice->posted_at)
          <button type="button" id="postInvoiceBtn"
            class="btn btn-primary bg-linear-to-r from-indigo-500 to-indigo-600 border-none shadow-lg shadow-indigo-500/25">
            <i class="fas fa-file-export mr-2"></i> Post Invoice
          </button>
        @endif

        <div class="flex items-center gap-2 mr-2">
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" id="hideVariantsToggle" class="sr-only peer">
            <div
              class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500">
            </div>
            <span class="ms-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Hide Variants</span>
          </label>
        </div>

        <a href="{{ company_route('accounting.invoices.print', ['invoice' => $invoice->uuid]) }}" target="_blank"
          id="printInvoiceBtn" class="btn btn-white">
          <i class="fas fa-print mr-2 text-slate-500"></i> Print
        </a>
        <a href="{{ company_route('sales-orders.show', ['sales_order' => $invoice->source_id]) }}" class="btn btn-white">
          <i class="fas fa-external-link-alt mr-2 text-slate-500"></i> View Order
        </a>
        <a href="{{ company_route('accounting.invoices.index') }}" class="btn btn-white">
          <i class="fas fa-arrow-left mr-2 text-slate-500"></i> Back
        </a>
        @if(auth()->user()->isSuperAdmin())
          <button type="button" id="deleteInvoiceBtn" class="btn btn-danger">
            <i class="fas fa-trash mr-2"></i> Delete
          </button>
        @endif
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Details & Items -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Addresses & Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Billing Details -->
          <div class="card overflow-hidden border-none shadow-sm h-full flex flex-col">
            <div class="bg-linear-to-r from-slate-50 to-slate-100/50 px-6 py-4 border-b border-slate-100">
              <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                <i class="fas fa-file-invoice-dollar mr-3 text-brand-500"></i> Billed To
              </h3>
            </div>
            <div class="p-6 space-y-4 flex-grow bg-white">
              @php $billTo = $invoice->billingAddress; @endphp
              <div class="flex flex-col">
                <span
                  class="text-lg font-bold text-slate-900">{{ $billTo->name ?? ($invoice->customer->legal_name ?? $invoice->customer->display_name) }}</span>
                <span class="text-slate-500 font-medium">{{ $invoice->customer->email }}</span>
              </div>
              <div class="space-y-1 text-slate-600 bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                <p class="flex items-start gap-3">
                  <i class="fas fa-map-marker-alt mt-1 text-slate-400"></i>
                  <span>
                    {{ $billTo->address_line_1 ?? '' }}<br>
                    @if ($billTo->address_line_2)
                      {{ $billTo->address_line_2 }}<br>
                    @endif
                    {{ $billTo->city ?? '' }}{{ $billTo->zip_code ? ', ' . $billTo->zip_code : '' }}<br>
                    {{ $billTo->state->name ?? '' }}{{ ($billTo->country->name ?? null) ? ', ' . $billTo->country->name : '' }}
                  </span>
                </p>
                @if ($billTo->phone)
                  <p class="flex items-center gap-3 pt-2">
                    <i class="fas fa-phone text-slate-400"></i>
                    <span>{{ $billTo->phone }}</span>
                  </p>
                @endif
              </div>
            </div>
          </div>

          <!-- Invoice Summary Info -->
          <div class="card overflow-hidden border-none shadow-sm h-full flex flex-col">
            <div class="bg-linear-to-r from-slate-50 to-slate-100/50 px-6 py-4 border-b border-slate-100">
              <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                <i class="fas fa-info-circle mr-3 text-brand-500"></i> Invoice Details
              </h3>
            </div>
            <div class="p-6 space-y-4 bg-white flex-grow">
              <div class="grid grid-cols-1 gap-4">
                <div
                  class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 flex justify-between items-center text-sm">
                  <span class="text-slate-500 font-medium">Issue Date</span>
                  <span
                    class="font-bold text-slate-900">{{ $invoice->issued_at ? $invoice->issued_at->format('M d, Y') : $invoice->created_at->format('M d, Y') }}</span>
                </div>
                <div
                  class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 flex justify-between items-center text-sm">
                  <span class="text-slate-500 font-medium">Due Date</span>
                  <span
                    class="font-bold {{ $invoice->due_date && $invoice->due_date->isPast() ? 'text-red-600' : 'text-slate-900' }}">
                    {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Immediate' }}
                  </span>
                </div>
                <div
                  class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 flex justify-between items-center text-sm">
                  <span class="text-slate-500 font-medium">Payment Term</span>
                  <span class="font-bold text-slate-900">{{ $invoice->payment_term_name ?? 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <div class="card border-none shadow-sm overflow-hidden bg-white">
          <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg">Invoiced Items</h3>
            <span class="badge badge-secondary">{{ $invoice->items->count() }} Items</span>
          </div>
          <div class="overflow-x-auto">
            <table class="table-premium w-full text-sm">
              <thead
                class="bg-slate-50/50 text-slate-500 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
                <tr>
                  <th class="px-6 py-4 text-left">Product</th>
                  <th class="px-6 py-4 text-center">Qty</th>
                  <th class="px-6 py-4 text-right">Unit Price</th>
                  <th class="px-6 py-4 text-right">Tax</th>
                  <th class="px-6 py-4 text-right">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                @foreach ($invoice->items as $item)
                  <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                      <div class="flex flex-col">
                        <span class="font-bold text-slate-800">{{ $item->product_name }}</span>
                        @if ($item->variant_description)
                          <span class="text-xs text-slate-400 mt-0.5">{{ $item->variant_description }}</span>
                        @endif
                      </div>
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-slate-700">{{ $item->quantity }}</td>
                    <td class="px-6 py-4 text-right font-medium text-slate-600">
                      {{ money($item->unit_price, $invoice->currency->code) }}
                    </td>
                    <td class="px-6 py-4 text-right font-medium text-slate-600">
                      {{ money($item->tax_amount, $invoice->currency->code) }}
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-slate-900">
                      {{ money($item->line_total, $invoice->currency->code) }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Column: Totals & Notes -->
      <div class="space-y-8">
        <!-- Totals Card -->
        <div class="card border-none shadow-xl bg-slate-900 text-white overflow-hidden relative group">
          <div class="absolute inset-0 bg-linear-to-br from-brand-600/20 to-transparent pointer-events-none"></div>
          <div class="p-8 relative z-10">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] mb-8 text-brand-400">Total Breakdown</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center text-slate-400">
                <span class="text-sm font-bold uppercase tracking-widest">Subtotal</span>
                <span class="text-lg font-bold">{{ money($invoice->subtotal, $invoice->currency->code) }}</span>
              </div>

              @if ($invoice->discount_total > 0)
                <div class="flex justify-between items-center text-emerald-400">
                  <div class="flex flex-col">
                    <span class="text-sm font-bold uppercase tracking-widest">Total Discount</span>
                    @if ($invoice->global_discount_percent > 0)
                      <span class="text-[9px] font-black uppercase tracking-tighter opacity-70">Incl.
                        {{ (float) $invoice->global_discount_percent }}% Global</span>
                    @endif
                  </div>
                  <span class="text-lg font-bold">-{{ money($invoice->discount_total, $invoice->currency->code) }}</span>
                </div>
              @endif

              @if ($invoice->shipping_total > 0)
                <div class="flex justify-between items-center text-slate-400">
                  <span class="text-sm font-bold uppercase tracking-widest">Shipping</span>
                  <span class="text-lg font-bold">{{ money($invoice->shipping_total, $invoice->currency->code) }}</span>
                </div>
              @endif

              <div class="flex justify-between items-center text-slate-400">
                <div class="flex flex-col">
                  <span class="text-sm font-bold uppercase tracking-widest">Tax Amount</span>
                  @if ($invoice->shipping_tax_total > 0)
                    <span class="text-[9px] font-black uppercase tracking-tighter opacity-70">Incl.
                      {{ money($invoice->shipping_tax_total, $invoice->currency->code) }} Shipping Tax</span>
                  @endif
                </div>
                <span class="text-lg font-bold">{{ money($invoice->tax_total, $invoice->currency->code) }}</span>
              </div>

              <div class="pt-6 mt-6 border-t border-slate-800">
                <div class="flex justify-between items-end">
                  <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-400 mb-1">Grand
                      Total</span>
                    <span
                      class="text-4xl font-black tracking-tight text-brand-500">{{ money($invoice->grand_total, $invoice->currency->code) }}</span>
                  </div>
                  <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-1">Status</span>
                    <span
                      class="text-xs font-black uppercase tracking-widest {{ $invoice->paymentStatus?->code === 'PAID' ? 'text-emerald-500' : 'text-orange-500' }}">
                      {{ $invoice->paymentStatus->name ?? 'Unpaid' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Internal Notes -->
        <div class="card border-none shadow-sm overflow-hidden bg-white">
          <button type="button" class="w-full text-left" onclick="this.nextElementSibling.classList.toggle('hidden')">
            <div class="px-6 py-4 flex items-center justify-between bg-slate-50/50">
              <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center">
                <i class="fas fa-sticky-note mr-3 text-amber-500"></i> Notes
              </h3>
              <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
            </div>
          </button>
          <div class="p-6">
            <div
              class="text-sm text-slate-600 bg-slate-50/50 p-4 rounded-xl border border-dashed border-slate-200 min-h-[100px]">
              {{ $invoice->notes ?: 'No additional notes provided.' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (!$invoice->posted_at)
    <script>
      // Hide Variants Toggle
      document.getElementById('hideVariantsToggle')?.addEventListener('change', function () {
        const isChecked = this.checked;
        const printBtn = document.getElementById('printInvoiceBtn');
        const baseUrl = "{{ company_route('accounting.invoices.print', ['invoice' => $invoice->uuid]) }}";

        if (isChecked) {
          printBtn.href = baseUrl + "?hide_variants=1";
        } else {
          printBtn.href = baseUrl;
        }
      });

      document.getElementById('postInvoiceBtn')?.addEventListener('click', function () {
        if (!confirm(
          'Are you sure you want to POST this invoice? This will finalize the document and cannot be undone.'))
          return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Posting...';

        fetch("{{ company_route('accounting.invoices.post', ['invoice' => $invoice->uuid]) }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              window.location.reload();
            } else {
              alert(data.message || 'Error posting invoice');
              btn.disabled = false;
              btn.innerHTML = '<i class="fas fa-file-export mr-2"></i> Post Invoice';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-export mr-2"></i> Post Invoice';
          });
      });

      // Delete Invoice Handler
      document.getElementById('deleteInvoiceBtn')?.addEventListener('click', function () {
        Swal.fire({
          title: 'Delete Invoice?',
          text: 'Are you sure you want to delete invoice #{{ $invoice->invoice_number }}?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc2626',
          cancelButtonColor: '#64748b',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            const btn = document.getElementById('deleteInvoiceBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';

            fetch("{{ company_route('accounting.invoices.destroy', ['invoice' => $invoice->uuid]) }}", {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              }
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire('Deleted!', data.message, 'success').then(() => {
                    window.location.href = "{{ company_route('accounting.invoices.index') }}";
                  });
                } else {
                  Swal.fire('Error!', data.message || 'Error deleting invoice', 'error');
                  btn.disabled = false;
                  btn.innerHTML = '<i class="fas fa-trash mr-2"></i> Delete';
                }
              })
              .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An error occurred while deleting the invoice.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash mr-2"></i> Delete';
              });
          }
        });
      });
    </script>
  @endif
@endsection
