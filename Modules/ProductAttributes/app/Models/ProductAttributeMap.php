<?php

namespace Modules\ProductAttributes\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Products\Database\Factories\AttributeSetAttributeFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Products\Models\Product;

class ProductAttributeMap extends Model
{
    use HasFactory;

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
    protected $table = 'product_attribute_maps';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['product_id', 'attribute_set_id', 'attribute_id', 'value', 'description', 'sort_order', 'is_active', 'created_by', 'updated_by'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    public function productData(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function attributeSet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AttributeSet::class, 'attribute_set_id', 'id');
    }

    public function attributeData(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'id');
    }

    public function createdUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
    public function optionValue()
    {
        return $this->belongsTo(ProductAttributeOptions::class, 'value', 'id');
    }
    public function attributeOption()
    {
        return $this->belongsTo(ProductAttributeOptions::class, 'value', 'id');
    }

}
