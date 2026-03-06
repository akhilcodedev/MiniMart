<?php

namespace Modules\Products\Models;

use Modules\Cart\Models\CartItem;
use Modules\Category\Models\Category;
// use Modules\Products\Database\Factories\ProductFactory;
use Modules\Customer\Models\WishListItem;
use Modules\Products\Models\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Models\ProductType;
use Modules\Products\Models\ProductImage;
use Modules\URLRewrite\Models\UrlRewrite;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\StoreManagement\Models\StoreProduct;
use Modules\ProductAttributes\Models\AttributeSet;
use Modules\Products\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductAttributes\Models\ProductAttributeMap;
use Modules\PriceRuleManagement\Models\ProductCatalogPrice;
use Modules\ProductAttributes\Models\Attribute as ProductAttribute;
use Modules\Products\Models\HotDeal;

class Product extends Model
{
    
    use HasFactory, SoftDeletes;

    const ACTIVE_YES = 'active';
    const ACTIVE_NO = 'inactive';

    protected $fillable = [
        'sku',
        'name',
        'product_type_id',
        'is_in_stock',
        'url_key',
        'price',
        'special_price',
        'special_price_from',
        'special_price_to',
        'quantity',
        'status',
        'out_of_stock_threshold',
        'min_qty_allowed_in_shopping_cart',
        'max_qty_allowed_in_shopping_cart',
        'qty_uses_decimals',
        'backorders',
        'attribute_set_id',
        'related_products',
        'cross_selling_products',
        'is_variant',
        'parent_id',
        'variant_products'
    ];

    // Relationship with ProductType (one-to-many)
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    // Relationship with attributes (many-to-many)
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute_values');
    }
    public function attributeSet()
    {
        return $this->belongsTo(AttributeSet::class, 'attribute_set_id');
    }

    // Relationship with attributes (many-to-many)
    public function productAttributes()
    {
        return $this->hasMany(ProductAttributeMap::class, 'product_id', 'id');
    }

    public function attributeSetData()
    {
        return $this->belongsTo(AttributeSet::class, 'attribute_set_id', 'id');
    }

    public function mappedAttributes()
    {
        return $this->belongsToMany(
            ProductAttribute::class,
            (new ProductAttributeMap())->getTable(),
            'product_id',
            'attribute_id',
        )->withPivot('attribute_set_id', 'attribute_id', 'value', 'description', 'sort_order', 'is_active', 'created_by', 'updated_by')->withTimestamps();
    }

    // Relationship with ProductImage (one-to-many)
    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function metaDetails()
    {
        return $this->hasMany(ProductsMeta::class);
    }

    public function metaData()
    {
        return $this->hasOne(ProductsMeta::class, 'product_id');
    }

    public function storeProduct()
    {
        return $this->hasOne(StoreProduct::class, 'product_id');
    }


    public function productAttribute()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function category()
    {
        return $this->hasMany(CategoryProduct::class, 'product_id');
    }
    public function wishListitem()
    {
        return $this->hasMany(WishListItem::class, 'product_id');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_products', 'product_id', 'category_id');
    }

    public function productPrice()
    {
        return $this->hasOne(ProductCatalogPrice::class, 'product_id');
    }

    public function urlRewrite()
    {
        return $this->hasOne(UrlRewrite::class, 'entity_id')
            ->where('entity_type', 'product');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function optionMap()
    {
        return $this->hasMany(ProductVariantOptionMap::class, 'product_id');
    }
    public function variantProducts()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }
    public function productVariantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class, 'variant_id');
    }

    public function hotDeals()
    {
        return $this->belongsToMany(HotDeal::class, 'hot_deal_product');
    }

}
