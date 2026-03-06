<?php

namespace Modules\PaymentMethod\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Models\Customer;
use Modules\OrderManagement\Models\Order;

class PaymentHistory extends Model
{
    use HasFactory;

    const PAYMENT_STATUS_FAILURE = 0;
    const PAYMENT_STATUS_SUCCESS = 1;
    const PAYMENT_STATUS_LIST = [
        self::PAYMENT_STATUS_FAILURE => 'Failed',
        self::PAYMENT_STATUS_SUCCESS => 'Success',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'amount',
        'currency_code',
        'payment_ref',
        'transaction_id',
        'customer_id',
        'order_id',
        'payment_method_id',
        'type',
        'date',
        'payment_detail',
        'comments',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    public function customerData() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function orderData() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function paymentMethodData() {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

}
