<?php

namespace Modules\Base\Services;

use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BaseServiceHelper
{

    private $restApiService = null;

    public function __construct($channel = '')
    {
        $this->restApiService = new RestApiService();
    }

    /**
     * Get the given DateTime string in the given DateTime format
     *
     * @param string $dateTimeString
     * @param string $format
     * @param string $toTz
     *  @param string $fromTz
     *
     * @return string
     */
    public function getFormattedTime($dateTimeString = '', $format = '', $toTz = '', $fromTz = '')
    {

        if (is_null($dateTimeString) || (trim($dateTimeString) == '')) {
            return '';
        }

        if (is_null($toTz) || (trim($toTz) == '')) {
            return '';
        }

        if (is_null($format) || (trim($format) == '')) {
            $format = DateTimeInterface::ATOM;
        }

        $appTimeZone = config('app.timezone');
        $sourceTz = (is_null($fromTz) || (trim($fromTz) == '')) ? $appTimeZone : trim($fromTz);
        $destTz = trim($toTz);

        $zoneList = timezone_identifiers_list();
        $sourceTzClean = (in_array(trim($sourceTz), $zoneList)) ? trim($sourceTz) : null;
        $destTzClean = (in_array(trim($destTz), $zoneList)) ? trim($destTz) : null;

        if (is_null($sourceTzClean) || is_null($destTzClean)) {
            return '';
        }

        try {
            $dtObj = new \DateTime($dateTimeString, new \DateTimeZone($sourceTzClean));
            $dtObj->setTimezone(new \DateTimeZone($destTzClean));
            return $dtObj->format($format);
        } catch (\Exception $e) {
            return '';
        }

    }

    public function getFileFullPath($path = '') {
        return (!is_null($path) && (trim($path) != ''))
            ? Storage::path(trim($path))
            : '';
    }

    public function getFileUrl($path = '') {
        return (!is_null($path) && (trim($path) != '') && Storage::exists(trim($path)))
            ? Storage::url(trim($path))
            : '';
    }

    public function deleteFile($path = '') {
        return (!is_null($path) && (trim($path) != '') && Storage::exists(trim($path)))
            ? Storage::delete(trim($path))
            : true;
    }

    public function stripAllHtmlTagsLooper($htmlString, $ignoredTags = []) {
        $ignoredTagsClean = (!is_null($ignoredTags) && is_array($ignoredTags) && (count($ignoredTags) > 0)) ? $ignoredTags : [];
        $convertedString = strip_tags(html_entity_decode($htmlString), $ignoredTagsClean);
        if ($htmlString != $convertedString) {
            return $this->stripAllHtmlTagsLooper($convertedString, $ignoredTagsClean);
        }
        return $htmlString;
    }

    public function stripAllNonPrintableCharacters($sourceString) {
        return preg_replace('/[[:^print:]]/', '', $sourceString);
        /*return preg_replace('/[^[:print:]\n]/u', '', mb_convert_encoding($sourceString, 'UTF-8', 'UTF-8'));
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $sourceString);
        return iconv("UTF-8", "UTF-8//IGNORE", $sourceString);*/
    }

    public function formatFileSizeFromBytes($size = '', $precision = 2, $singleSuffix = false) {
        if (is_null($size) || (trim($size) == '') || !is_numeric($size) || ((float)$size < 0)) {
            return '';
        }
        $precisionClean = (!is_null($precision) && is_numeric($precision) && ((int)$precision >= 0)) ? (int)$precision : 2;
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $singleSuffixes = array('B', 'K', 'M', 'G', 'T', 'P');
        $sizeValue = round(pow(1024, $base - floor($base)), $precisionClean);
        $suffixClean = (!is_null($singleSuffix) && is_bool($singleSuffix) && ($singleSuffix === true))
            ? $singleSuffixes[floor($base)] : $suffixes[floor($base)];
        return $sizeValue . ' ' . $suffixClean;
    }

    function formatFileSizeToBytes($sizeString = '') {
        if (is_null($sizeString) || (trim($sizeString) == '')) {
            return 0;
        }
        $cleanString = trim(str_replace(' ', '', $sizeString));
        if (is_numeric($cleanString)) {
            return $cleanString;
        }
        if (strlen($cleanString) < 3) {
            return is_numeric($cleanString) ? $cleanString : 0;
        }
        $number = substr($cleanString, 0, -2);
        if (!is_numeric($number)) {
            return 0;
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $unitSingleChar = ['K' => 'KB', 'M' => 'MB', 'G' => 'GB', 'T' => 'TB', 'P' => 'PB'];
        $number = substr($cleanString, 0, -2);
        $suffix = strtoupper(substr($cleanString,-2));
        $suffixClean = $suffix;
        $numberClean = $number;
        if(is_numeric(substr($suffix, 0, 1))) {
            $singleSuffix = substr($suffix, -1);
            if (!array_key_exists($singleSuffix, $unitSingleChar)) {
                return preg_replace('/[^\d]/', '', $cleanString);
            }
            $numberClean = substr($cleanString, 0, -1);
            $suffixClean = $unitSingleChar[$singleSuffix];
        }
        $exponent = array_flip($units)[$suffixClean] ?? null;
        if($exponent === null) {
            return 0;
        }
        $byteResult = ((float)$numberClean) * (1024 ** $exponent);
        return floor($byteResult);
    }

    public function getTimeSpanBetweenDates($fromDate = '', $toDate = '') {

        if (is_null($fromDate) || (trim($fromDate) == '') || (strtotime(trim($fromDate)) === false)) {
            return null;
        }

        if (is_null($toDate) || (trim($toDate) == '') || (strtotime(trim($toDate)) === false)) {
            return null;
        }

        $origin = date_create($fromDate);
        $target = date_create($toDate);
        $interval = date_diff($origin, $target);
        $intervalStringLabels = ['year', 'month', 'day', 'hour', 'minute', 'second'];
        $intervalString = $interval->format('%y-%m-%d-%H-%i-%s');
        $intervalStringSplitter = explode('-', $intervalString);
        $intervalStringFinal = '';
        foreach ($intervalStringSplitter as $indexKey => $splitterEl) {
            if ((int)$splitterEl > 0) {
                $intervalStringFinal = $splitterEl . ' ' . $intervalStringLabels[$indexKey] . (((int)$splitterEl > 1) ? 's' : '');
                break;
            }
        }

        return ($intervalStringFinal == '') ? 'just now' : $intervalStringFinal;

    }

    public function getTimeAgo($time_ago) {

        $time_ago = strtotime($time_ago);
        $cur_time = time();
        $time_elapsed = $cur_time - $time_ago;
        $seconds    = $time_elapsed;
        $minutes    = round($time_elapsed / 60 );
        $hours      = round($time_elapsed / 3600);
        $days       = round($time_elapsed / 86400 );
        $weeks      = round($time_elapsed / 604800);
        $months     = round($time_elapsed / 2600640 );
        $years      = round($time_elapsed / 31207680 );

        if ($seconds <= 60){
            return "just now";
        } elseif ($minutes <= 60) {
            if ($minutes == 1) {
                return "1m ago";
            } else {
                return $minutes."m ago";
            }
        } elseif ($hours <= 24) {
            if ($hours == 1) {
                return "1h ago";
            } else {
                return $hours."h ago";
            }
        } elseif ($days <= 7) {
            if ($days == 1) {
                return "yesterday";
            } else {
                return $days."d ago";
            }
        } elseif ($weeks <= 4.3) {
            if($weeks == 1){
                return "1w ago";
            } else {
                return $weeks."w ago";
            }
        } elseif ($months <= 12) {
            if ($months == 1) {
                return "1mo ago";
            } else {
                return $months."mo ago";
            }
        } else {
            if ($years == 1) {
                return "1y ago";
            } else {
                return $years."y ago";
            }
        }

    }

    public function generateRandomString($limit = 10) {
        return (!is_null($limit) && is_numeric($limit) && ((int)$limit > 0)) ? Str::random((int)$limit) : '';
    }

    /**
     * Create a "Random" String
     *
     * @param int $limit
     * @param string $type type of random string.  basic, alpha, alnum, numeric, nozero, md5, sha1, bytes
     *
     * @return int|string
     */
    public function generateCustomRandomString($limit = 10, $type = 'basic') {

        $limitClean = (!is_null($limit) && is_numeric($limit) && ((int)$limit > 0)) ? (int)$limit : 10;

        $allowedTypes = ['basic', 'alpha', 'alnum', 'numeric', 'nozero', 'md5', 'sha1', 'bytes'];
        $typeClean = (!is_null($type) && (trim($type) != '') && in_array(strtolower(trim($type)), $allowedTypes)) ? strtolower(trim($type)) : $allowedTypes[0];

        $randomRangePower = ($limitClean < 1) ? 1 : $limitClean;
        $randomStartRange = pow(10, ($randomRangePower - 1));
        $randomEndRange = (pow(10, $randomRangePower) - 1);

        try {

            switch ($typeClean) {
                case 'basic':
                    return mt_rand($randomStartRange, $randomEndRange);
                case 'alnum':
                case 'numeric':
                case 'nozero':
                case 'alpha':
                    switch ($typeClean)
                    {
                        case 'alpha':
                            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            break;
                        case 'alnum':
                            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            break;
                        case 'numeric':
                            $pool = '0123456789';
                            break;
                        case 'nozero':
                            $pool = '123456789';
                            break;
                    }
                    return substr(str_shuffle(str_repeat($pool, ceil($limitClean / strlen($pool)))), 0, $limitClean);
                case 'md5':
                    return md5(uniqid(mt_rand($randomStartRange, $randomEndRange)));
                case 'sha1':
                    return sha1(uniqid(mt_rand($randomStartRange, $randomEndRange), TRUE));
                case 'bytes':
                    return random_bytes($limitClean);
                default:
                    return '';
            }

        } catch (\Exception $e) {
            return '';
        }

    }

    public function setUrlSlugFromString($str, $separator = '-') {
        return Str::slug($str, $separator);
    }

}
