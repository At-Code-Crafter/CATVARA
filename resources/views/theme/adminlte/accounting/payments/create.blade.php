@extends('theme.adminlte.layouts.app')

@section('title', 'Record Payment')

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
          <h1 class="m-0">Record Payment</h1>
          <div class="help-hint mb-0">Record a new payment received from customer.</div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end mt-3 mt-lg-0">
      <a href="{{ company_route('accounting.payments.index') }}" class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-arrow-left mr-1"></i> Back to Payments
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row">
    <div class="col-lg-8">
      <form action="{{ company_route('accounting.payments.store') }}" method="POST" id="payment-form" enctype="multipart/form-data">
        @csrf

        {{-- Payment Details --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Payment Details</h3>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="customer_id">Customer <small class="text-muted">(Optional)</small></label>
                  <select name="customer_id" id="customer_id" class="form-control select2 @error('customer_id') is-invalid @enderror">
                    <option value="">Walk-in / Anonymous</option>
                    @foreach ($customers as $customer)
                      <option value="{{ $customer->id }}" {{ old('customer_id', $order?->customer_id) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->display_name }}
                      </option>
                    @endforeach
                  </select>
                  @error('customer_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="payment_method_id">Payment Method <span class="text-danger">*</span></label>
                  <select name="payment_method_id" id="payment_method_id" class="form-control @error('payment_method_id') is-invalid @enderror" required>
                    <option value="">Select Method</option>
                    @foreach ($methods as $method)
                      <option value="{{ $method->id }}"
                        data-requires-reference="{{ $method->requires_reference ? '1' : '0' }}"
                        {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                        {{ $method->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('payment_method_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="amount">Amount <span class="text-danger">*</span></label>
                  <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                    class="form-control @error('amount') is-invalid @enderror"
                    value="{{ old('amount', $order?->grand_total) }}" required>
                  @error('amount')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="currency_id">Currency <span class="text-danger">*</span></label>
                  <select name="currency_id" id="currency_id" class="form-control @error('currency_id') is-invalid @enderror" required>
                    @foreach ($currencies as $currency)
                      <option value="{{ $currency->id }}" {{ old('currency_id', 1) == $currency->id ? 'selected' : '' }}>
                        {{ $currency->code }} - {{ $currency->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('currency_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="exchange_rate">Exchange Rate</label>
                  <input type="number" step="0.00000001" min="0.00000001" name="exchange_rate" id="exchange_rate"
                    class="form-control @error('exchange_rate') is-invalid @enderror"
                    value="{{ old('exchange_rate', '1.00000000') }}">
                  @error('exchange_rate')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="paid_at">Payment Date <span class="text-danger">*</span></label>
                  <input type="datetime-local" name="paid_at" id="paid_at"
                    class="form-control @error('paid_at') is-invalid @enderror"
                    value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required>
                  @error('paid_at')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="source">Source <span class="text-danger">*</span></label>
                  <select name="source" id="source" class="form-control @error('source') is-invalid @enderror" required>
                    <option value="MANUAL" {{ old('source') == 'MANUAL' ? 'selected' : '' }}>Manual Entry</option>
                    <option value="POS" {{ old('source') == 'POS' ? 'selected' : '' }}>POS</option>
                    <option value="WEB" {{ old('source') == 'WEB' ? 'selected' : '' }}>Web</option>
                    <option value="API" {{ old('source') == 'API' ? 'selected' : '' }}>API</option>
                  </select>
                  @error('source')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="direction">Direction <span class="text-danger">*</span></label>
                  <select name="direction" id="direction" class="form-control @error('direction') is-invalid @enderror" required>
                    <option value="IN" {{ old('direction', 'IN') == 'IN' ? 'selected' : '' }}>Payment Received</option>
                    <option value="OUT" {{ old('direction') == 'OUT' ? 'selected' : '' }}>Refund</option>
                  </select>
                  @error('direction')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group" id="reference-group">
                  <label for="reference">Reference <span class="text-danger reference-required d-none">*</span></label>
                  <input type="text" name="reference" id="reference"
                    class="form-control @error('reference') is-invalid @enderror"
                    value="{{ old('reference') }}"
                    placeholder="Bank ref, cheque no, transaction ID...">
                  @error('reference')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="description">Description / Notes</label>
                  <textarea name="description" id="description" rows="1"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Optional notes...">{{ old('description') }}</textarea>
                  @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            {{-- Attachments Section --}}
            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label for="attachments"><i class="fas fa-paperclip mr-1"></i> Proof of Payment / Attachments</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input @error('attachments.*') is-invalid @enderror"
                      id="attachments" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx">
                    <label class="custom-file-label" for="attachments" id="attachments-label">Choose files (receipt, bank slip, screenshot...)</label>
                  </div>
                  <small class="form-text text-muted">
                    Accepted: Images (JPG, PNG), PDF, DOC. Max 5MB per file. You can select multiple files.
                  </small>
                  @error('attachments.*')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div id="attachment-preview" class="row mt-2"></div>
              </div>
            </div>
          </div>
        </div>

        {{-- Apply to Document (Optional) --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <h3 class="card-title"><i class="fas fa-link"></i> Apply to Document</h3>
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="apply_toggle" {{ $order ? 'checked' : '' }}>
                <label class="custom-control-label" for="apply_toggle">Apply payment now</label>
              </div>
            </div>
          </div>

          <div class="card-body" id="apply-section" style="{{ $order ? '' : 'display: none;' }}">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="apply_to_type">Document Type</label>
                  <select name="apply_to_type" id="apply_to_type" class="form-control">
                    <option value="">Select Type</option>
                    <option value="order" {{ $order ? 'selected' : '' }}>Sales Order</option>
                    <option value="invoice">Invoice</option>
                  </select>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="apply_to_id">Document</label>
                  <select name="apply_to_id" id="apply_to_id" class="form-control select2">
                    <option value="">Select Document</option>
                    @if($order)
                      <option value="{{ $order->id }}" selected>{{ $order->order_number }} - {{ number_format($order->grand_total, 2) }}</option>
                    @endif
                  </select>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="apply_amount">Apply Amount</label>
                  <input type="number" step="0.01" min="0.01" name="apply_amount" id="apply_amount"
                    class="form-control" value="{{ old('apply_amount', $order?->grand_total) }}">
                </div>
              </div>
            </div>

            @if($order)
              <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                Applying payment to Order <strong>{{ $order->order_number }}</strong>
                (Total: {{ number_format($order->grand_total, 2) }})
              </div>
            @endif
          </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex justify-content-end">
          <a href="{{ company_route('accounting.payments.index') }}" class="btn btn-outline-secondary mr-2">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Record Payment
          </button>
        </div>
      </form>
    </div>

    {{-- Help Sidebar --}}
    <div class="col-lg-4">
      <div class="card ent-card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-question-circle"></i> Help</h3>
        </div>
        <div class="card-body">
          <h6>Payment Recording</h6>
          <p class="text-muted small">
            Record payments received from customers. You can optionally apply the payment directly to an order or invoice.
          </p>

          <h6 class="mt-3">Payment Methods</h6>
          <ul class="text-muted small pl-3">
            <li><strong>Cash</strong> - Physical cash received</li>
            <li><strong>Card</strong> - Credit/Debit card</li>
            <li><strong>Bank Transfer</strong> - Wire transfer, requires reference</li>
            <li><strong>Cheque</strong> - Paper cheque, requires reference</li>
          </ul>

          <h6 class="mt-3">Unallocated Payments</h6>
          <p class="text-muted small">
            If you don't apply the payment now, it will be marked as "unallocated" and can be applied later to any order or invoice.
          </p>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function () {
      // Select2
      $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
      });

      // Toggle apply section
      $('#apply_toggle').on('change', function() {
        $('#apply-section').toggle(this.checked);
        if (!this.checked) {
          $('#apply_to_type, #apply_to_id, #apply_amount').val('');
        }
      });

      // Reference requirement based on method
      $('#payment_method_id').on('change', function() {
        var requiresRef = $(this).find(':selected').data('requires-reference') == '1';
        if (requiresRef) {
          $('.reference-required').removeClass('d-none');
          $('#reference').attr('required', true);
        } else {
          $('.reference-required').addClass('d-none');
          $('#reference').removeAttr('required');
        }
      }).trigger('change');

      // Sync apply amount with main amount
      $('#amount').on('change', function() {
        if ($('#apply_toggle').is(':checked') && !$('#apply_amount').val()) {
          $('#apply_amount').val($(this).val());
        }
      });

      // Load documents based on type
      $('#apply_to_type').on('change', function() {
        var type = $(this).val();
        var customerId = $('#customer_id').val();

        if (!type) {
          $('#apply_to_id').html('<option value="">Select Document</option>');
          return;
        }

        // In production, load via AJAX
        // For now, placeholder
        $('#apply_to_id').html('<option value="">Loading...</option>');

        var url = type === 'order'
          ? '{{ company_route("sales-orders.data") }}'
          : '{{ company_route("invoices.data") ?? "#" }}';

        // Simple placeholder - implement proper AJAX in production
        setTimeout(function() {
          $('#apply_to_id').html('<option value="">Select Document</option>');
        }, 500);
      });

      // File attachment preview
      $('#attachments').on('change', function() {
        var files = this.files;
        var label = files.length > 0
          ? files.length + ' file(s) selected'
          : 'Choose files (receipt, bank slip, screenshot...)';
        $('#attachments-label').text(label);

        // Preview
        var $preview = $('#attachment-preview');
        $preview.empty();

        for (var i = 0; i < files.length; i++) {
          var file = files[i];
          var isImage = file.type.startsWith('image/');

          var $col = $('<div class="col-md-3 col-6 mb-2"></div>');
          var $card = $('<div class="card h-100"></div>');
          var $body = $('<div class="card-body p-2 text-center"></div>');

          if (isImage) {
            var reader = new FileReader();
            (function(f, b) {
              reader.onload = function(e) {
                b.prepend('<img src="' + e.target.result + '" class="img-fluid mb-2" style="max-height: 80px;">');
              };
              reader.readAsDataURL(f);
            })(file, $body);
          } else {
            var icon = file.type.includes('pdf') ? 'fa-file-pdf text-danger' : 'fa-file-alt text-primary';
            $body.append('<i class="fas ' + icon + ' fa-3x mb-2"></i>');
          }

          $body.append('<div class="small text-truncate" title="' + file.name + '">' + file.name + '</div>');
          $body.append('<div class="text-muted small">' + (file.size / 1024).toFixed(1) + ' KB</div>');

          $card.append($body);
          $col.append($card);
          $preview.append($col);
        }
      });
    });
  </script>
@endpush
