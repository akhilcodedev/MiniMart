<?php

namespace Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderStatus extends Model
{
    use HasFactory;

    const ORDER_STATUS_PENDING = 1;
    const ORDER_STATUS_PROCESSING = 2;
    const ORDER_STATUS_PENDING_PAYMENT = 3;
    const ORDER_STATUS_PAYMENT_REVIEW = 4;
    const ORDER_STATUS_HOLD = 5;
    const ORDER_STATUS_COMPLETE = 6;
    const ORDER_STATUS_CANCELED = 7;
    const ORDER_STATUS_RETURNED = 8;
    const ORDER_STATUS_FRAUD = 9;
    const ORDER_STATUS_CLOSED = 10;

    const ORDER_STATUS_LIST = [
        self::ORDER_STATUS_PENDING => 'Pending',
        self::ORDER_STATUS_PROCESSING => 'Processing',
        self::ORDER_STATUS_PENDING_PAYMENT => 'Pending Payment',
        self::ORDER_STATUS_PAYMENT_REVIEW => 'Payment Review',
        self::ORDER_STATUS_HOLD => 'Hold',
        self::ORDER_STATUS_COMPLETE => 'Completed',
        self::ORDER_STATUS_CANCELED => 'Canceled',
        self::ORDER_STATUS_RETURNED => 'Returned',
        self::ORDER_STATUS_FRAUD => 'Fraud',
        self::ORDER_STATUS_CLOSED => 'Closed',
    ];



    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['status', 'label'];

    protected $table = "order_status";


}
