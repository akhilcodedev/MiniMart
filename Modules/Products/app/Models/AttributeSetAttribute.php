<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Products\Database\Factories\AttributeSetAttributeFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AttributeSetAttribute extends Model
{
    protected $table = 'attribute_set_attributes';
}
