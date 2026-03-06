<?php

namespace Modules\Products\Models;

use Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Products\Database\Factories\HotDealFactory;

class HotDeal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['discount', 'start_date', 'end_date'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'hot_deal_product');
    }
}
