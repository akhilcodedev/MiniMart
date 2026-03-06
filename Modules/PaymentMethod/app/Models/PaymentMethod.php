<?php

namespace Modules\PaymentMethod\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\OrderManagement\Models\Order;

// use Modules\PaymentMethod\Database\Factories\PaymentMethodFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;
    const ACTIVE_STATUS_LIST = [
        self::ACTIVE_YES => 'Active',
        self::ACTIVE_NO => 'Inactive',
    ];

    const ONLINE_YES = 1;
    const ONLINE_NO = 0;
    const ONLINE_STATUS_LIST = [
        self::ONLINE_YES => 'Online',
        self::ONLINE_NO => 'Offline',
    ];

    const TEST_MODE_YES = 1;
    const TEST_MODE_NO = 0;
    const TEST_MODE_STATUS_LIST = [
        self::TEST_MODE_YES => 'Test Mode',
        self::TEST_MODE_NO => 'Live Mode',
    ];

    const PAYMENT_METHOD_CODE_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_CODE_PAYPAL = 'paypal';
    const PAYMENT_METHOD_CODE_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_CODE_STRIPE = 'stripe';
    const PAYMENT_METHOD_CODE_TELR = 'telr';
    const PAYMENT_METHOD_CODE_LIST = [
        self::PAYMENT_METHOD_CODE_CREDIT_CARD => 'Credit Card',
        self::PAYMENT_METHOD_CODE_PAYPAL => 'PayPal',
        self::PAYMENT_METHOD_CODE_BANK_TRANSFER => 'Bank Transfer',
        self::PAYMENT_METHOD_CODE_STRIPE => 'Stripe',
        self::PAYMENT_METHOD_CODE_TELR => 'Telr',
    ];
    const PAYMENT_METHOD_ONLINE_CODE_LIST = [
        self::PAYMENT_METHOD_CODE_PAYPAL => 'PayPal',
        self::PAYMENT_METHOD_CODE_STRIPE => 'Stripe',
        self::PAYMENT_METHOD_CODE_TELR => 'Telr',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_methods';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'code',
        'name',
        'image',
        'sort_order',
        'description',
        'credentials',
        'test_mode',
        'is_online',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    public function attributes()
    {
        return $this->hasMany(PaymentMethodAttribute::class, 'payment_method_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
