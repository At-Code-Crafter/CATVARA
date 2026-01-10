@extends('theme.adminlte.layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    Order #{{ $order->order_number }}
                    <small class="text-muted ml-2" style="font-size: 0.6em;">{{ $order->created_at->format('d M Y, h:i A') }}</small>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group">
                     @if(($order->status->code ?? '') !== 'CONFIRMED')
                    <a href="{{ company_route('sales-orders.edit', $order->uuid) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Order
                    </a>
                    @endif
                    <a href="{{ company_route('sales-orders.print', ['sales_order' => $order->uuid]) }}" target="_blank" class="btn btn-default btn-sm">
                        <i class="fas fa-print"></i> Print
                    </a>
                    <button type="button" class="btn btn-default btn-sm" onclick="alert('Generate Invoice feature coming soon!')">
                        <i class="fas fa-file-invoice"></i> Generate Invoice
                    </button>
                    <a href="{{ company_route('sales-orders.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Top Status Bar -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="callout callout-info d-flex justify-content-between align-items-center bg-white shadow-sm">
                    <div>
                        <strong>Order Status:</strong>
                        <span class="badge {{ ($order->status->code ?? '') === 'CONFIRMED' ? 'badge-success' : 'badge-warning' }} ml-1" style="font-size: 1rem;">
                            {{ $order->status->name ?? 'Draft' }}
                        </span>
                    </div>
                    <div>
                         <strong>Payment Status:</strong>
                        <span id="paymentStatusBadge" class="badge {{ $order->payment_status === 'PAID' ? 'badge-success' : 'badge-danger' }} ml-1" style="font-size: 1rem;">
                            {{ $order->payment_status }}
                        </span>
                        <button type="button" class="btn btn-xs btn-outline-secondary ml-2" data-toggle="modal" data-target="#updatePaymentModal">
                                <i class="fas fa-pen"></i> Update
                        </button>
                    </div>
                    <div>
                        <strong>Total:</strong>
                        <span class="text-primary font-weight-bold ml-1" style="font-size: 1.2rem;">
                            {{ number_format((float)$order->grand_total, 2) }} {{ $order->currency->code ?? '' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sell To -->
            <div class="col-md-4">
                <div class="card card-primary card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-user mr-1"></i> Customer (Sell To)</h3>
                    </div>
                    <div class="card-body">
                        <h5 class="text-primary display-customer-name">{{ $order->customer->display_name ?? 'N/A' }}</h5>
                         @if($order->customer->tax_number)
                            <p class="mb-1 text-muted small"><strong>VAT:</strong> {{ $order->customer->tax_number }}</p>
                        @endif
                        <hr class="my-2">
                        <address class="mb-0 text-secondary">
                            @if($order->shippingAddress)
                                {{ $order->shippingAddress->address_line_1 }}<br>
                                @if($order->shippingAddress->address_line_2) {{ $order->shippingAddress->address_line_2 }}<br> @endif
                                {{ $order->shippingAddress->city ?? '' }} {{ $order->shippingAddress->state->name ?? '' }} {{ $order->shippingAddress->zip_code ?? '' }}<br>
                                {{ $order->shippingAddress->country->name ?? '' }}<br>
                                <strong>Phone:</strong> {{ $order->shippingAddress->phone ?? $order->customer->phone }}<br>
                                <strong>Email:</strong> {{ $order->shippingAddress->email ?? $order->customer->email }}
                            @else
                                <span class="text-muted font-italic">No shipping address provided.</span>
                            @endif
                        </address>
                    </div>
                </div>
            </div>

            <!-- Bill To -->
            <div class="col-md-4">
                 <div class="card card-purple card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-file-invoice-dollar mr-1"></i> Billing Address</h3>
                    </div>
                    <div class="card-body">
                         @if($order->billingAddress)
                             <h5 class="text-purple display-customer-name">
                                 {{ $order->billingAddress->first_name ?? $order->customer->display_name }}
                             </h5>
                             <hr class="my-2">
                            <address class="mb-0 text-secondary">
                                {{ $order->billingAddress->address_line_1 }}<br>
                                @if($order->billingAddress->address_line_2) {{ $order->billingAddress->address_line_2 }}<br> @endif
                                {{ $order->billingAddress->city ?? '' }} {{ $order->billingAddress->state->name ?? '' }} {{ $order->billingAddress->zip_code ?? '' }}<br>
                                {{ $order->billingAddress->country->name ?? '' }}<br>
                                <strong>Phone:</strong> {{ $order->billingAddress->phone ?? '' }}<br>
                                <strong>Email:</strong> {{ $order->billingAddress->email ?? '' }}
                            </address>
                        @else
                             <p class="text-muted font-italic">Same as Sell To</p>
                        @endif
                    </div>
                </div>
            </div>

             <!-- Order Meta -->
            <div class="col-md-4">
                 <div class="card card-secondary card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Order Info</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Created By</dt>
                            <dd class="col-sm-8 text-right">
                                <div class="user-block" style="float: right;">
                                    <span class="username" style="margin-left: 0;">
                                        <a href="#">{{ $order->creator->name ?? 'System' }}</a>
                                    </span>
                                    <span class="description" style="margin-left: 0;">{{ $order->created_at->diffForHumans() }}</span>
                                </div>
                            </dd>

                            <dt class="col-sm-4">Payment Term</dt>
                            <dd class="col-sm-8 text-right">{{ $order->paymentTerm->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-4">Due Date</dt>
                            <dd class="col-sm-8 text-right text-danger font-weight-bold">
                                {{ $order->due_date ? $order->due_date->format('d M Y') : 'N/A' }}
                            </dd>

                            <dt class="col-sm-4">Currency</dt>
                            <dd class="col-sm-8 text-right">{{ $order->currency->code ?? 'AED' }}</dd>

                            <dt class="col-sm-4">Last Update</dt>
                            <dd class="col-sm-8 text-right small text-muted">{{ $order->updated_at->format('d M Y, h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3 class="card-title">Line Items</h3>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-striped table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Product / Service</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-center">Discount</th>
                                    <th class="text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $item->product_name }}</div>
                                        @if($item->variant_description)
                                            <small class="text-muted d-block">{{ $item->variant_description }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ (float)$item->quantity }}</td>
                                    <td class="text-right">{{ number_format((float)$item->unit_price, 2) }}</td>
                                    <td class="text-center">
                                        @if($item->discount_amount > 0)
                                            <span class="badge badge-light text-danger border border-danger">
                                                -{{ number_format((float)$item->discount_amount, 2) }}
                                                @if($item->discount_percent > 0)
                                                    <small>({{ (float)$item->discount_percent }}%)</small>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">{{ number_format((float)$item->line_total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer / Totals -->
        <div class="row">
            <div class="col-md-6">
                @if($order->notes)
                <div class="card shadow-none border-light bg-light">
                    <div class="card-body">
                         <h5 class="text-muted mb-2"><i class="fas fa-sticky-note mr-1"></i> Notes</h5>
                        <p class="text-secondary small mb-0">{{ $order->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-sm">
                            <tr>
                                <td class="pl-4">Subtotal</td>
                                <td class="text-right pr-4 font-weight-bold">{{ number_format((float)$order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->discount_total > 0)
                            <tr>
                                <td class="pl-4 text-success">Discount</td>
                                <td class="text-right pr-4 text-success font-weight-bold">-{{ number_format((float)$order->discount_total, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->shipping_total > 0)
                            <tr>
                                <td class="pl-4">Shipping / Additional</td>
                                <td class="text-right pr-4">{{ number_format((float)$order->shipping_total, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="pl-4">Tax</td>
                                <td class="text-right pr-4">{{ number_format((float)$order->tax_total, 2) }}</td>
                            </tr>
                            <tr class="bg-light">
                                <td class="pl-4 py-3 h5 mb-0 font-weight-bold">Grand Total</td>
                                <td class="text-right pr-4 py-3 h5 mb-0 font-weight-bold text-primary">
                                    {{ number_format((float)$order->grand_total, 2) }} {{ $order->currency->code ?? '' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Status Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title">Update Payment Status</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
         <form id="updatePaymentForm">
             <div class="form-group">
                 <label>Select New Status</label>
                 <select class="form-control" id="newPaymentStatus">
                     <option value="UNPAID" {{ $order->payment_status == 'UNPAID' ? 'selected' : '' }}>UNPAID</option>
                     <option value="PAID" {{ $order->payment_status == 'PAID' ? 'selected' : '' }}>PAID</option>
                 </select>
             </div>
             <p class="small text-muted mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                This will simply update the status label. To record a full payment transaction, better functionality will be added later.
             </p>
         </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btnSavePaymentStatus">Save Changes</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btnSavePaymentStatus').on('click', function() {
            const newStatus = $('#newPaymentStatus').val();
            const btn = $(this);
            const originalText = btn.text();

            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '{{ company_route("sales-orders.update-payment-status", ["id" => $order->id]) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    payment_status: newStatus
                },
                success: function(res) {
                    if(res.success) {
                        // Update badge
                        const badge = $('#paymentStatusBadge');
                        badge.text(res.payment_status);
                        if(res.payment_status === 'PAID') {
                            badge.removeClass('badge-danger').addClass('badge-success');
                        } else {
                            badge.removeClass('badge-success').addClass('badge-danger');
                        }
                        $('#updatePaymentModal').modal('hide');
                        toastr.success('Payment status updated successfully.');
                    }
                },
                error: function(err) {
                    toastr.error('Failed to update status. Please try again.');
                },
                complete: function() {
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });
    });
</script>
@endpush