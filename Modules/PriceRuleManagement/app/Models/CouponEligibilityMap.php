<?php

namespace Modules\PriceRuleManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponEligibilityMap extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    const ELIGIBILITY_CODE_CUSTOMER = 'customer';
    const ELIGIBILITY_CODE_REGION = 'region';

    const ELIGIBILITY_CODE_LIST = [
        self::ELIGIBILITY_CODE_CUSTOMER => 'Customer',
        /*self::ELIGIBILITY_CODE_REGION => 'Region',*/
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coupon_eligibility_maps';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'coupon_id',
        'eligible_code',
        'target_id',
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
     * Fetches the Coupon data.
     *
     * @return BelongsTo
     */
    public function coupon() {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

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
