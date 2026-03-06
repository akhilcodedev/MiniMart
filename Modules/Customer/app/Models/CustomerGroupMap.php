<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerGroupMap extends Model
{
    use HasFactory;
    public function group()
    {
        return $this->belongsTo(CustomerGroups::class, 'group_id');
    }
    protected $table = 'customer_groups_maps';

    protected $fillable = ['customer_id', 'group_id'];
}