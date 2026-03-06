<?php

namespace Modules\UserPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRolePermission extends Model
{
    use HasFactory;

    protected $table = "user_role_permission";

    protected $fillable = [
        'role_id',
        'user_id',
        'user_name'
    ];
}
