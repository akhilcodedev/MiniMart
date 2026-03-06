<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Customer\Models\Customer;
use Modules\Products\Models\Product;
use Modules\ShippingMethode\Models\ShippingMethod;
use function Carbon\this;

// use Modules\Cart\Database\Factories\CartFactory;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */



    protected $fillable = [
        'cart_id',
        'product_id',
        'product_sku',
        'product_name',
        'product_type_id',
        'product_is_in_stock',
        'product_url_key',
        'product_price',
        'product_quantity',
        'product_special_price',
        'product_special_price_from',
        'product_special_price_to',
        'product_status',
        'language_id',
        'product_meta_short_description',
        'product_meta_description',
        'product_meta_title',
        'product_meta_image',
        'product_meta_keyword',
        'attribute_id',
        'product_attribute_value_text',
        'product_attribute_value_int',
        'product_attribute_value_decimal',
        'product_attribute_value_date',
        'product_image_type_name',
        'product_image_type_description',
        'product_image_type_id',
        'product_image_url',
        'product_image_is_default',
        'quantity',
        'price',
        'total',
        'coupon_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }



    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function addresses(){
        return $this->hasMany(CartAddress::class,'new_cart_addresses.cart_id');
    }
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id'); // Ensure correct foreign key
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function shippingMethods()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}
