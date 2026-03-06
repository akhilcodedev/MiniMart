<?php

namespace Modules\PriceRuleManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponEntity extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    const COUPON_ENTITY_ALL = 'all';
    const COUPON_ENTITY_CATEGORY = 'category';
    const COUPON_ENTITY_PRODUCT = 'product';

    const COUPON_ENTITY_LIST = [
        self::COUPON_ENTITY_ALL => "All",
        self::COUPON_ENTITY_CATEGORY => "Category",
        self::COUPON_ENTITY_PRODUCT => "Product",
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coupon_entities';

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
