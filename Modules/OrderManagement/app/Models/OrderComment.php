<?php

namespace Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'comment', 'commented_by',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
