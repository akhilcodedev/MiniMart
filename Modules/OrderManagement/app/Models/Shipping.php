<?php

namespace Modules\OrderManagement\Models;

use Modules\Cart\Models\CartAddress;
use Modules\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends Model
{
    use HasFactory;

    protected $table = 'shipping';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'customer_id',
        'total_amount',
        'status',
    ];



    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function addresses()
    {
        return $this->hasMany(CartAddress::class, 'cart_id');
    }

}
