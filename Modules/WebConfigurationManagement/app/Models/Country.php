<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\WebConfigurationManagement\Database\Factories\CountryFactory;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'countries';

    // Define the columns that are mass assignable
    protected $fillable = [
        'name',
        'code',
        'nicename',
        'iso3',
        'numcode',
        'phonecode',
        'dial_code',
    ];

    // Optional: You can also specify the primary key if it differs from 'id'
    protected $primaryKey = 'id';

    // protected static function newFactory(): CountryFactory
    // {
    //     // return CountryFactory::new();
    // }
}
