<?php

namespace Modules\PriceRuleManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponMode extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    const COUPON_MODE_AMOUNT = 'amount';
    const COUPON_MODE_PERCENTAGE = 'percentage';

    const COUPON_MODE_LIST = [
        self::COUPON_MODE_AMOUNT => "Amount Discount",
        self::COUPON_MODE_PERCENTAGE => "Percent Discount",
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coupon_modes';

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
