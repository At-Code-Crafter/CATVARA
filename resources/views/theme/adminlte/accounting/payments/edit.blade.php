@extends('theme.adminlte.layouts.app')

@section('title', 'Edit Payment - ' . $payment->payment_number)

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8 col-md-12">
      <div class="d-flex align-items-start">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-edit"></i>
          </span>
        </div>

        <div>
          <h1 class="m-0">Edit Payment: {{ $payment->payment_number }}</h1>
          <div class="help-hint mb-0">
            Update payment details
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end mt-3 mt-lg-0">
      <a href="{{ company_route('accounting.payments.show', ['payment' => $payment->id]) }}" class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-times mr-1"></i> Cancel
      </a>
    </div>
  </div>
@endsection

@section('content')
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('error') }}
    </div>
  @endif

  @if(!$payment->canBeEdited())
    <div class="alert alert-warning">
      <i class="fas fa-exclamation-triangle"></i> This payment cannot be edited because it has been {{ $payment->status?->name }}.
    </div>
  @endif

  <form action="{{ company_route('accounting.payments.update', ['payment' => $payment->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
      <div class="col-lg-8">
        {{-- Payment Details --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Payment Details</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="customer_id">Customer</label>
                  <select name="customer_id" id="customer_id" class="form-control select2-customer @error('customer_id') is-invalid @enderror"
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                    <option value="">Walk-in Customer</option>
                    @if($payment->customer)
                      <option value="{{ $payment->customer_id }}" selected>{{ $payment->customer->display_name }}</option>
                    @endif
                  </select>
                  @error('customer_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="payment_method_id">Payment Method <span class="text-danger">*</span></label>
                  <select name="payment_method_id" id="payment_method_id"
                    class="form-control @error('payment_method_id') is-invalid @enderror" required
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                    <option value="">Select Method</option>
                    @foreach($paymentMethods as $method)
                      <option value="{{ $method->id }}" {{ $payment->payment_method_id == $method->id ? 'selected' : '' }}>
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
                    value="{{ old('amount', $payment->amount) }}" required
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                  @error('amount')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                  @if($payment->applications->count() > 0)
                    <small class="text-warning">
                      <i class="fas fa-exclamation-triangle"></i>
                      Minimum amount: {{ number_format($payment->allocatedAmount(), 2) }} (already allocated)
                    </small>
                  @endif
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="currency_id">Currency <span class="text-danger">*</span></label>
                  <select name="currency_id" id="currency_id"
                    class="form-control @error('currency_id') is-invalid @enderror" required
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                    @foreach($currencies as $currency)
                      <option value="{{ $currency->id }}"
                        data-symbol="{{ $currency->symbol }}"
                        {{ $payment->currency_id == $currency->id ? 'selected' : '' }}>
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
                  <input type="number" step="0.000001" min="0.000001" name="exchange_rate" id="exchange_rate"
                    class="form-control @error('exchange_rate') is-invalid @enderror"
                    value="{{ old('exchange_rate', $payment->exchange_rate ?? 1) }}"
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                  @error('exchange_rate')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="paid_at">Payment Date</label>
                  <input type="datetime-local" name="paid_at" id="paid_at"
                    class="form-control @error('paid_at') is-invalid @enderror"
                    value="{{ old('paid_at', $payment->paid_at?->format('Y-m-d\TH:i')) }}"
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                  @error('paid_at')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="source">Source <span class="text-danger">*</span></label>
                  <select name="source" id="source" class="form-control @error('source') is-invalid @enderror" required
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                    <option value="MANUAL" {{ $payment->source == 'MANUAL' ? 'selected' : '' }}>Manual Entry</option>
                    <option value="WEB" {{ $payment->source == 'WEB' ? 'selected' : '' }}>Web</option>
                    <option value="POS" {{ $payment->source == 'POS' ? 'selected' : '' }}>POS</option>
                    <option value="API" {{ $payment->source == 'API' ? 'selected' : '' }}>API</option>
                  </select>
                  @error('source')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="direction">Direction <span class="text-danger">*</span></label>
                  <select name="direction" id="direction" class="form-control @error('direction') is-invalid @enderror" required
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                    <option value="IN" {{ $payment->direction == 'IN' ? 'selected' : '' }}>Received (Payment In)</option>
                    <option value="OUT" {{ $payment->direction == 'OUT' ? 'selected' : '' }}>Refund (Payment Out)</option>
                  </select>
                  @error('direction')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="reference">Reference Number</label>
                  <input type="text" name="reference" id="reference"
                    class="form-control @error('reference') is-invalid @enderror"
                    value="{{ old('reference', $payment->reference) }}"
                    placeholder="e.g., Check #, Transaction ID"
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                  @error('reference')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="description">Description/Notes</label>
                  <textarea name="description" id="description" rows="2"
                    class="form-control @error('description') is-invalid @enderror"
                    {{ !$payment->canBeEdited() ? 'disabled' : '' }}>{{ old('description', $payment->description) }}</textarea>
                  @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Attachments --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-paperclip"></i> Attachments</h3>
          </div>
          <div class="card-body">
            {{-- Existing Attachments --}}
            @if($payment->attachments && $payment->attachments->count() > 0)
              <div class="mb-3">
                <label class="d-block mb-2">Current Attachments</label>
                <div class="row">
                  @foreach($payment->attachments as $attachment)
                    <div class="col-md-4 col-sm-6 mb-3" id="attachment-{{ $attachment->id }}">
                      <div class="card h-100">
                        <div class="card-body p-2 text-center">
                          @if(Str::startsWith($attachment->mime_type, 'image/'))
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk($attachment->disk)->url($attachment->path) }}"
                              alt="{{ $attachment->file_name }}"
                              class="img-fluid rounded mb-2" style="max-height: 100px;">
                          @elseif($attachment->mime_type === 'application/pdf')
                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                          @else
                            <i class="fas fa-file fa-3x text-secondary mb-2"></i>
                          @endif
                          <p class="small mb-1 text-truncate" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</p>
                          <small class="text-muted">{{ number_format($attachment->size / 1024, 1) }} KB</small>
                        </div>
                        <div class="card-footer p-1">
                          <button type="button" class="btn btn-sm btn-outline-danger btn-block delete-attachment"
                            data-id="{{ $attachment->id }}" {{ !$payment->canBeEdited() ? 'disabled' : '' }}>
                            <i class="fas fa-trash"></i> Remove
                          </button>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Add New Attachments --}}
            @if($payment->canBeEdited())
              <div class="form-group">
                <label for="attachments">Add More Attachments</label>
                <div class="custom-file">
                  <input type="file" class="custom-file-input @error('attachments.*') is-invalid @enderror"
                    id="attachments" name="attachments[]" multiple
                    accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                  <label class="custom-file-label" for="attachments">Choose file(s)...</label>
                </div>
                <small class="form-text text-muted">Supported: JPG, PNG, GIF, PDF, DOC, DOCX (max 5MB each)</small>
                @error('attachments.*')
                  <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
              <div id="file-preview" class="row mt-2"></div>
            @endif
          </div>
        </div>

        {{-- Current Applications Info --}}
        @if($payment->applications->count() > 0)
          <div class="card ent-card mb-3">
            <div class="card-header bg-info text-white">
              <h3 class="card-title mb-0"><i class="fas fa-link"></i> Current Applications</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th>Document</th>
                    <th>Type</th>
                    <th class="text-right">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($payment->applications as $app)
                    <tr>
                      <td>{{ $app->document_number ?? $app->paymentable_id }}</td>
                      <td><span class="badge badge-info">{{ $app->document_type_label }}</span></td>
                      <td class="text-right">{{ number_format($app->amount, 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr class="bg-light">
                    <th colspan="2">Total Allocated</th>
                    <th class="text-right">{{ number_format($payment->allocatedAmount(), 2) }}</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        @endif
      </div>

      {{-- Sidebar --}}
      <div class="col-lg-4">
        {{-- Status Info --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Status</h3>
          </div>
          <div class="card-body">
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <td>Payment #</td>
                <td class="text-right"><strong>{{ $payment->payment_number }}</strong></td>
              </tr>
              <tr>
                <td>Status</td>
                <td class="text-right">
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
                <td>Created</td>
                <td class="text-right">{{ $payment->created_at->format('M d, Y H:i') }}</td>
              </tr>
              @if($payment->confirmed_at)
                <tr>
                  <td>Confirmed</td>
                  <td class="text-right">{{ $payment->confirmed_at->format('M d, Y H:i') }}</td>
                </tr>
              @endif
            </table>
          </div>
        </div>

        {{-- Amount Summary --}}
        <div class="card ent-card mb-3">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0"><i class="fas fa-calculator"></i> Amount Summary</h3>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span>Current Amount</span>
              <strong>{{ $payment->currency?->symbol }}{{ number_format($payment->amount, 2) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Allocated</span>
              <span class="text-success">{{ $payment->currency?->symbol }}{{ number_format($payment->allocatedAmount(), 2) }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
              <strong>Unallocated</strong>
              <strong class="{{ $payment->unallocated_amount > 0 ? 'text-warning' : 'text-success' }}">
                {{ $payment->currency?->symbol }}{{ number_format($payment->unallocated_amount, 2) }}
              </strong>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="card ent-card">
          <div class="card-body">
            @if($payment->canBeEdited())
              <button type="submit" class="btn btn-primary btn-block btn-lg mb-2">
                <i class="fas fa-save mr-1"></i> Save Changes
              </button>
            @else
              <button type="button" class="btn btn-secondary btn-block btn-lg mb-2" disabled>
                <i class="fas fa-lock mr-1"></i> Cannot Edit
              </button>
            @endif
            <a href="{{ company_route('accounting.payments.show', ['payment' => $payment->id]) }}" class="btn btn-outline-secondary btn-block">
              <i class="fas fa-times mr-1"></i> Cancel
            </a>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  // Initialize Select2 for customer
  $('.select2-customer').select2({
    theme: 'bootstrap4',
    allowClear: true,
    placeholder: 'Search customer...',
    ajax: {
      url: '{{ company_route("customers.search") }}',
      dataType: 'json',
      delay: 250,
      data: function(params) {
        return { q: params.term, page: params.page || 1 };
      },
      processResults: function(data, params) {
        params.page = params.page || 1;
        return {
          results: data.data.map(function(item) {
            return { id: item.id, text: item.display_name || item.name };
          }),
          pagination: { more: data.current_page < data.last_page }
        };
      },
      cache: true
    },
    minimumInputLength: 2
  });

  // Calculate base amount
  function calculateBaseAmount() {
    var amount = parseFloat($('#amount').val()) || 0;
    var rate = parseFloat($('#exchange_rate').val()) || 1;
    var base = amount * rate;
    $('#base_amount_display').text(base.toFixed(2));
  }

  $('#amount, #exchange_rate').on('input change', calculateBaseAmount);

  // File input label update and preview
  $('#attachments').on('change', function() {
    var files = this.files;
    if (!files || files.length === 0) return;

    var label = files.length > 1 ? files.length + ' files selected' : files[0].name;
    $(this).siblings('.custom-file-label').text(label);

    // Preview
    var $preview = $('#file-preview');
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

  // Delete existing attachment
  $('.delete-attachment').on('click', function() {
    var btn = $(this);
    var attachmentId = btn.data('id');

    if (!confirm('Are you sure you want to remove this attachment?')) return;

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
      url: '{{ company_route("accounting.payments.deleteAttachment", ["payment" => $payment->id]) }}',
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        _method: 'DELETE',
        attachment_id: attachmentId
      },
      success: function(response) {
        $('#attachment-' + attachmentId).fadeOut(300, function() { $(this).remove(); });
      },
      error: function(xhr) {
        alert('Failed to delete attachment');
        btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Remove');
      }
    });
  });
});
</script>
@endpush
