<?php

namespace Modules\PaymentManagement\Helpers;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Modules\PaymentManagement\Models\PaymentMethod;

class PaymentManagementServiceHelper
{

    public function __construct()
    {

    }

    /**
     * Get the given DateTime string in the given DateTime format
     *
     * @param string $dateTimeString
     * @param string $format
     * @param bool $reverse
     *
     * @return string
     */
    public function getFormattedTime(string $dateTimeString = null, string $format = null, bool $reverse = null): string
    {

        if (is_null($dateTimeString) || (trim($dateTimeString) == '')) {
            return '';
        }

        if (is_null($format) || (trim($format) == '')) {
            $format = DateTimeInterface::ATOM;
        }

        $appTimeZone = 'UTC';
        $channelTimeZone = config('app.timezone');
        $zoneList = timezone_identifiers_list();
        $cleanZone = (in_array(trim($channelTimeZone), $zoneList)) ? trim($channelTimeZone) : $appTimeZone;

        $sourceTz = $appTimeZone;
        $destTz = $cleanZone;
        if (!is_null($reverse) && is_bool($reverse) && ($reverse === true)) {
            $sourceTz = $cleanZone;
            $destTz = $appTimeZone;
        }

        try {
            $dtObj = new \DateTime($dateTimeString, new \DateTimeZone($sourceTz));
            $dtObj->setTimezone(new \DateTimeZone($destTz));
            return $dtObj->format($format);
        } catch (\Exception $e) {
            return '';
        }

    }

    public function getPaymentMethodsFilteredData($searchTerm = '', $start = 0, $limit = 0,  $sortColumn = '', $sortDir = 'asc') {

        $returnResult = null;

        $modeTableName = (new PaymentMethod())->getTable();

        $modesRequest = DB::table($modeTableName . ' as m');

        if (trim($searchTerm) != '') {
            $modesRequest->where(function ($query) use ($searchTerm) {
                $query->where('m.code', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('m.name', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $modesRequest->select(
            'm.id as methodId', 'm.code as methodCode', 'm.name as methodName', 'm.sort_order as methodSortOrder',
            'm.test_mode as methodTestMode', 'm.is_online as methodOnlineStatus', 'm.is_active as methodActiveStatus', 'm.updated_at as updatedAt'
        );

        if ((trim($sortColumn) != '') && ((strtolower(trim($sortDir)) == 'asc') || (strtolower(trim($sortDir)) == 'desc'))) {
            $modesRequest->orderBy($sortColumn, $sortDir);
        }

        $countRequest = $modesRequest;
        $returnResult['totalCount'] = $countRequest->count();

        if (is_numeric($start) && ((int)$start >= 0)) {
            $modesRequest->offset((int)$start);
        }

        if (is_numeric($limit) && ((int)$limit > 0)) {
            $modesRequest->limit((int)$limit);
        }

        $returnResult['filteredData'] = $modesRequest->get();

        return $returnResult;

    }

    function checkIsValidJSONString($data) {
        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

}
