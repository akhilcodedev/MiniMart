<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Cart\Database\Factories\CartAddressFactory;

class CartAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cart_id',
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'locality',
        'city',
        'state',
        'postal_code',
        'country',
        'type',
        'is_default',
    ];



    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

}
