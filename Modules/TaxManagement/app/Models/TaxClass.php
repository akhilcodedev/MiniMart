<?php

namespace Modules\TaxManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\TaxManagement\Database\Factories\TaxClassFactory;

class TaxClass extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = ['name', 'code'];

    public function taxRates()
    {
        return $this->hasMany(TaxRate::class);
    }
}
