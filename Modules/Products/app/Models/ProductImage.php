<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductImageType;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['product_id', 'image_type_id', 'image_url', 'is_default'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function imageType()
    {
        return $this->belongsTo(ProductImageType::class, 'image_type_id');
    }
}
