<?php

namespace Modules\StoreManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\StoreManagement\Database\Factories\StoreProductFactory;

class StoreProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'store_id',
        'short_description',
        'description',
        'meta_title',
        'meta_image',
        'meta_keyword',
        'meta_description'
    ];

}
