<?php

namespace Modules\OrderManagement\Models;

use Modules\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Modules\PriceRuleManagement\Models\Coupon;
use Modules\PaymentMethod\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Order extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    protected $fillable = [
        'order_number',
        'cart_id',
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
        'payment_status',
        'payment_method_id',
        'payment_ref',
        'transaction_id',
        'order_status',
        'coupon_id',
        'total_coupon_amount',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function addresses()
    {
        return $this->hasMany(OrderAddress::class, 'order_id');
    }

    public function comments()
    {
        return $this->hasMany(OrderComment::class, 'order_id');
    }

    public function paymentStatusOption()
    {
        return $this->belongsTo(PaymentStatusOption::class, 'payment_status_option_id');
    }
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class, 'order_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    public function newPaymentStatusOption()
    {
        return $this->belongsTo(PaymentStatusOption::class);
    }

    public function customerData()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
