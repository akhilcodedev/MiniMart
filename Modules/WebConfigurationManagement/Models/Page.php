<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\UserManagement\Models\Language;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'order_by'];

    public function translation()
    {
        return $this->hasOne(PageTranslation::class, 'page_id', 'id');
    }
}
