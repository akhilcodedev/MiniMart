<?php

namespace Modules\ShippingMethode\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ShippingMethode\Database\Factories\ShippingMethodFactory;

class ShippingMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'shipping_methods';

    protected $fillable = ['name','code', 'status'];


    public function attributes()
    {
        return $this->hasMany(ShippingMethodAttribute::class);
    }
}
