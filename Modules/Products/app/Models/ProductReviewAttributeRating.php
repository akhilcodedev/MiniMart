<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductReviewAttributeRating extends Model
{
    use HasFactory;

    protected $table = 'product_review_attribute_ratings';
    protected $fillable = [
        'product_review_id',
        'product_review_attribute_id',
        'rating',
    ];


    public function attribute()
    {
        return $this->belongsTo(ProductReviewAttribute::class, 'product_review_attribute_id');
    }

    public function review()
    {
        return $this->belongsTo(ProductReview::class, 'product_review_id');
    }
}
