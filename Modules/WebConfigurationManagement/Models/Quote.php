<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\WebConfigurationManagement\Database\Factories\QuoteFactory;

class Quote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['key','value','user_id'];

    protected static function newFactory(): QuoteFactory
    {
        //return QuoteFactory::new();
    }
}
