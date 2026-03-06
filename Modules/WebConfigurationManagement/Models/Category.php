<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\WebConfigurationManagement\Database\Factories\CategoryFactory;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['category_name','label'];

    public function subcategories()
    {
        return $this->hasMany(SubCategory::class,'parent_id');
    }

    protected static function newFactory(): CategoryFactory
    {
        //return CategoryFactory::new();
    }
}
