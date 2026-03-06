<?php

namespace Modules\TaxManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\TaxManagement\Database\Factories\TaxRateFactory;

class TaxRate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['tax_class_id', 'country', 'state', 'rate', 'type'];

    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class);
    }
}
