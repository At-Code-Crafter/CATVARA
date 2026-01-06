<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice #{{ $order->order_number }}</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  {{-- Using AdminLTE Styles for consistency --}}
  <link rel="stylesheet" href="{{ asset('themes/adminlte/dist/css/adminlte.min.css') }}">
  <style>
    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <section class="invoice p-3 mb-3">
      <!-- title row -->
      <div class="row">
        <div class="col-12">
          <h4>
            <i class="fas fa-globe"></i> {{ $order->company->name ?? 'Company Name' }}
            <small class="float-right">Date: {{ $order->created_at->format('d/m/Y') }}</small>
          </h4>
        </div>
        <!-- /.col -->
      </div>
      <!-- info row -->
      <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
          From
          <address>
            <strong>{{ $order->company->name ?? 'Company Name' }}</strong><br>
            Phone: {{ $order->company->phone ?? '' }}<br>
            Email: {{ $order->company->email ?? '' }}
          </address>
        </div>
        <!-- /.col -->
        <div class="col-sm-4 invoice-col">
          To
          <address>
            <strong>{{ $order->customer->display_name ?? 'Guest' }}</strong><br>
            {{ $order->customer->email ?? '' }}<br>
            {{ $order->customer->phone ?? '' }}
          </address>
        </div>
        <!-- /.col -->
        <div class="col-sm-4 invoice-col">
          <b>Invoice #{{ $order->order_number }}</b><br>
          <br>
          <b>Order ID:</b> {{ $order->uuid }}<br>
          <b>Payment Due:</b> {{ $order->due_date ? $order->due_date->format('d/m/Y') : 'Immediate' }}<br>
          <b>Account:</b> {{ $order->customer->id ?? 'Unregistered' }}
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

      <!-- Table row -->
      <div class="row">
        <div class="col-12 table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($order->items as $item)
                <tr>
                  <td>{{ $item->product_name }}</td>
                  <td>{{ $item->quantity }}</td>
                  <td>{{ number_format($item->unit_price, 2) }}</td>
                  <td>{{ number_format($item->line_total, 2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

      <div class="row">
        <!-- accepted payments column -->
        <div class="col-6">
          <!-- Payment Methods if any -->
        </div>
        <!-- /.col -->
        <div class="col-6">
          <p class="lead">Amount Due {{ $order->due_date ? $order->due_date->format('d/m/Y') : '' }}</p>

          <div class="table-responsive">
            <table class="table">
              <tr>
                <th style="width:50%">Subtotal:</th>
                <td>{{ number_format($order->items->sum('line_total'), 2) }}</td>
              </tr>
              <tr>
                <th>Total:</th>
                <td>{{ number_format($order->items->sum('line_total'), 2) }}</td>
              </tr>
            </table>
          </div>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

      <!-- this row will not appear when printing -->
      <div class="row no-print">
        <div class="col-12">
          <a href="javascript:window.print()" rel="noopener" target="_blank" class="btn btn-default"><i
              class="fas fa-print"></i> Print</a>
        </div>
      </div>
    </section>
  </div>

  <script>
    window.addEventListener("load", window.print());
  </script>
</body>

</html>
