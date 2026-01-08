@extends('theme.adminlte.layouts.app')

@section('title', 'New Sales Order')

@section('content_header')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">New Sales Order</h1>
    </div>
  </div>
@endsection

@section('css')
  <style>
    /* Wizard / Stepper Styles */
    .wizard-steps {
      display: flex;
      justify-content: center;
      margin-bottom: 2rem;
      position: relative;
      z-index: 1;
    }

    .wizard-step {
      width: 15rem;
      text-align: center;
      position: relative;
      opacity: 0.6;
      transition: all 0.3s ease;
    }

    .wizard-step.active {
      opacity: 1;
      transform: scale(1.05);
    }

    .wizard-step.completed .step-icon {
      background: #10b981;
      /* Green */
      color: white;
      border-color: #10b981;
    }

    .wizard-step.active .step-icon {
      background: #3b82f6;
      /* Blue */
      color: white;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
      border-color: #3b82f6;
    }

    .step-icon {
      width: 3.5rem;
      height: 3.5rem;
      border-radius: 50%;
      background: #fff;
      border: 2px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      font-weight: 700;
      color: #64748b;
      margin: 0 auto 0.75rem;
      position: relative;
      z-index: 2;
      transition: all 0.3s ease;
    }

    .step-label {
      font-weight: 600;
      color: #1e293b;
      font-size: 0.95rem;
    }

    .step-desc {
      font-size: 0.75rem;
      color: #64748b;
      display: block;
      margin-top: 2px;
    }

    /* Connecting Lines */
    .wizard-steps::before {
      content: '';
      position: absolute;
      top: 1.75rem;
      left: 50%;
      transform: translateX(-50%);
      width: 60%;
      height: 2px;
      background: #e2e8f0;
      z-index: 0;
    }

    /* Content Area */
    .wizard-content {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      border: 1px solid #f1f5f9;
      overflow: hidden;
      min-height: 60vh;
    }

    /* Ultra Enterprise Inputs */
    .form-label-group {
      position: relative;
      margin-bottom: 1.5rem;
    }

    .form-control-lg {
      border-radius: 0.75rem;
      padding: 1rem 1.25rem;
      font-size: 1rem;
      height: auto;
      border-color: #e2e8f0;
      background-color: #f8fafc;
      transition: all 0.2s;
    }

    .form-control-lg:focus {
      background-color: #fff;
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .btn-next {
      background: #0f172a;
      color: #fff;
      border-radius: 0.75rem;
      padding: 0.75rem 2rem;
      font-weight: 600;
      transition: all 0.2s;
      border: 1px solid #0f172a;
    }

    .btn-next:hover {
      background: #1e293b;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
      color: #fff;
    }

    .glass-header {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid #f1f5f9;
      padding: 1.5rem;
    }
  </style>
  @yield('wizard-css')
@endsection

@section('content')
  <div class="container-fluid pb-5">

    <!-- Wizard Stepper -->
    <div class="wizard-steps">
      @php
        $currentRoute = Route::currentRouteName();
        $step1Active = str_contains($currentRoute, 'step1');
        $step2Active = str_contains($currentRoute, 'step2');
        $step3Active = str_contains($currentRoute, 'step3');

        $step1Done = $step2Active || $step3Active;
        $step2Done = $step3Active;
      @endphp

      <div class="wizard-step {{ $step1Active ? 'active' : '' }} {{ $step1Done ? 'completed' : '' }}">
        <div class="step-icon">
          @if ($step1Done)
            <i class="fas fa-check"></i>
          @else
            1
          @endif
        </div>
        <div class="step-label">Select Customer</div>
        <span class="step-desc">Who's buying?</span>
      </div>

      <div class="wizard-step {{ $step2Active ? 'active' : '' }} {{ $step2Done ? 'completed' : '' }}">
        <div class="step-icon">
          @if ($step2Done)
            <i class="fas fa-check"></i>
          @else
            2
          @endif
        </div>
        <div class="step-label">Add Products</div>
        <span class="step-desc">Build the order</span>
      </div>

      <div class="wizard-step {{ $step3Active ? 'active' : '' }}">
        <div class="step-icon">3</div>
        <div class="step-label">Review & Pay</div>
        <span class="step-desc">Finalize details</span>
      </div>
    </div>

    <!-- Wizard Content -->
    <div class="wizard-content">
      @yield('wizard-content')
    </div>

  </div>
@endsection
