<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Products\Models\ProductImage;

class ProductImageType extends Model
{
    use HasFactory;

    protected $fillable = ['id','name', 'description'];

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}
