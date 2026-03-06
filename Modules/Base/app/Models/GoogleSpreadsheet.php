<?php

namespace Modules\Base\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleSpreadsheet extends Model
{

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_spreadsheets';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'spreadsheet_id',
        'title',
        'sheets',
        'comments',
        'notes',
        'extra_info',
        'google_sheet_user_email',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Fetches the User data who executed the creation.
     *
     * @return BelongsTo
     */
    public function createdUser() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Fetches the User data who executed the update.
     *
     * @return BelongsTo
     */
    public function updatedUser() {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Checks whether the Google Spreadsheet is Active.
     * @return bool
     */
    public function isSpreadsheetActive() {
        return $this->status === self::ACTIVE_YES;
    }


}
