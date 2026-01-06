@extends('theme.adminlte.layouts.app')

@section('content-header')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">Sales Orders</h1>
    </div>
    <div class="col-sm-6 text-right">
      <a href="{{ company_route('sales-orders.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> New Sales Order
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card card-outline card-primary shadow-sm border-0">
        <div class="card-body">
          <table class="table table-hover" id="orders-table">
            <thead class="bg-light">
              <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Total</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($orders ?? [] as $order)
                <tr>
                  <td class="font-weight-bold">{{ $order->order_number }}</td>
                  <td>{{ $order->created_at->format('M d, Y') }}</td>
                  <td>{{ $order->customer->display_name ?? 'N/A' }}</td>
                  <td>
                    <span class="badge badge-{{ $order->status->code == 'CONFIRMED' ? 'success' : 'secondary' }}">
                      {{ $order->status->name }}
                    </span>
                  </td>
                  <td class="font-weight-bold">{{ number_format($order->total_amount, 2) }}</td>
                  <td class="text-right">
                    <a href="{{ company_route('sales-orders.show', ['sales_order' => $order->id]) }}"
                      class="btn btn-sm btn-info" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ company_route('sales-orders.print', ['order' => $order->id]) }}"
                      class="btn btn-sm btn-default" title="Print" target="_blank">
                      <i class="fas fa-print"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">No sales orders found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      // Initializing simple DataTable if many rows
      if ($('#orders-table tbody tr').length > 5) {
        $('#orders-table').DataTable({
          "responsive": true,
          "autoWidth": false,
          "order": [
            [1, "desc"]
          ]
        });
      }
    });
  </script>
@endpush
