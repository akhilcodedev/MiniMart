<?php

namespace Modules\WebConfigurationManagement\Models;

use Modules\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\WebConfigurationManagement\Database\Factories\OtpConfigurationFactory;

class OtpConfiguration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mobile_number',
        'otp',
        'expires_at'
    ];

    protected $dates = ['expires_at'];
    
    public function customer()
{
    return $this->belongsTo(Customer::class, 'customer_id');
}

}
