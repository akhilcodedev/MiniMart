<?php

namespace Modules\Customer\Models;

use Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WishListItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['customer_id', 'product_id'];

    public function customer(){
        return $this->hasOne(Customer::class, 'id');

    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

}
