<?php

namespace Modules\WebConfigurationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\WebConfigurationManagement\Database\Factories\TimeFactory;

class TimeZone extends Model
{
    use HasFactory;
    protected $table = 'timezones';

//    public function timezone_name($timezone_id){
//        $timezonedetail = TimeZone::where('id', $timezone_id)->orWhere('timezone', $timezone_id)->first('timezone');
//        if($timezonedetail)
//        {
//            return $timezonedetail->timezone;
//        } else {
//            return "Asia/Kolkata";
//        }
//    }

    public function getTimezoneName($timezone_id)
    {
        return cache()->remember("timezone_name_{$timezone_id}", 60 * 60, function () use ($timezone_id) {
            $timezoneDetailQ = \App\Models\TimeZone::select('id', 'offset', 'timezone');
            if (is_numeric($timezone_id)) {
                $timezoneDetailQ->where('id', $timezone_id)
                    ->orWhere('timezone', $timezone_id);
            } else {
                $timezoneDetailQ->where('timezone', $timezone_id);
            }
            $timezoneDetail = $timezoneDetailQ->first();
            return $timezoneDetail ? $timezoneDetail->timezone : "Asia/Kolkata";
        });
    }

    public function getTimezoneOffset($timezone_id){
        return cache()->remember("timezone_name_{$timezone_id}", 60 * 60, function () use ($timezone_id) {
            $timezoneDetailQ = TimeZone::select('id', 'offset', 'timezone');
            if (is_numeric($timezone_id)) {
                $timezoneDetailQ->where('id', $timezone_id)
                    ->orWhere('timezone', $timezone_id);
            } else {
                $timezoneDetailQ->where('timezone', $timezone_id);
            }
            $timezoneDetail = $timezoneDetailQ->first();
            return $timezoneDetail ? $timezoneDetail->offset : "+00:00";
        });
    }

    protected $fillable = [
        'id',
        'timezone',
        'offset',
        'diff_from_gtm',
        'created_at',
        'updated_at',
    ];
}
