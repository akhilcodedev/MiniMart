<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Products\Database\Factories\ProductVariantAttributeFactory;

class ProductVariantAttribute extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['parent_id', 'variant_id', 'variants'];

    protected $casts = [
        'variants' => 'array', // Auto-convert JSON to array
    ];

}
