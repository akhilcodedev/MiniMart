<?php

namespace Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Cart\Models\CartAddress;


class OrderAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id','customer_id', 'first_name', 'last_name', 'email', 'phone',
        'address_line1', 'address_line2', 'locality', 'city', 'state',
        'postal_code', 'country', 'type', 'is_default',
    ];

    public function addresses()
    {
        return $this->hasMany(CartAddress::class, 'cart_id');
    }

}
