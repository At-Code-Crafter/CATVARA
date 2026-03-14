<?php

namespace App\Models\Sales;

use App\Models\Company\Company;
use Illuminate\Database\Eloquent\Model;

class OrderItemBox extends Model
{
    protected $fillable = [
        'company_id',
        'order_id',
        'delivery_note_id',
        'order_item_id',
        'box_number',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:6',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
