@forelse($order->items as $item)
  <tr>
    <td class="pl-3 py-3">
      <div class="font-weight-bold text-truncate" style="max-width: 140px;" title="{{ $item->name }}">
        {{ $item->name }}
      </div>
      <div class="small text-muted">{{ number_format($item->price, 2) }} / unit</div>
    </td>
    <td class="text-center align-middle">
      <input type="number" class="form-control form-control-sm text-center px-1 cart-qty" data-id="{{ $item->id }}"
        value="{{ $item->quantity }}" min="1" style="width: 50px; margin: 0 auto;">
    </td>
    <td class="text-right pr-3 align-middle font-weight-bold">
      {{ number_format($item->total, 2) }}
    </td>
    <td class="text-center align-middle">
      <a href="javascript:void(0)" class="text-danger remove-item" data-id="{{ $item->id }}">
        <i class="fas fa-times"></i>
      </a>
    </td>
  </tr>
@empty
  <tr>
    <td colspan="4" class="text-center py-4 text-muted">
      <i class="fas fa-shopping-basket fa-2x mb-2 d-block opacity-50"></i>
      Cart is empty
    </td>
  </tr>
@endforelse
