<?php

namespace Modules\NewsLetter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\NewsLetter\Database\Factories\NewsletterFactory;

class Newsletter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'status',
        'subscribed_at',
    ];

    protected $dates = ['subscribed_at', 'deleted_at'];

}
