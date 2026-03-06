<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Products\Models\Attribute;
// use Modules\Products\Database\Factories\AttributeSetFactory;

class AttributeSet extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_set_attributes');
    }
}
