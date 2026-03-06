<?php

namespace Modules\PaymentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    const PAYMENT_METHOD_CODE_STRIPE = 'stripe';
    const PAYMENT_METHOD_CODE_LIST = [
        self::PAYMENT_METHOD_CODE_STRIPE => 'Stripe',
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

}
