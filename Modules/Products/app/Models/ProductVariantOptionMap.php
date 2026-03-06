<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariantOptionMap extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'option_id',
        'value',
        'sort_order',
        'is_active'
    ];

    public function option()
    {
        return $this->belongsTo(ProductVariantOption::class, 'option_id');
    }

}
