<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Customer\Database\Factories\CustomerGroupsFactory;

class CustomerGroups extends Model
{
    use HasFactory;
    protected $table = 'customer_groups';

    protected $fillable = ['name', 'description', 'discount_rate'];

    public $timestamps = true;
    

}
