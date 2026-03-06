<?php

namespace Modules\PriceRuleManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    const AUTOMATIC_YES = 1;
    const AUTOMATIC_NO = 0;

    const MAX_LIMIT_YES = 1;
    const MAX_LIMIT_NO = 0;

    const CUSTOMER_ELIGIBILITY_ALL = 0;
    const CUSTOMER_ELIGIBILITY_SPECIFIC = 1;

    const CUSTOMER_ELIGIBILITY_LIST = [
        self::CUSTOMER_ELIGIBILITY_ALL => 'All',
        self::CUSTOMER_ELIGIBILITY_SPECIFIC => 'Specific',
    ];

    const REGION_ELIGIBILITY_ALL = 0;
    const REGION_ELIGIBILITY_SPECIFIC = 1;

    const REGION_ELIGIBILITY_LIST = [
        self::REGION_ELIGIBILITY_ALL => 'All',
        self::REGION_ELIGIBILITY_SPECIFIC => 'Specific',
    ];

    const ORDER_ELIGIBILITY_ALL = 1;
    const ORDER_ELIGIBILITY_GREATER_THAN = 2;
    const ORDER_ELIGIBILITY_EQUALS = 3;
    const ORDER_ELIGIBILITY_LESS_THAN = 4;

    const ORDER_ELIGIBILITY_LIST = [
        self::ORDER_ELIGIBILITY_ALL => 'All',
        self::ORDER_ELIGIBILITY_GREATER_THAN => 'Greater Than',
        self::ORDER_ELIGIBILITY_EQUALS => 'Equals',
        self::ORDER_ELIGIBILITY_LESS_THAN => 'Less Than',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coupons';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'code',
        'name',
        'type_id',
        'mode_id',
        'entity_id',
        'description',
        'start_date',
        'end_date',
        'is_automatic',
        'discount_value',
        'buy_count',
        'get_count',
        'has_max_limit',
        'max_discount_value',
        'min_cart_value',
        'max_cart_value',
        'customer_eligibility',
        'region_eligibility',
        'order_eligibility',
        'order_eligibility_value',
        'used_count',
        'max_usage_count',
        'max_count_per_user',
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
     * Fetches the Coupon Type data.
     *
     * @return BelongsTo
     */
    public function couponType() {
        return $this->belongsTo(CouponType::class, 'type_id', 'id');
    }

    /**
     * Fetches the Coupon Mode data.
     *
     * @return BelongsTo
     */
    public function couponMode() {
        return $this->belongsTo(CouponMode::class, 'mode_id', 'id');
    }

    /**
     * Fetches the Coupon Entity data.
     *
     * @return BelongsTo
     */
    public function couponEntity() {
        return $this->belongsTo(CouponEntity::class, 'entity_id', 'id');
    }

    /**
     * Fetches the Entity Map Data of the Coupon.
     *
     * @return HasMany
     */
    public function entityMap() {
        return $this->hasMany(CouponEntityMap::class, 'coupon_id', 'id');
    }

    /**
     * Fetches the mapped Entity Target Data.
     *
     * @return BelongsToMany
     */
    public function mappedEntities() {
        return $this->belongsToMany(
            CouponEntity::class,
            (new CouponEntityMap())->getTable(),
            'coupon_id',
            'entity_id',
        )->withPivot( 'target_id', 'is_active')->withTimestamps();
    }

    /**
     * Fetches the Eligibility Map Data of the Coupon.
     *
     * @return HasMany
     */
    public function eligibilityMap() {
        return $this->hasMany(CouponEligibilityMap::class, 'coupon_id', 'id');
    }

    /**
     * Fetches the Eligible Customers Data of the Coupon.
     *
     * @return HasMany
     */
    public function eligibleCustomers() {
        return $this->hasMany(CouponEligibilityMap::class, 'coupon_id', 'id')
            ->where('eligible_code',  CouponEligibilityMap::ELIGIBILITY_CODE_CUSTOMER);
    }

    /**
     * Fetches the Eligible Regions Data of the Coupon.
     *
     * @return HasMany
     */
    public function eligibleRegions() {
        return $this->hasMany(CouponEligibilityMap::class, 'coupon_id', 'id')
            ->where('eligible_code',  CouponEligibilityMap::ELIGIBILITY_CODE_REGION);
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
