<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Customer\Models\Customer;
use function Carbon\this;

// use Modules\Cart\Database\Factories\CartFactory;

class Cart extends Model
{
    use HasFactory;

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */

//    protected $fillable = [
//      'customer_id',
//      'guest_fingerprint_id',
//      'shipment_method_id',
//      //'shipment_method',
//      'shipping_cost',
//      'is_active'
//    ];

    protected $fillable = [
        'id',
        'customer_id',
        'customer_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'is_active',
        'profile_path',
        'guest_fingerprint_code',
        'guest_first_name',
        'guest_last_name',
        'guest_email',
        'guest_phone',
        'guest_password',
        'guest_is_active',
        'shipping_method_name',
        'shipping_method_code',
        'shipping_method_status',
        'shipping_attribute_name',
        'shipping_attribute_type',
        'shipping_attribute_value',
        'shipping_attribute_sort_order',
        'shipping_cost',
        'coupon_id',
        'total_coupon_amount',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }



    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }


    public function addresses(){
        return $this->hasMany(CartAddress::class,'cart_id');
    }

}
