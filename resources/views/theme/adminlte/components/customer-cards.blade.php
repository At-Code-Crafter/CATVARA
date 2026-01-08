  @foreach ($customers as $c)
    @php

      $primaryAddress = $c->address ?: $c->addresses->first()?->address_line_1 ?? null;
      $addressLine = $primaryAddress ?: 'No address on file';
      $country = $c->country->name ?? null;
      $state = $c->state->name ?? null;

      $logoUrl = $c->logo_url ?? null;
    @endphp
    <div class="customer-card js-pick-customer" data-name="{{ e($c->display_name) }}">
      <div class="cust-logo">
        @if ($logoUrl)
          <img src="{{ $logoUrl }}" alt="{{ e($c->display_name) }}">
        @else
          {{ $c->initials ?: '??' }}
        @endif
      </div>

      <div class="cust-meta">
        <div class="cust-name">{{ $c->display_name }}</div>
        <div class="cust-address">
          {{ $addressLine }}
          @if ($state || $country)
            <br><span class="text-muted">{{ $state ? $state . ', ' : '' }}{{ $country }}</span>
          @endif
        </div>
        <div class="cust-sub">
          {{ $c->email ?: '—' }} @if ($c->phone)
            • {{ $c->phone }}
          @endif
        </div>
      </div>

      <div class="cust-cta">
        <a href="{{ company_route('pos.orders.create', ['customer' => $c->uuid]) }}"
          class="btn btn-outline-primary btn-sm btn-ent select-customer" data-id="{{ $c->uuid }}">
          Select
        </a>
      </div>
    </div>
  @endforeach
