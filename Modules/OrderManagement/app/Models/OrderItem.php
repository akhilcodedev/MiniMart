<?php

namespace Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Products\Models\Product;


class OrderItem extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'cart_id','order_id','cart_item_id', 'product_id', 'product_sku', 'product_name',
        'product_type_id', 'product_is_in_stock', 'product_url_key',
        'product_price', 'product_quantity', 'product_special_price',
        'product_special_price_from', 'product_special_price_to',
        'product_status', 'language_id', 'attribute_id',
        'product_attribute_value_text', 'product_attribute_value_int',
        'product_attribute_value_decimal', 'product_attribute_value_date',
        'product_image_url', 'quantity', 'price', 'total', 'coupon_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
