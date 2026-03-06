<?php

namespace Modules\UserPermission\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = "roles";

    protected $fillable = [
        'name',
        'guard_name',
        'created_by',
        'label',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
}
