<?php

namespace Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\URLRewrite\Models\UrlRewrite;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CMSMeta extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "cms_metas";

    protected $fillable = [
        'cms_page_id',
        'slug',
        'meta_title',
        'meta_key',
        'meta_description'
    ];
    public function page()
    {
        return $this->belongsTo(CMS::class, 'cms_id');
    }

    public function urlRewrite()
    {
        return $this->hasOne(UrlRewrite::class, 'entity_id')
            ->where('entity_type', 'cms');
    }
}
