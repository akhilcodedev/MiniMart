<?php

namespace Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderShipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'shipping_method_id', 'store_id', 'address',
        'city', 'postal_code', 'country',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
