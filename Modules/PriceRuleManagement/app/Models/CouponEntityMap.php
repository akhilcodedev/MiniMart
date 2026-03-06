<?php

namespace Modules\PriceRuleManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponEntityMap extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coupon_entity_maps';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'coupon_id',
        'entity_id',
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
     * Fetches the Application data.
     *
     * @return BelongsTo
     */
    public function couponEntity() {
        return $this->belongsTo(CouponEntity::class, 'entity_id', 'id');
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
