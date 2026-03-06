<?php

namespace Modules\ProductAttributes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ProductAttributes\Database\Factories\ProductAttributeOptionsFactory;

class ProductAttributeOptions extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = ['attribute_id', 'english_value', 'arabic_value', 'color_code', 'image_url', 'created_by', 'updated_by'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
