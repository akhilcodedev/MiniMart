<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Products\Models\AttributeSet;



class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'label', 'input_type', 'is_required', 'is_filterable', 'is_sortable'];

    public function attributeSets()
    {
        return $this->belongsToMany(AttributeSet::class, 'attribute_set_attributes');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values');
    }
}
