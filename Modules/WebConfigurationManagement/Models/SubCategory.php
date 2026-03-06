<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\WebConfigurationManagement\Database\Factories\SubCategoryFactory;

class SubCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'subcategories';
    protected $fillable = ['subcategory_name','parent_id'];

    public function category()
    {
        return $this->belongsTo(Category::class,'parent_id');
    }

    protected static function newFactory(): SubCategoryFactory
    {
        //return SubCategoryFactory::new();
    }
}
