<?php

namespace  Modules\Customer\Models;

use App\Models\User;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Modules\Products\Models\ProductReview;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\CustomerResetPasswordNotification;
use Modules\WebConfigurationManagement\Models\OtpConfiguration;


class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    protected $fillable = [
        'customer_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'dial_code', 
        'password',
        'is_active',
        'profile_path',
        'user_id'
    ];

    protected $hidden = [
        'password',
    ];


    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomerResetPasswordNotification($token));
    }

    public function group()
    {
        return $this->hasOneThrough(
            CustomerGroups::class,
            CustomerGroupMap::class,
            'customer_id', // Foreign key on customer_groups_maps table
            'id', // Foreign key on customer_groups table
            'id', // Local key on customers table
            'group_id' // Local key on customer_groups_maps table
        );
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function otpConfigurations()
{
    return $this->hasMany(OtpConfiguration::class, 'customer_id');
}

}
