<?php

namespace App\Models\Sales;

use App\Models\Common\Address;
use App\Models\Company\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    use SoftDeletes;

    const STATUS_SHIPPED = 'SHIPPED';
    const STATUS_DELIVERED = 'DELIVERED';

    protected $fillable = [
        'uuid',
        'company_id',
        'order_id',
        'inventory_location_id',
        'delivery_note_number',
        'status',
        'reference_number',
        'vehicle_number',
        'notes',
        'shipped_at',
        'delivered_at',
        'created_by',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function boxItems()
    {
        return $this->hasMany(OrderItemBox::class);
    }

    public function inventoryLocation()
    {
        return $this->belongsTo(\App\Models\Inventory\InventoryLocation::class);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function billingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'BILLING');
    }

    public function shippingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'SHIPPING');
    }
}
