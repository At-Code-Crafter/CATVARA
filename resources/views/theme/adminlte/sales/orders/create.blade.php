@extends('theme.adminlte.layouts.app')

@section('title', 'New Sales Order - Select Customer')

@section('content-header')
  <link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-lg-8">
      <h1 class="m-0">
        <i class="fas fa-file-invoice mr-2 text-primary"></i> New Sales Order
      </h1>
      <div class="text-muted">Step 1: Select Customer</div>
    </div>
  </div>
@endsection

@section('content')
  <div class="container-fluid pos-shell">

    <div class="card ent-card">
      {{-- WIZARD HEADER --}}
      <div class="card-header p-0 pt-3 border-bottom-0">
        <ul class="nav nav-tabs pos-steps" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" href="#">
              <i class="fas fa-user mr-2"></i> 1. Customer
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="#">
              <i class="fas fa-cubes mr-2"></i> 2. Products
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="#">
              <i class="fas fa-file-invoice-dollar mr-2"></i> 3. Terms
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="#">
              <i class="fas fa-check-circle mr-2"></i> 4. Preview
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body p-4">
        {{-- STEP 1 CONTENT --}}
        <div class="customer-picker">

          <div class="text-center mb-4">
            <h3 class="font-weight-bold mb-1">Select Customer</h3>
            <div class="text-muted">Pick from quick cards or search to continue.</div>
          </div>

          {{-- SEARCH + QUICK FILTER --}}
          <div class="customer-searchbar mb-3">
            <div class="row align-items-end">
              <div class="col-md-8">
                <label class="text-muted small font-weight-bold text-uppercase mb-1">Search Customer <small
                    class="text-muted ">
                    (Search by Name, Email, or Phone.)
                  </small></label>
                <select class="form-control ent-control select2" id="customer_id" style="width: 100%;"></select>
              </div>

              <div class="col-md-4">
                <label class="text-muted small font-weight-bold text-uppercase mb-1">Quick Filter Cards</label>
                <input type="text" id="customer_card_filter" class="form-control ent-control"
                  placeholder="Type to filter cards...">
              </div>
            </div>
          </div>

          {{-- QUICK CUSTOMER CARDS --}}
          <div class="customer-grid" id="customer-cards">
            <div class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
              </div>
            </div>
          </div>

          {{-- FORM TO SUBMIT --}}
          <form id="customer-form" action="{{ company_route('sales.orders.store.customer') }}" method="POST"
            style="display:none;">
            @csrf
            <input type="hidden" name="customer_id" id="selected_customer_id">
          </form>

        </div>
      </div>
    </div>

  </div>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {

      // 1. Load Customer Cards
      $.ajax({
        url: '{{ company_route('sales.orders.load.customers') }}',
        type: 'GET',
        success: function(response) {
          $('#customer-cards').html(response.view);
        },
        error: function() {
          $('#customer-cards').html('<div class="text-center text-danger">Failed to load customers.</div>');
        }
      });

      // 2. Initialize Select2 for searching
      $('#customer_id').select2({
        placeholder: 'Search for a customer...',
        ajax: {
          url: '{{ company_route('sales.orders.search.customers') }}',
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              term: params.term, // search term
            };
          },
          processResults: function(data) {
            return {
              results: data.results
            };
          },
          cache: true
        },
        minimumInputLength: 1,
        theme: 'bootstrap4',
      });

      // 3. Handle Select2 Selection
      $('#customer_id').on('select2:select', function(e) {
        var data = e.params.data;
        submitCustomer(data.id);
      });

      // 4. Handle Card Selection (Delegated event)
      $(document).on('click', '.select-customer', function(e) {
        e.preventDefault();
        var custId = $(this).data('id');
        submitCustomer(custId);
      });

      // 5. Filter Cards
      $('#customer_card_filter').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $(".customer-card").filter(function() {
          $(this).toggle($(this).attr('data-name').toLowerCase().indexOf(value) > -1)
        });
      });

      function submitCustomer(uuid) {
        // Show full page loader if needed
        $('#selected_customer_id').val(uuid);
        $('#customer-form').submit();
      }

    });
  </script>
@endpush
