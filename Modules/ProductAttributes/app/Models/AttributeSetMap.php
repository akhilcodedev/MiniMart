<?php

namespace Modules\ProductAttributes\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Products\Database\Factories\AttributeSetAttributeFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AttributeSetMap extends Model
{
    use HasFactory;

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;
    const ACTIVE_STATUS_LIST = [
        self::ACTIVE_YES => 'Yes',
        self::ACTIVE_NO => 'No',
    ];

    const REQUIRED_YES = 1;
    const REQUIRED_NO = 0;
    const REQUIRED_STATUS_LIST = [
        self::REQUIRED_YES => 'Yes',
        self::REQUIRED_NO => 'No',
    ];

    const FILTERABLE_YES = 1;
    const FILTERABLE_NO = 0;
    const FILTERABLE_STATUS_LIST = [
        self::FILTERABLE_YES => 'Yes',
        self::FILTERABLE_NO => 'No',
    ];

    const SORTABLE_YES = 1;
    const SORTABLE_NO = 0;
    const SORTABLE_STATUS_LIST = [
        self::SORTABLE_YES => 'Yes',
        self::SORTABLE_NO => 'No',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribute_set_maps';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['attribute_set_id', 'attribute_id', 'value', 'description', 'sort_order', 'is_required', 'is_filterable', 'is_sortable', 'is_active', 'created_by', 'updated_by'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    public function attributeSet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AttributeSet::class, 'attribute_set_id', 'id');
    }


    public function attributeData(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'id');
    }

    public function createdUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

}
