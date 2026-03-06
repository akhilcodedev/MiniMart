<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\UserManagement\Models\Language;

class PageTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'page_id', 'language_id', 'meta_title', 'meta_keyword', 'meta_description', 'is_published', 'type_of_form'];

    public function language()
    {
        return $this->hasOne(Language::class, 'id', 'language_id');
    }

}
