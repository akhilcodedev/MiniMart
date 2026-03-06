<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Modules\WebConfigurationManagement\Database\Factories\CoreConfigDataFactory;

class CoreConfigData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['country_id', 'config_path', 'value'];

//    protected static function newFactory(): CoreConfigDataFactory
//    {
//        //return CoreConfigDataFactory::new();
//    }
}
