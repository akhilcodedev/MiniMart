<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductReviewAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'label'];

    protected $guarded = [];

    public function ratings()
    {
        return $this->hasMany(ProductReviewAttributeRating::class, 'product_review_attribute_id');
    }
}
