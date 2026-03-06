<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\WebConfigurationManagement\Database\Factories\OssConfigurationFactory;

class OssConfiguration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'customer_id',
        'customer_email',
        'guest_email',
    ];

   
}
