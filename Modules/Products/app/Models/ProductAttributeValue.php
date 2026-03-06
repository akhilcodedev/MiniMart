<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Products\Models\Attribute;


class ProductAttributeValue extends Model
{
    protected $table = 'product_attribute_values';

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
