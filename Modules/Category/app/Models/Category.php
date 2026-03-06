<?php

namespace Modules\Category\Models;

use Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'path',
        'position',
        'level',
        'is_enabled',
        'included_in_menu',
        'banner_image',
        'category_image',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'url_key',
        'deleted_at'
    ];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public static function getCategoryTree()
    {
        return Category::whereNull('parent_id')->with('children')->get();
    }

    public function childrenRecursive()
    {
    return $this->hasMany(Category::class, 'parent_id')
                ->where('is_enabled', true)
                ->where('included_in_menu', true)
                ->with('childrenRecursive');
    }

    public function meta()
    {
        return $this->hasOne(CategoriesMeta::class, 'category_id');
    }
    public function products()
    {
         return $this->belongsToMany(
             Product::class,
             'category_products',
             'category_id',
             'product_id'
         );
    }

}
