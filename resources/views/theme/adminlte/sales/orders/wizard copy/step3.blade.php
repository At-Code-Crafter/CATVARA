@extends('theme.adminlte.sales.orders.wizard.layout')

@section('wizard-content')
  <form action="{{ route('company.sales-orders.wizard.storeStep3', $order->uuid) }}" method="POST">
    @csrf

    <div class="p-5">
      <div class="text-center mb-5">
        <h2 class="font-weight-bold text-dark">Review & Finalize</h2>
        <p class="text-muted">Please confirm the details below before placing the order.</p>
      </div>

      <div class="row">
        <!-- Order Details -->
        <div class="col-md-8">
          <div class="card shadow-sm border-0 rounded-lg mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
              <h5 class="font-weight-bold mb-0">Order Items</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                      <th class="pl-4 border-0">Product</th>
                      <th class="text-center border-0">Qty</th>
                      <th class="text-right border-0">Unit Price</th>
                      <th class="text-right pr-4 border-0">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($order->items as $item)
                      <tr>
                        <td class="pl-4 align-middle">
                          <div class="font-weight-bold text-dark">{{ $item->item_name }}</div>
                          <small class="text-muted">{{ $item->item_code }}</small>
                        </td>
                        <td class="text-center align-middle">
                          <span class="badge badge-light border px-2">{{ $item->quantity }}</span>
                        </td>
                        <td class="text-right align-middle">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right pr-4 align-middle font-weight-bold">
                          ${{ number_format($item->sub_total, 2) }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="text-center p-4">No items found.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body p-4">
              <div class="form-group">
                <label class="font-weight-bold text-muted small text-uppercase">Internal Notes / Instructions</label>
                <textarea name="notes" class="form-control bg-light border-0 rounded-lg p-3" rows="3"
                  placeholder="Add any special instructions here...">{{ $order->notes }}</textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary & Actions -->
        <div class="col-md-4">
          <div class="card shadow-sm border-0 rounded-lg mb-4">
            <div class="card-body p-4 bg-light">
              <h6 class="font-weight-bold text-dark mb-4">Payment & Shipping</h6>

              <div class="form-group mb-4">
                <label class="small font-weight-bold text-muted">Payment Terms</label>
                <select name="payment_term_id" class="form-control shadow-sm border-0 h-auto py-2" required>
                  <option value="">Select Term...</option>
                  @foreach ($paymentTerms as $term)
                    <option value="{{ $term->id }}" {{ $order->payment_term_id == $term->id ? 'selected' : '' }}>
                      {{ $term->name }} ({{ $term->due_days }} Days)
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="form-group mb-4">
                <label class="small font-weight-bold text-muted">Shipping Cost</label>
                <div class="input-group shadow-sm border-0">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-0">$</span>
                  </div>
                  <input type="number" name="shipping_cost" class="form-control border-0"
                    value="{{ $order->shipping_total }}" step="0.01">
                </div>
              </div>

              <hr class="my-4 border-muted">

              <div class="d-flex justify-content-between mb-2 text-muted">
                <span>Subtotal</span>
                <span>${{ number_format($order->subtotal, 2) }}</span>
              </div>
              <div class="d-flex justify-content-between mb-4 pb-2 border-bottom">
                <span>Shipping</span>
                <span>Calculated at invoice</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="h5 font-weight-bold text-dark mb-0">Total</span>
                <span class="h3 font-weight-bold text-primary mb-0">${{ number_format($order->grand_total, 2) }}</span>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" name="action" value="save"
                  class="btn btn-dark btn-block btn-lg font-weight-bold py-3 shadow-sm rounded-lg">
                  <i class="fas fa-save mr-2"></i> Confirm Order
                </button>
                <button type="submit" name="action" value="invoice"
                  class="btn btn-outline-primary btn-block btn-lg font-weight-bold py-3 border-2 rounded-lg mt-3">
                  <i class="fas fa-file-invoice mr-2"></i> Generate Invoice
                </button>
              </div>

              <div class="text-center mt-3">
                <a href="{{ route('company.sales-orders.wizard.step2', $order->uuid) }}" class="text-muted small">Back to
                  Items</a>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection
