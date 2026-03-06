<?php

namespace Modules\ProductAttributes\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Products\Models\Product;


class Attribute extends Model
{
    use HasFactory;

    const INPUT_TYPE_TEXT = 'text';
    const INPUT_TYPE_TEXTAREA = 'textarea';
    const INPUT_TYPE_SELECT = 'select';
    const INPUT_TYPE_SELECT_MULTI = 'multiselect';
    const INPUT_TYPE_BOOLEAN = 'boolean';
    const INPUT_TYPE_DATE = 'date';
    const INPUT_TYPE_PRICE = 'price';
    const INPUT_TYPE_LIST = [
        self::INPUT_TYPE_TEXT => 'Text',
        self::INPUT_TYPE_TEXTAREA => 'Text-Area',
        self::INPUT_TYPE_SELECT => 'Select Drop-Down',
        self::INPUT_TYPE_SELECT_MULTI => 'Multi-Select DropDown',
        self::INPUT_TYPE_BOOLEAN => 'Boolean',
        self::INPUT_TYPE_DATE => 'Date',
        self::INPUT_TYPE_PRICE => 'Price',
    ];
    const VALUE_NEEDED_INPUT_TYPES = [
        self::INPUT_TYPE_SELECT => 'Select Drop-Down',
        self::INPUT_TYPE_SELECT_MULTI => 'Multi-Select DropDown',
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
    protected $table = 'product_attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['code', 'label', 'input_type', 'description', 'is_required', 'is_filterable', 'is_configurable','is_sortable', 'is_active', 'created_by', 'updated_by'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    public function options()
    {
        return $this->hasMany(ProductAttributeOptions::class, 'attribute_id', 'id');
    }

    public function mappedAttributeSets() {
        return $this->belongsToMany(
            AttributeSet::class,
            (new AttributeSetMap())->getTable(),
            'attribute_id',
            'attribute_set_id'
        )->withPivot('id', 'value', 'description', 'sort_order', 'is_required', 'is_filterable', 'is_sortable', 'is_active', 'created_by', 'updated_by')->withTimestamps();
    }

    public function mappedProducts() {
        return $this->belongsToMany(
            Product::class,
            (new ProductAttributeMap())->getTable(),
            'attribute_id',
            'product_id'
        )->withPivot('attribute_set_id', 'product_id', 'value', 'description', 'sort_order', 'is_active', 'created_by', 'updated_by')->withTimestamps();
    }

    public function createdUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
//    public function values()
//    {
//        return $this->hasMany(ProductAttributeOptions::class, 'attribute_id','id');
//    }



}
