@extends('theme.adminlte.layouts.app')

@section('title', 'Payment Details - ' . $payment->payment_number)

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8 col-md-12">
      <div class="d-flex align-items-start">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-money-bill-wave"></i>
          </span>
        </div>

        <div>
          <h1 class="m-0">{{ $payment->payment_number }}</h1>
          <div class="help-hint mb-0">
            Payment details and application history
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end mt-3 mt-lg-0">
      <a href="{{ company_route('accounting.payments.index') }}" class="btn btn-outline-secondary btn-ent mr-2">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
      @if($payment->canBeEdited())
        <a href="{{ company_route('accounting.payments.edit', ['payment' => $payment->id]) }}" class="btn btn-primary btn-ent mr-2">
          <i class="fas fa-edit mr-1"></i> Edit
        </a>
      @endif
      @if($payment->isPending())
        <form action="{{ company_route('accounting.payments.confirm', ['payment' => $payment->id]) }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-success btn-ent mr-2">
            <i class="fas fa-check mr-1"></i> Confirm
          </button>
        </form>
      @endif
      @if($payment->canBeCancelled())
        <form action="{{ company_route('accounting.payments.cancel', ['payment' => $payment->id]) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Are you sure you want to cancel this payment?')">
          @csrf
          <button type="submit" class="btn btn-danger btn-ent">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
        </form>
      @endif
    </div>
  </div>
@endsection

