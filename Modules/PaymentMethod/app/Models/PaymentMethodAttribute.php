<?php

namespace Modules\PaymentMethod\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\PaymentMethod\Database\Factories\PaymentMethodAttributeFactory;

class PaymentMethodAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['payment_method_id', 'name', 'type', 'value', 'sort_order'];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }
}
