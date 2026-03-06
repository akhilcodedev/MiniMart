<?php

namespace Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentStatusOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'status', 'label',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_status');
    }
}
