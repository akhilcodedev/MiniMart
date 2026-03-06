<?php

namespace Modules\ProductAttributes\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Products\Models\Product;

// use Modules\Products\Database\Factories\AttributeSetFactory;

class AttributeSet extends Model
{
    use HasFactory;

    const ATTRIBUTE_SET_TYPE_PRODUCT = 'product';
    const ATTRIBUTE_SET_TYPE_LIST = [
        self::ATTRIBUTE_SET_TYPE_PRODUCT => 'Product',
    ];

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;
    const ACTIVE_STATUS_LIST = [
        self::ACTIVE_YES => 'Yes',
        self::ACTIVE_NO => 'No',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_attribute_sets';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name', 'label', 'type', 'description', 'is_active', 'created_by', 'updated_by'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    public function mappedAttributes() {
        return $this->belongsToMany(
            Attribute::class,
            (new AttributeSetMap())->getTable(),
            'attribute_set_id',
            'attribute_id',
        )->withPivot('id', 'value', 'description', 'sort_order', 'is_required', 'is_filterable', 'is_sortable', 'is_active', 'created_by', 'updated_by')->withTimestamps();
    }
    public function attributeMappings()
    {
        return $this->hasMany(AttributeSetMap::class, 'attribute_set_id');
    }

    public function mappedProducts() {
        return $this->belongsToMany(
            Product::class,
            (new ProductAttributeMap())->getTable(),
            'attribute_set_id',
            'product_id'
        )->withPivot('attribute_set_id', 'attribute_id', 'value', 'description', 'sort_order', 'is_active', 'created_by', 'updated_by')->withTimestamps();
    }

    public function createdUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

}
