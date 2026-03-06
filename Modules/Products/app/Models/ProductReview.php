<?php

namespace Modules\Products\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Customer\Models\Customer;


class ProductReview extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'product_reviews';
    protected $fillable = [
        'customer_id',
        'product_id',
        'user_id',
        'product_review_attribute_id',
        'title',
        'star_rating',
        'description',
        'status',
        'average_rating',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeSet()
    {
        return $this->belongsTo(ProductReviewAttribute::class, 'product_review_attribute_id');
    }

    public function attributeRatings()
    {
        return $this->hasMany(ProductReviewAttributeRating::class);
    }

    public function calculateAverageRating()
    {
        $ratings = $this->attributeRatings()->pluck('rating')->toArray();

        if (count($ratings) > 0) {
            $average = array_sum($ratings) / count($ratings);
            return round($average, 2);
        }

        return null;
    }
}
