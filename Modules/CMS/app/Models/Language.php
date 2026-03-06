<?php

namespace Modules\CMS\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\StoreManagement\Models\Store;

class Language extends Model
{
    use HasFactory;

    protected $fillable = ['sort_code', 'name', 'nativeName'];

    public function stores()
    {
        return $this->hasMany(Store::class);
    }
}
