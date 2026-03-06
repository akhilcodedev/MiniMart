<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Products\Database\Factories\ProductsMetaFactory;

class ProductsMeta extends Model
{
    use HasFactory;
    protected $table = 'products_meta'; // Make sure this matches your table name in the database

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'language_id',
        'short_description',
        'description',
        'meta_title',
        'meta_image',
        'meta_keyword',
        'meta_description',
    ];
    // protected static function newFactory(): ProductsMetaFactory
    // {
    //     // return ProductsMetaFactory::new();
    // }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
