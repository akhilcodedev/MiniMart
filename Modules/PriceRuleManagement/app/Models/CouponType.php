<?php

namespace Modules\PriceRuleManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponType extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    const COUPON_TYPE_MANUAL = 'manual';
    const COUPON_TYPE_PROMOTIONAL = 'promotional';
    const COUPON_TYPE_INAUGURAL = 'inaugural';
    const COUPON_TYPE_FIRST_TIME_USER = 'first_time_user';
    const COUPON_TYPE_CASHBACK = 'cashback';

    const COUPON_TYPE_LIST = [
        self::COUPON_TYPE_MANUAL => "Manual Type",
        self::COUPON_TYPE_PROMOTIONAL => "Promotional",
        self::COUPON_TYPE_INAUGURAL => "Inaugural",
        self::COUPON_TYPE_FIRST_TIME_USER => "First Time (User)",
        self::COUPON_TYPE_CASHBACK => "Cashback",
    ];

    const COUPON_TYPE_ONE_USE_LIST = [
        self::COUPON_TYPE_INAUGURAL,
        self::COUPON_TYPE_FIRST_TIME_USER,
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coupon_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Fetches the User data who executed the creation.
     *
     * @return BelongsTo
     */
    public function createdUser() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Fetches the User data who executed the update.
     *
     * @return BelongsTo
     */
    public function updatedUser() {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

}
