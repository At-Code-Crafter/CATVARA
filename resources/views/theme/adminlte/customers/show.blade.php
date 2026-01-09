@extends('theme.adminlte.layouts.app')

@section('title', 'Customer Details')

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-6">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-user-circle"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">{{ $customer->display_name }}</h1>
          <div class="text-muted small">
            View details, orders and activity.
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 d-flex justify-content-end" style="gap:10px;">
      <a href="{{ route('customers.edit', [$company->uuid, $customer->id]) }}" class="btn btn-primary btn-ent">
        <i class="fas fa-edit mr-1"></i> Edit Customer
      </a>
      <a href="{{ route('customers.index', $company->uuid) }}" class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')
  {{-- Stats Row --}}
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $stats['orders_count'] }}</h3>
          <p>Total Orders</p>
          <small class="d-block">Draft: {{ $stats['orders_draft'] }} | Completed: {{ $stats['orders_completed'] }}</small>
        </div>
        <div class="icon">
          <i class="fas fa-shopping-bag"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $stats['invoices_paid'] }}</h3>
          <p>Paid Invoices</p>
           <small class="d-block">Unpaid: {{ $stats['invoices_unpaid'] }}</small>
        </div>
        <div class="icon">
          <i class="fas fa-file-invoice-dollar"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>${{ number_format($stats['total_spent'], 2) }}</h3>
          <p>Total Spent</p>
           <small class="d-block">&nbsp;</small>
        </div>
        <div class="icon">
          <i class="fas fa-hand-holding-usd"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>${{ number_format($stats['total_overdue'], 2) }}</h3>
          <p>Total Overdue</p>
           <small class="d-block">&nbsp;</small>
        </div>
        <div class="icon">
          <i class="fas fa-exclamation-circle"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    {{-- LEFT SIDEBAR --}}
    <div class="col-lg-3">

      {{-- Profile Card --}}
      <div class="card ent-card mb-3">
        <div class="card-body text-center">
            <div class="mb-3">
                <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 80px; height: 80px; font-size: 2.5rem; color: var(--ent-primary);">
                    {{ $customer->initials }}
                </span>
            </div>
            <h5 class="font-weight-bold mb-1">{{ $customer->display_name }}</h5>
            <div class="mb-3">
                @if($customer->type === 'COMPANY')
                    <span class="badge badge-primary"><i class="fas fa-building mr-1"></i> Company</span>
                @else
                    <span class="badge badge-secondary"><i class="fas fa-user mr-1"></i> Individual</span>
                @endif
                
                @if($customer->is_active)
                    <span class="badge badge-success ml-1">Active</span>
                @else
                    <span class="badge badge-danger ml-1">Inactive</span>
                @endif
            </div>
             
             {{-- Legal Name if available and different from display --}}
             @if($customer->legal_name && $customer->legal_name !== $customer->display_name)
                <div class="small text-muted mb-3">
                   <i class="fas fa-briefcase mr-1"></i> {{ $customer->legal_name }}
                </div>
             @endif

            <ul class="list-group list-group-unbordered text-left mt-4 mb-3">
                <li class="list-group-item px-0">
                    <strong>Payment Term</strong> 
                    <span class="float-right text-muted">{{ $customer->paymentTerm->name ?? 'None' }}</span>
                </li>
                 <li class="list-group-item px-0">
                    <strong>Tax Number</strong> 
                    <span class="float-right text-muted">{{ $customer->tax_number ?? '—' }}</span>
                </li>
            </ul>
        </div>
      </div>

      {{-- Contact Info --}}
      <div class="card ent-card mb-3">
        <div class="card-header border-0">
          <h3 class="card-title font-weight-bold">Contact Details</h3>
        </div>
        <div class="card-body pt-0">
            <div class="mb-3">
                <label class="small text-uppercase text-muted">Email</label>
                <div>
                     @if($customer->email)
                        <a href="mailto:{{ $customer->email }}" class="d-block text-truncate">{{ $customer->email }}</a>
                     @else
                        <span class="text-muted">—</span>
                     @endif
                </div>
            </div>
            
            <div class="mb-3">
                 <label class="small text-uppercase text-muted">Phone</label>
                 <div>
                     @if($customer->phone)
                        <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                     @else
                        <span class="text-muted">—</span>
                     @endif
                 </div>
            </div>
             
             @if($customer->legal_name)
                 <div class="mb-3">
                     <label class="small text-uppercase text-muted">Legal Name</label>
                     <div>{{ $customer->legal_name }}</div>
                 </div>
             @endif
        </div>
      </div>
      
       {{-- Address --}}
      <div class="card ent-card mb-3">
        <div class="card-header border-0">
          <h3 class="card-title font-weight-bold">Address</h3>
        </div>
        <div class="card-body pt-0">
             @if($customer->address)
                <div class="mb-3 p-2 rounded bg-light border">
                    <div class="small">
                        {{ $customer->address->address_line_1 }}
                        @if($customer->address->address_line_2)<br>{{ $customer->address->address_line_2 }}@endif
                         <br>
                        @if($customer->address->city){{ $customer->address->city }}, @endif {{ $customer->address->state?->name }} {{ $customer->address->zip_code }}
                        @if($customer->address->country)<br>{{ $customer->address->country->name }}@endif
                    </div>
                </div>
             @else
                <p class="text-muted small">No address found.</p>
             @endif
        </div>
      </div>
      
      {{-- Internal Notes --}}
      <div class="card ent-card mb-3">
        <div class="card-header border-0">
          <h3 class="card-title font-weight-bold">Internal Notes</h3>
        </div>
        <div class="card-body pt-0">
            @if($customer->notes)
                <div class="p-3 bg-light rounded text-muted small border-left-primary" style="font-style:italic; border-left: 3px solid var(--ent-primary);">
                    {!! nl2br(e($customer->notes)) !!}
                </div>
            @else
                <p class="text-muted small mb-0">No internal notes.</p>
            @endif
        </div>
      </div>

    </div>

    {{-- RIGHT CONTENT (TABS) --}}
    <div class="col-lg-9">
      <div class="card card-primary card-outline card-outline-tabs ent-card">
        <div class="card-header p-0 border-bottom-0">
          <ul class="nav nav-tabs" id="customerTabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="orders-tab" data-toggle="tab" href="#orders" role="tab" aria-controls="orders" aria-selected="true">
                  <i class="fas fa-shopping-cart mr-1"></i> Orders
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="invoices-tab" data-toggle="tab" href="#invoices" role="tab" aria-controls="invoices" aria-selected="false">
                   <i class="fas fa-file-invoice-dollar mr-1"></i> Invoices
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="payments-tab" data-toggle="tab" href="#payments" role="tab" aria-controls="payments" aria-selected="false">
                  <i class="fas fa-money-bill-wave mr-1"></i> Payments
              </a>
            </li>
             <li class="nav-item">
              <a class="nav-link" id="ledger-tab" data-toggle="tab" href="#ledger" role="tab" aria-controls="ledger" aria-selected="false">
                  <i class="fas fa-book mr-1"></i> Ledger
              </a>
            </li>
          </ul>
        </div>
        
        <div class="card-body">
          <div class="tab-content" id="customerTabsContent">
            
            {{-- ORDERS TAB --}}
            <div class="tab-pane fade show active" id="orders" role="tabpanel" aria-labelledby="orders-tab">
               <div class="d-flex justify-content-between align-items-center mb-3">
                   <h5 class="mb-0">All Orders</h5>
                   <!-- Optional: Add Create Order button here if needed in future -->
               </div>
               
               <div class="table-responsive">
                   <table class="table table-hover table-striped">
                       <thead>
                           <tr>
                               <th>Date</th>
                               <th>Order #</th>
                               <th>Status</th>
                               <th class="text-right">Total</th>
                               <th class="text-right">Items</th>
                               <th></th>
                           </tr>
                       </thead>
                       <tbody>
                           @forelse($customer->orders as $order)
                               <tr>
                                   <td>{{ $order->created_at->format('d M Y') }}</td>
                                   <td>
                                       <a href="#" class="font-weight-bold">
                                           {{ $order->order_number }}
                                       </a>
                                   </td>
                                   <td>
                                        <!-- Assuming status relationship exists or falling back to string -->
                                        <span class="badge badge-light border">
                                            {{ $order->status->name ?? $order->status_id }}
                                        </span>
                                   </td>
                                   <td class="text-right">
                                       {{ $order->currency->symbol ?? '$' }}{{ number_format($order->grand_total, 2) }}
                                   </td>
                                   <td class="text-right">
                                       {{ $order->items_count ?? $order->items->count() }}
                                   </td>
                                   <td class="text-right">
                                       <a href="#" class="btn btn-sm btn-light">View</a>
                                   </td>
                               </tr>
                           @empty
                               <tr>
                                   <td colspan="6" class="text-center py-5 text-muted">
                                       <i class="fas fa-shopping-basket fa-3x mb-3 text-light-gray"></i><br>
                                       No orders found for this customer.
                                   </td>
                               </tr>
                           @endforelse
                       </tbody>
                   </table>
               </div>
            </div>

            {{-- INVOICES TAB --}}
            <div class="tab-pane fade" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                 <div class="text-center py-5 text-muted">
                    <i class="fas fa-file-invoice fa-3x mb-3 text-light-gray"></i>
                    <p>Invoices module integration coming soon.</p>
                 </div>
            </div>

            {{-- PAYMENTS TAB --}}
            <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                 <div class="text-center py-5 text-muted">
                    <i class="fas fa-money-check-alt fa-3x mb-3 text-light-gray"></i>
                    <p>Payments history coming soon.</p>
                 </div>
            </div>

            {{-- LEDGER TAB --}}
            <div class="tab-pane fade" id="ledger" role="tabpanel" aria-labelledby="ledger-tab">
                 <div class="text-center py-5 text-muted">
                    <i class="fas fa-book-open fa-3x mb-3 text-light-gray"></i>
                    <p>Customer ledger coming soon.</p>
                 </div>
            </div>

          </div>
        </div>
        <!-- /.card -->
      </div>
    </div>
  </div>
@endsection
