@forelse($products as $p)
  <div class="col-6 col-md-4 col-lg-3 mb-4">
    <div class="card h-100 product-card shadow-sm border-0"
      onclick='openProductModal("{{ $p->id }}", "{{ $p->name }}", "{{ $p->sku }}", @json($p->variants))'
      style="cursor: pointer; transition: transform 0.2s;">

      <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 140px;">
        @if ($p->main_image)
          <img src="{{ $p->main_image }}" class="img-fluid" style="max-height: 100%;">
        @else
          <i class="fas fa-box fa-3x text-black-50"></i>
        @endif
      </div>

      <div class="card-body p-3 d-flex flex-column">
        <h6 class="card-title font-weight-bold mb-1 text-truncate" title="{{ $p->name }}">
          {{ $p->name }}
        </h6>
        <div class="small text-muted mb-2">{{ $p->sku }}</div>

        <div class="mt-auto d-flex justify-content-between align-items-center">
          <span class="font-weight-bold text-primary">
            @if ($p->variants->isNotEmpty())
              {{ number_format($p->variants->min('price'), 2) }}
            @else
              -
            @endif
          </span>
          <i class="fas fa-plus-circle text-primary"></i>
        </div>
      </div>
    </div>
  </div>
@empty
  <div class="col-12 text-center py-5">
    <div class="text-muted">No products found matching your search.</div>
  </div>
@endforelse
