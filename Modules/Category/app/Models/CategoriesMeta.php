<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\URLRewrite\Models\UrlRewrite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Category\Database\Factories\CategoriesMetaFactory;

class CategoriesMeta extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'categories_meta';

    protected $fillable = [
        'category_id',
        'title',
        'short_description',
        'description',
        'meta_keywords',
        'meta_title',
        'meta_description',
    ];

    /**
     * Relationship to Category model.
     *
     * A category meta belongs to a category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function urlRewrite()
    {
        return $this->hasOne(UrlRewrite::class, 'entity_id')
        ->where('entity_type', 'category');
    }
}
