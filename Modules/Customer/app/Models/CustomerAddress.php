<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'id','customer_id', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country', 'type', 'is_default',
    ];
}