@section('content')
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('error') }}
    </div>
  @endif

  <div class="row">
    {{-- Main Details --}}
    <div class="col-lg-8">
      {{-- Payment Info --}}
      <div class="card ent-card mb-3">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-info-circle"></i> Payment Information</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <table class="table table-sm table-borderless">
                <tr>
                  <td class="text-muted" width="40%">Payment Number</td>
                  <td><strong>{{ $payment->payment_number }}</strong></td>
                </tr>
                <tr>
                  <td class="text-muted">Customer</td>
                  <td>{{ $payment->customer?->display_name ?? 'Walk-in' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Payment Method</td>
                  <td>{{ $payment->method?->name ?? '—' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Source</td>
                  <td>
                    <span class="badge badge-secondary">{{ $payment->source_label }}</span>
                  </td>
                </tr>
                <tr>
                  <td class="text-muted">Direction</td>
                  <td>
                    @if($payment->direction === 'IN')
                      <span class="text-success"><i class="fas fa-arrow-down"></i> Received</span>
                    @else
                      <span class="text-danger"><i class="fas fa-arrow-up"></i> Refund</span>
                    @endif
                  </td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-sm table-borderless">
                <tr>
                  <td class="text-muted" width="40%">Status</td>
                  <td>
                    @php
                      $statusColors = [
                        'PENDING' => 'warning',
                        'CONFIRMED' => 'success',
                        'FAILED' => 'danger',
                        'CANCELLED' => 'secondary',
                        'REFUNDED' => 'info',
                      ];
                      $color = $statusColors[$payment->status?->code] ?? 'secondary';
                    @endphp
                    <span class="badge badge-{{ $color }}">{{ $payment->status?->name ?? '—' }}</span>
                  </td>
                </tr>
                <tr>
                  <td class="text-muted">Payment Date</td>
                  <td>{{ $payment->paid_at?->format('M d, Y H:i') ?? '—' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Reference</td>
                  <td>{{ $payment->reference ?? '—' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Created By</td>
                  <td>{{ $payment->creator?->name ?? '—' }}</td>
                </tr>
                @if($payment->confirmed_at)
                  <tr>
                    <td class="text-muted">Confirmed By</td>
                    <td>{{ $payment->confirmer?->name ?? '—' }} @ {{ $payment->confirmed_at->format('M d, Y H:i') }}</td>
                  </tr>
                @endif
              </table>
            </div>
          </div>

          @if($payment->description)
            <div class="mt-3 p-3 bg-light rounded">
              <strong>Notes:</strong><br>
              {{ $payment->description }}
            </div>
          @endif
        </div>
      </div>

      {{-- Amount Details --}}
      <div class="card ent-card mb-3">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-calculator"></i> Amount Details</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 text-center border-right">
              <div class="text-muted small">Total Amount</div>
              <div class="h3 mb-0 text-primary">
                {{ $payment->currency?->symbol }}{{ number_format((float)$payment->amount, 2) }}
              </div>
              <div class="text-muted small">{{ $payment->currency?->code }}</div>
            </div>
            <div class="col-md-4 text-center border-right">
              <div class="text-muted small">Allocated</div>
              <div class="h3 mb-0 text-success">
                {{ $payment->currency?->symbol }}{{ number_format((float)$payment->allocatedAmount(), 2) }}
              </div>
              <div class="text-muted small">Applied to documents</div>
            </div>
            <div class="col-md-4 text-center">
              <div class="text-muted small">Unallocated</div>
              <div class="h3 mb-0 {{ (float)$payment->unallocated_amount > 0 ? 'text-warning' : 'text-success' }}">
                {{ $payment->currency?->symbol }}{{ number_format((float)$payment->unallocated_amount, 2) }}
              </div>
              <div class="text-muted small">Available to apply</div>
            </div>
          </div>

          @if($payment->exchange_rate != 1)
            <hr>
            <div class="row">
              <div class="col-md-6">
                <small class="text-muted">Exchange Rate: {{ $payment->exchange_rate }}</small>
              </div>
              <div class="col-md-6 text-right">
                <small class="text-muted">Base Amount: {{ number_format((float)$payment->base_amount, 2) }}</small>
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- Applications --}}
      <div class="card ent-card mb-3">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-link"></i> Payment Applications</h3>
            @if($payment->isConfirmed() && (float)$payment->unallocated_amount > 0)
              <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#applyModal" data-bs-toggle="modal" data-bs-target="#applyModal">
                <i class="fas fa-plus"></i> Apply Payment
              </button>
            @endif
          </div>
        </div>
        <div class="card-body p-0">
          @if($payment->applications->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Document</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Applied By</th>
                    <th>Applied At</th>
                    <th width="80"></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($payment->applications as $app)
                    <tr>
                      <td>
                        <strong>{{ $app->document_number ?? $app->paymentable_id }}</strong>
                      </td>
                      <td>
                        <span class="badge badge-info">{{ $app->document_type_label }}</span>
                      </td>
                      <td>
                        {{ $payment->currency?->symbol }}{{ number_format((float)$app->amount, 2) }}
                      </td>
                      <td>{{ $app->applier?->name ?? '—' }}</td>
                      <td>{{ $app->applied_at?->format('M d, Y H:i') }}</td>
                      <td>
                        <form action="{{ company_route('accounting.payments.applications.remove', ['application' => $app->id]) }}"
                          method="POST" onsubmit="return confirm('Remove this application?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                            <i class="fas fa-unlink"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="p-4 text-center text-muted">
              <i class="fas fa-link fa-3x mb-3"></i>
              <p class="mb-0">No applications yet. This payment is unallocated.</p>
            </div>
          @endif
        </div>
      </div>

      {{-- Attachments --}}
      <div class="card ent-card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-paperclip"></i> Attachments / Proof of Payment</h3>
        </div>
        <div class="card-body">
          @if($payment->attachments->count() > 0)
            <div class="row">
              @foreach($payment->attachments as $attachment)
                <div class="col-md-3 col-6 mb-3">
                  <div class="card h-100">
                    <div class="card-body p-2 text-center">
                      @if(str_starts_with($attachment->mime_type, 'image/'))
                        <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                          <img src="{{ asset('storage/' . $attachment->path) }}" class="img-fluid mb-2" style="max-height: 100px;">
                        </a>
                      @elseif(str_contains($attachment->mime_type, 'pdf'))
                        <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                          <i class="fas fa-file-pdf fa-3x mb-2 text-danger"></i>
                        </a>
                      @else
                        <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                          <i class="fas fa-file-alt fa-3x mb-2 text-primary"></i>
                        </a>
                      @endif
                      <div class="small text-truncate" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</div>
                      <div class="text-muted small">{{ number_format($attachment->size / 1024, 1) }} KB</div>
                      <a href="{{ asset('storage/' . $attachment->path) }}" download="{{ $attachment->file_name }}" class="btn btn-xs btn-outline-primary mt-1">
                        <i class="fas fa-download"></i> Download
                      </a>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-muted mb-0">No attachments uploaded.</p>
          @endif
        </div>
      </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
      {{-- Quick Summary --}}
      <div class="card ent-card mb-3">
        <div class="card-header bg-primary text-white">
          <h3 class="card-title mb-0"><i class="fas fa-receipt"></i> Summary</h3>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <span>Amount</span>
            <strong>{{ $payment->currency?->symbol }}{{ number_format((float)$payment->amount, 2) }}</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Allocated</span>
            <span class="text-success">-{{ $payment->currency?->symbol }}{{ number_format((float)$payment->allocatedAmount(), 2) }}</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between">
            <strong>Unallocated</strong>
            <strong class="{{ (float)$payment->unallocated_amount > 0 ? 'text-warning' : 'text-success' }}">
              {{ $payment->currency?->symbol }}{{ number_format((float)$payment->unallocated_amount, 2) }}
            </strong>
          </div>
        </div>
      </div>

      {{-- Activity Timeline --}}
      <div class="card ent-card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-history"></i> Activity</h3>
        </div>
        <div class="card-body">
          <div class="timeline timeline-inverse">
            <div class="time-label">
              <span class="bg-secondary">{{ $payment->created_at->format('M d, Y') }}</span>
            </div>
            <div>
              <i class="fas fa-plus bg-primary"></i>
              <div class="timeline-item">
                <span class="time"><i class="far fa-clock"></i> {{ $payment->created_at->format('H:i') }}</span>
                <h3 class="timeline-header">Payment Created</h3>
                <div class="timeline-body">
                  By {{ $payment->creator?->name ?? 'System' }}
                </div>
              </div>
            </div>
            @if($payment->confirmed_at)
              <div>
                <i class="fas fa-check bg-success"></i>
                <div class="timeline-item">
                  <span class="time"><i class="far fa-clock"></i> {{ $payment->confirmed_at->format('H:i') }}</span>
                  <h3 class="timeline-header">Payment Confirmed</h3>
                  <div class="timeline-body">
                    By {{ $payment->confirmer?->name ?? 'System' }}
                  </div>
                </div>
              </div>
            @endif
            @foreach($payment->applications as $app)
              <div>
                <i class="fas fa-link bg-info"></i>
                <div class="timeline-item">
                  <span class="time"><i class="far fa-clock"></i> {{ $app->applied_at?->format('H:i') }}</span>
                  <h3 class="timeline-header">Applied to {{ $app->document_type_label }}</h3>
                  <div class="timeline-body">
                    {{ $payment->currency?->symbol }}{{ number_format((float)$app->amount, 2) }} by {{ $app->applier?->name ?? 'System' }}
                  </div>
                </div>
              </div>
            @endforeach
            <div>
              <i class="far fa-clock bg-gray"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Apply Payment Modal --}}
  @if($payment->isConfirmed() && (float)$payment->unallocated_amount > 0)
    <div class="modal fade" id="applyModal" tabindex="-1" role="dialog" aria-labelledby="applyModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <form action="{{ company_route('accounting.payments.apply', ['payment' => $payment->id]) }}" method="POST" id="applyPaymentForm">
            @csrf
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="applyModalLabel">
                <i class="fas fa-link mr-2"></i>Apply Payment to Order/Invoice
              </h5>
              <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              {{-- Available Amount Banner --}}
              <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
                <div>
                  <i class="fas fa-wallet mr-2"></i>
                  <strong>Available to Apply:</strong>
                </div>
                <div class="h4 mb-0">
                  {{ $payment->currency?->symbol }}<span id="availableAmount">{{ number_format((float)$payment->unallocated_amount, 2) }}</span>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  {{-- Document Type --}}
                  <div class="form-group">
                    <label><i class="fas fa-file-alt mr-1"></i> Document Type <span class="text-danger">*</span></label>
                    <select name="paymentable_type" id="docType" class="form-control" required>
                      <option value="">Select Type</option>
                      <option value="order" selected>Sales Order</option>
                      <option value="invoice">Invoice</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  {{-- Customer Filter (optional) --}}
                  <div class="form-group">
                    <label><i class="fas fa-user mr-1"></i> Filter by Customer</label>
                    <select id="customerFilter" class="form-control">
                      <option value="">All Customers</option>
                      @if($payment->customer)
                        <option value="{{ $payment->customer->id }}" selected>{{ $payment->customer->display_name }}</option>
                      @endif
                    </select>
                  </div>
                </div>
              </div>

              {{-- Order/Invoice Selection --}}
              <div class="form-group">
                <label><i class="fas fa-search mr-1"></i> Select Order/Invoice <span class="text-danger">*</span></label>
                <select name="paymentable_id" id="documentSelect" class="form-control" required>
                  <option value="">Search or select a document...</option>
                </select>
                <small class="text-muted">Search by order number or select from the list</small>
              </div>

              {{-- Selected Document Info --}}
              <div id="documentInfo" class="card bg-light mb-3" style="display: none;">
                <div class="card-body py-2">
                  <div class="row text-center">
                    <div class="col-4">
                      <small class="text-muted d-block">Order Total</small>
                      <strong id="docTotal">—</strong>
                    </div>
                    <div class="col-4">
                      <small class="text-muted d-block">Already Paid</small>
                      <strong id="docPaid" class="text-success">—</strong>
                    </div>
                    <div class="col-4">
                      <small class="text-muted d-block">Balance Due</small>
                      <strong id="docBalance" class="text-danger">—</strong>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  {{-- Amount to Apply --}}
                  <div class="form-group">
                    <label><i class="fas fa-money-bill mr-1"></i> Amount to Apply <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">{{ $payment->currency?->symbol ?? '₹' }}</span>
                      </div>
                      <input type="number" step="0.01" name="amount" id="applyAmount" class="form-control" required
                        min="0.01" max="{{ $payment->unallocated_amount }}" value="{{ $payment->unallocated_amount }}"
                        data-max-available="{{ $payment->unallocated_amount }}">
                    </div>
                    <small class="text-muted">Max: <span id="maxApplyHint">{{ $payment->currency?->symbol }}{{ number_format((float)$payment->unallocated_amount, 2) }}</span></small>
                  </div>
                </div>
                <div class="col-md-6">
                  {{-- Quick Amount Buttons --}}
                  <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="btn-group btn-block" role="group">
                      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnApplyBalance" title="Apply full balance">
                        <i class="fas fa-balance-scale"></i> Full Balance
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnApplyAvailable" title="Apply all available">
                        <i class="fas fa-wallet"></i> All Available
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Notes --}}
              <div class="form-group">
                <label><i class="fas fa-sticky-note mr-1"></i> Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes about this allocation..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">
                <i class="fas fa-times mr-1"></i> Cancel
              </button>
              <button type="submit" class="btn btn-primary" id="btnApplySubmit">
                <i class="fas fa-check mr-1"></i> Apply Payment
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
  #applyModal .select2-container { width: 100% !important; }
  #applyModal .select2-selection { min-height: 38px; }
  #documentInfo { border-left: 4px solid #17a2b8; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
  const currencySymbol = '{{ $payment->currency?->symbol ?? "₹" }}';
  const maxAvailable = parseFloat('{{ $payment->unallocated_amount }}');
  const customerId = '{{ $payment->customer_id ?? "" }}';
  let selectedDocBalance = 0;

  // Initialize Select2 for document selection
  $('#documentSelect').select2({
    theme: 'bootstrap4',
    placeholder: 'Search by order number...',
    allowClear: true,
    dropdownParent: $('#applyModal'),
    ajax: {
      url: '{{ company_route("accounting.payments.customer-documents") }}',
      dataType: 'json',
      delay: 250,
      data: function(params) {
        return {
          customer_id: $('#customerFilter').val() || customerId,
          type: $('#docType').val() || 'order',
          q: params.term
        };
      },
      processResults: function(data) {
        return {
          results: data.map(function(doc) {
            return {
              id: doc.id,
              text: doc.number + ' - Balance: ' + currencySymbol + parseFloat(doc.balance).toFixed(2),
              total: doc.total,
              paid: doc.paid,
              balance: doc.balance,
              status: doc.status
            };
          })
        };
      },
      cache: true
    },
    minimumInputLength: 0
  });

  // Load documents on modal open
  $('#applyModal').on('shown.bs.modal show.bs.modal', function() {
    loadDocuments();
  });

  // Reload when type or customer changes
  $('#docType, #customerFilter').on('change', function() {
    $('#documentSelect').val(null).trigger('change');
    loadDocuments();
  });

  // Load documents into dropdown
  function loadDocuments() {
    const type = $('#docType').val() || 'order';
    const customer = $('#customerFilter').val() || customerId;

    $.get('{{ company_route("accounting.payments.customer-documents") }}', {
      customer_id: customer,
      type: type
    }, function(data) {
      $('#documentSelect').empty().append('<option value="">Select a document...</option>');
      data.forEach(function(doc) {
        const text = doc.number + ' - ' + doc.status + ' - Balance: ' + currencySymbol + parseFloat(doc.balance).toFixed(2);
        $('#documentSelect').append(new Option(text, doc.id, false, false));
        // Store data
        $('#documentSelect option[value="' + doc.id + '"]').data('doc', doc);
      });
    });
  }

  // When document is selected
  $('#documentSelect').on('select2:select change', function(e) {
    const docId = $(this).val();
    if (!docId) {
      $('#documentInfo').hide();
      selectedDocBalance = 0;
      updateAmountHint();
      return;
    }

    // Get doc data from Select2 or from stored data
    let doc = null;
    if (e.params && e.params.data) {
      doc = e.params.data;
    } else {
      doc = $('#documentSelect option:selected').data('doc');
    }

    if (doc) {
      selectedDocBalance = parseFloat(doc.balance) || 0;
      $('#docTotal').text(currencySymbol + parseFloat(doc.total).toFixed(2));
      $('#docPaid').text(currencySymbol + parseFloat(doc.paid).toFixed(2));
      $('#docBalance').text(currencySymbol + selectedDocBalance.toFixed(2));
      $('#documentInfo').show();

      // Auto-fill amount with min(balance, available)
      const suggestedAmount = Math.min(selectedDocBalance, maxAvailable);
      $('#applyAmount').val(suggestedAmount.toFixed(2));
      updateAmountHint();
    }
  });

  // Update max hint
  function updateAmountHint() {
    const maxApply = selectedDocBalance > 0 ? Math.min(selectedDocBalance, maxAvailable) : maxAvailable;
    $('#maxApplyHint').text(currencySymbol + maxApply.toFixed(2));
    $('#applyAmount').attr('max', maxApply);
  }

  // Quick buttons
  $('#btnApplyBalance').on('click', function() {
    if (selectedDocBalance > 0) {
      const amount = Math.min(selectedDocBalance, maxAvailable);
      $('#applyAmount').val(amount.toFixed(2));
    }
  });

  $('#btnApplyAvailable').on('click', function() {
    $('#applyAmount').val(maxAvailable.toFixed(2));
  });

  // Validate on submit
  $('#applyPaymentForm').on('submit', function(e) {
    const amount = parseFloat($('#applyAmount').val()) || 0;
    const docId = $('#documentSelect').val();

    if (!docId) {
      e.preventDefault();
      alert('Please select a document to apply payment to.');
      return false;
    }

    if (amount <= 0) {
      e.preventDefault();
      alert('Please enter a valid amount.');
      return false;
    }

    if (amount > maxAvailable) {
      e.preventDefault();
      alert('Amount cannot exceed available balance (' + currencySymbol + maxAvailable.toFixed(2) + ')');
      return false;
    }

    if (selectedDocBalance > 0 && amount > selectedDocBalance) {
      e.preventDefault();
      alert('Amount cannot exceed document balance (' + currencySymbol + selectedDocBalance.toFixed(2) + ')');
      return false;
    }
  });

  // Modal trigger fallback
  $('[data-target="#applyModal"]').on('click', function(e) {
    e.preventDefault();
    $('#applyModal').modal('show');
  });
});
</script>
@endpush
