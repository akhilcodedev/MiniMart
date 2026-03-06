<?php

namespace Modules\StoreManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CMS\Models\Language;

// use Modules\StoreManagement\Database\Factories\StoreFactory;

class Store extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'status',
        'url_key',
        'website',
        'language_id',
        'is_default',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
