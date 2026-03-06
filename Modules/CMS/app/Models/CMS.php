<?php

namespace Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; 

class CMS extends Model
{
    use HasFactory, SoftDeletes; 
    protected $table = "cms_pages";

    protected $fillable = [
        'title',
        'language',
        'content',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean'
    ];

    public function meta()
    {
        return $this->hasOne(CMSMeta::class, 'cms_page_id');
    }
}
