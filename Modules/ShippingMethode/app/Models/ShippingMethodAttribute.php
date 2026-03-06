<?php

namespace Modules\ShippingMethode\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ShippingMethode\Database\Factories\ShippingMethodAttributeFactory;

class ShippingMethodAttribute extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'shipping_method_attributes';

    protected $fillable = ['shipping_method_id', 'name', 'type', 'value', 'sort_order'];


    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}
