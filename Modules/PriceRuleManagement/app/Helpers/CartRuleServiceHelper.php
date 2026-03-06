<?php

namespace Modules\PriceRuleManagement\Helpers;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use \Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Category\Models\Category;
use Modules\Customer\Models\Customer;
use Modules\OrderManagement\Models\Order;
use Modules\PriceRuleManagement\Models\Coupon;
use Modules\PriceRuleManagement\Models\CouponMode;
use Modules\PriceRuleManagement\Models\CouponType;
use Modules\PriceRuleManagement\Models\CouponEntity;
use Modules\PriceRuleManagement\Models\CouponEntityMap;
use Modules\PriceRuleManagement\Models\CouponEligibilityMap;
use Modules\Products\Models\CategoryProduct;
use Modules\Products\Models\Product;

class CartRuleServiceHelper
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
    public function getFormattedTime(string $dateTimeString = '', string $format = '', bool $reverse = false): string
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

    /**
     * Get the Coupon Type List
     *
     * @param $active
     *
     * @return Model
     */
    public function getCouponTypeList($active = null) {

        $typeRequest = CouponType::select('*');

        if (!is_null($active)) {
            if (is_bool($active) && ($active === true)) {
                $typeRequest->where('is_active', CouponType::ACTIVE_YES);
            } elseif (is_bool($active) && ($active === false)) {
                $typeRequest->where('is_active', CouponType::ACTIVE_NO);
            }
        }

        $typeRequest->orderBy('sort_order', 'asc');
        $typeRequest->orderBy('id', 'asc');

        return $typeRequest->get();

    }

    /**
     * Get the Coupon Mode List
     *
     * @param $active
     *
     * @return Model
     */
    public function getCouponModeList($active = null) {

        $modeRequest = CouponMode::select('*');

        if (!is_null($active)) {
            if (is_bool($active) && ($active === true)) {
                $modeRequest->where('is_active', CouponMode::ACTIVE_YES);
            } elseif (is_bool($active) && ($active === false)) {
                $modeRequest->where('is_active', CouponMode::ACTIVE_NO);
            }
        }

        $modeRequest->orderBy('sort_order', 'asc');
        $modeRequest->orderBy('id', 'asc');

        return $modeRequest->get();

    }

    /**
     * Get the Coupon Application List
     *
     * @param $active
     *
     * @return Model
     */
    public function getCouponEntityList($active = null) {

        $applicationRequest = CouponEntity::select('*');

        if (!is_null($active)) {
            if (is_bool($active) && ($active === true)) {
                $applicationRequest->where('is_active', CouponEntity::ACTIVE_YES);
            } elseif (is_bool($active) && ($active === false)) {
                $applicationRequest->where('is_active', CouponEntity::ACTIVE_NO);
            }
        }

        $applicationRequest->orderBy('sort_order', 'asc');
        $applicationRequest->orderBy('id', 'asc');

        return $applicationRequest->get();

    }

    public function getCouponsFilteredData($searchTerm = '', $start = 0, $limit = 0,  $sortColumn = '', $sortDir = 'asc') {

        $returnResult = null;

        $couponTableName = (new Coupon())->getTable();
        $typeTableName = (new CouponType())->getTable();
        $modeTableName = (new CouponMode())->getTable();
        $entityTableName = (new CouponEntity())->getTable();
        $userTableName = (new User())->getTable();

        $couponsRequest = DB::table($couponTableName . ' as c');

        $couponsRequest->leftJoin($typeTableName . ' as t', 'c.type_id', '=',  't.id')
            ->leftJoin($modeTableName . ' as m', 'c.mode_id', '=',  'm.id')
            ->leftJoin($entityTableName . ' as e', 'c.entity_id', '=',  'e.id')
            ->leftJoin($userTableName . ' as u', 'c.updated_by', '=',  'u.id');

        if (trim($searchTerm) != '') {
            $couponsRequest->where(function ($query) use ($searchTerm) {
                $query->where('c.code', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('c.name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('c.description', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('c.start_date', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('c.end_date', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('t.name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('m.name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('e.name', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $couponsRequest->select(
            'c.id as couponId', 'c.code as couponCode', 'c.name as couponName', 'c.start_date as startDate',
            'c.end_date as endDate', 't.name as couponType', 'm.name as couponMode', 'c.discount_value as couponDiscount',
            'c.has_max_limit as couponHasLimit', 'c.max_discount_value as couponMaxDiscount', 'c.min_cart_value as minCartValue',
            'e.name as CouponEntity', 'c.region_eligibility as couponRegion', 'c.customer_eligibility as couponCustomer',
            'c.order_eligibility as couponOrder', 'c.order_eligibility_value as couponOrderValue',
            'c.max_usage_count as totalAvailable', 'c.max_count_per_user as countPerUser', 'c.used_count as usedCount',
            'c.updated_at as updatedAt', 'c.is_active as isActive'
        );

        if ((trim($sortColumn) != '') && ((strtolower(trim($sortDir)) == 'asc') || (strtolower(trim($sortDir)) == 'desc'))) {
            $couponsRequest->orderBy($sortColumn, $sortDir);
        }

        $countRequest = $couponsRequest;
        $returnResult['totalCount'] = $countRequest->count();

        if (is_numeric($start) && ((int)$start >= 0)) {
            $couponsRequest->offset((int)$start);
        }

        if (is_numeric($limit) && ((int)$limit > 0)) {
            $couponsRequest->limit((int)$limit);
        }

        $returnResult['filteredData'] = $couponsRequest->get();

        return $returnResult;

    }

    public function getProductsSearchData($searchTerm = '', $page = 0, $limit = 0) {

        $returnResult = null;

        $productTableName = (new Product())->getTable();

        $productRequest = DB::table($productTableName . ' as p');

        if (trim($searchTerm) != '') {
            $productRequest->where(function ($query) use ($searchTerm) {
                $query->where('p.name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('p.sku', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $productRequest->where('p.status', Product::ACTIVE_YES);

        $productRequest->select(
            'p.id as productId', 'p.name as productName', 'p.sku as productSku'
        );

        if (is_numeric($limit) && ((int)$limit > 0)) {
            if (is_numeric($page) && ((int)$page >= 0)) {
                $productRequest->offset(((int)$page * $limit));
            }
            $productRequest->limit((int)$limit);
        }

        return $productRequest->get();

    }

    public function getCategoriesSearchData($searchTerm = '', $page = 0, $limit = 0) {

        $returnResult = null;

        $categoryTableName = (new Category())->getTable();

        $categoryRequest = DB::table($categoryTableName . ' as c');

        if (trim($searchTerm) != '') {
            $categoryRequest->where(function ($query) use ($searchTerm) {
                $query->where('c.name', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $categoryRequest->select(
            'c.id as categoryId', 'c.name as categoryName'
        );

        if (is_numeric($limit) && ((int)$limit > 0)) {
            if (is_numeric($page) && ((int)$page >= 0)) {
                $categoryRequest->offset(((int)$page * $limit));
            }
            $categoryRequest->limit((int)$limit);
        }

        return $categoryRequest->get();

    }

    public function getCustomersSearchData($searchTerm = '', $page = 0, $limit = 0) {

        $returnResult = null;

        $customerTableName = (new Customer())->getTable();

        $customerRequest = DB::table($customerTableName . ' as c');

        if (trim($searchTerm) != '') {
            $customerRequest->where(function ($query) use ($searchTerm) {
                $query->where(DB::raw("CONCAT(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,''))"), 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('c.email', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('c.phone', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $customerRequest->where('c.is_active', Customer::ACTIVE_YES);

        $customerRequest->select(
            'c.id as customerId', DB::raw("CONCAT(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) as customerName")
        );

        if (is_numeric($limit) && ((int)$limit > 0)) {
            if (is_numeric($page) && ((int)$page >= 0)) {
                $customerRequest->offset(((int)$page * $limit));
            }
            $customerRequest->limit((int)$limit);
        }

        return $customerRequest->get();

    }

    public function getCouponModesFilteredData($searchTerm = '', $start = 0, $limit = 0,  $sortColumn = '', $sortDir = 'asc') {

        $returnResult = null;

        $modeTableName = (new CouponMode())->getTable();
        $userTableName = (new User())->getTable();

        $modesRequest = DB::table($modeTableName . ' as m');

        $modesRequest->leftJoin($userTableName . ' as u', 'm.updated_by', '=',  'u.id');

        if (trim($searchTerm) != '') {
            $modesRequest->where(function ($query) use ($searchTerm) {
                $query->where('m.code', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('m.name', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $modesRequest->select(
            'm.id as modeId', 'm.code as modeCode', 'm.name as modeName', 'm.sort_order as modeSortOrder',
            'u.name as updatedBy', 'm.updated_at as updatedAt', 'm.is_active as isActive'
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

    public function processCouponModeImport($csvData = [], $userId = 0) {

        $returnMessage = [];

        $totalRecords = 0;
        $insertedRecords = 0;
        $updatedRecords = 0;

        $chunkedArraySize = 500;
        foreach (array_chunk($csvData, $chunkedArraySize, true) as $chunkedKey => $chunkedSheetData) {

            $targetModes = [];
            $modeNames = array_map('trim', array_column($chunkedSheetData, 'CouponModeCode'));
            $modeCodesSmall = array_map('strtolower', $modeNames);
            $modeCodesSnake = array_map([Str::class, 'snake'], $modeCodesSmall);
            $targetModesData = CouponMode::select('id', 'code', 'name')->whereIn('code', $modeCodesSnake)->get();
            if ($targetModesData && (count($targetModesData) > 0)) {
                $targetModesArray = $targetModesData->toArray();
                foreach ($targetModesArray as $modeEl) {
                    $targetModes[$modeEl['code']] = $modeEl;
                }
            }

            foreach ($chunkedSheetData as $row) {

                $modeCode = Str::snake(strtolower(trim($row['CouponModeCode'])));

                $tempRecord = [
                    'name' => trim($row['CouponMode']),
                    'sort_order' => trim($row['SortOrder']),
                    'is_active' => ((strtolower($row['Status']) == 'true') || (strtolower($row['Status']) == 'active')) ? CouponMode::ACTIVE_YES : CouponMode::ACTIVE_NO,
                    'updated_by' => $userId,
                ];

                $modeId = null;
                if (array_key_exists($modeCode, $targetModes)) {
                    $modeId = $targetModes[$modeCode]['id'];
                }

                $totalRecords++;
                if (is_null($modeId)) {
                    /*$tempRecord['code'] = $modeCode;
                    $tempRecord['created_by'] = $userId;
                    $modeObj = CouponMode::create($tempRecord);
                    if ($modeObj) {
                        $insertedRecords++;
                    }*/
                } else {
                    $modeObj = CouponMode::find($modeId);
                    $modeObj->fill($tempRecord)->save();
                    $updatedRecords++;
                }

            }

        }

        $skippedRecords = ($totalRecords - ($insertedRecords + $updatedRecords));

        if ($skippedRecords == $totalRecords) {
            return [
                'success' => false,
                'message' => 'No records are imported!'
            ];
        }

        return [
            'success' => true,
            'message' => 'Total Rows (' . $totalRecords . ') | Inserted (' . $insertedRecords . ') | Updated (' . $updatedRecords . ') | Not Inserted (' . $skippedRecords . ')'
        ];

    }

    public function processCouponModeExport() {

        $modeTableName = (new CouponMode())->getTable();

        $modesRequest = DB::table($modeTableName . ' as m');

        $modesRequest->select(
            'm.code as CouponModeCode', 'm.name as CouponMode', 'm.sort_order as SortOrder', 'm.is_active as Status'
        );

        $modesRequest->orderBy('SortOrder', 'ASC');

        return $modesRequest->get();

    }

    public function getCouponTypesFilteredData($searchTerm = '', $start = 0, $limit = 0,  $sortColumn = '', $sortDir = 'asc') {

        $returnResult = null;

        $typeTableName = (new CouponType())->getTable();
        $userTableName = (new User())->getTable();

        $typesRequest = DB::table($typeTableName . ' as t');

        $typesRequest->leftJoin($userTableName . ' as u', 't.updated_by', '=',  'u.id');

        if (trim($searchTerm) != '') {
            $typesRequest->where(function ($query) use ($searchTerm) {
                $query->where('t.code', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('t.name', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $typesRequest->select(
            't.id as typeId', 't.code as typeCode', 't.name as typeName', 't.sort_order as typeSortOrder',
            'u.name as updatedBy', 't.updated_at as updatedAt', 't.is_active as isActive'
        );

        if ((trim($sortColumn) != '') && ((strtolower(trim($sortDir)) == 'asc') || (strtolower(trim($sortDir)) == 'desc'))) {
            $typesRequest->orderBy($sortColumn, $sortDir);
        }

        $countRequest = $typesRequest;
        $returnResult['totalCount'] = $countRequest->count();

        if (is_numeric($start) && ((int)$start >= 0)) {
            $typesRequest->offset((int)$start);
        }

        if (is_numeric($limit) && ((int)$limit > 0)) {
            $typesRequest->limit((int)$limit);
        }

        $returnResult['filteredData'] = $typesRequest->get();

        return $returnResult;

    }

    public function processCouponTypeImport($csvData = [], $userId = 0) {

        $returnMessage = [];

        $totalRecords = 0;
        $insertedRecords = 0;
        $updatedRecords = 0;

        $chunkedArraySize = 500;
        foreach (array_chunk($csvData, $chunkedArraySize, true) as $chunkedKey => $chunkedSheetData) {

            $targetTypes = [];
            $typeNames = array_map('trim', array_column($chunkedSheetData, 'CouponTypeCode'));
            $typeCodesSmall = array_map('strtolower', $typeNames);
            $typeCodesSnake = array_map([Str::class, 'snake'], $typeCodesSmall);
            $targetTypesData = CouponType::select('id', 'code', 'name')->whereIn('code', $typeCodesSnake)->get();
            if ($targetTypesData && (count($targetTypesData) > 0)) {
                $targetTypesArray = $targetTypesData->toArray();
                foreach ($targetTypesArray as $typeEl) {
                    $targetTypes[$typeEl['code']] = $typeEl;
                }
            }

            foreach ($chunkedSheetData as $row) {

                $typeCode = Str::snake(strtolower(trim($row['CouponTypeCode'])));

                $tempRecord = [
                    'name' => trim($row['CouponType']),
                    'sort_order' => trim($row['SortOrder']),
                    'is_active' => ((strtolower($row['Status']) == 'true') || (strtolower($row['Status']) == 'active')) ? CouponType::ACTIVE_YES : CouponType::ACTIVE_NO,
                    'updated_by' => $userId,
                ];

                $typeId = null;
                if (array_key_exists($typeCode, $targetTypes)) {
                    $typeId = $targetTypes[$typeCode]['id'];
                }

                $totalRecords++;
                if (is_null($typeId)) {
                    $tempRecord['code'] = trim($row['CouponType']);
                    $tempRecord['created_by'] = $userId;
                    $typeObj = CouponType::create($tempRecord);
                    if ($typeObj) {
                        $insertedRecords++;
                    }
                } else {
                    $typeObj = CouponType::find($typeId);
                    $typeObj->fill($tempRecord)->save();
                    $updatedRecords++;
                }

            }

        }

        $skippedRecords = ($totalRecords - ($insertedRecords + $updatedRecords));

        if ($skippedRecords == $totalRecords) {
            return [
                'success' => false,
                'message' => 'No records are imported!'
            ];
        }

        return [
            'success' => true,
            'message' => 'Total Rows (' . $totalRecords . ') | Inserted (' . $insertedRecords . ') | Updated (' . $updatedRecords . ') | Not Inserted (' . $skippedRecords . ')'
        ];

    }

    public function processCouponTypeExport() {

        $typeTableName = (new CouponType())->getTable();

        $typesRequest = DB::table($typeTableName . ' as t');

        $typesRequest->select(
            't.code as CouponTypeCode', 't.name as CouponType', 't.sort_order as SortOrder', 't.is_active as Status'
        );

        $typesRequest->orderBy('SortOrder', 'ASC');

        return $typesRequest->get();

    }

    public function getCouponEntitiesFilteredData($searchTerm = '', $start = 0, $limit = 0,  $sortColumn = '', $sortDir = 'asc') {

        $returnResult = null;

        $appTableName = (new CouponEntity())->getTable();
        $userTableName = (new User())->getTable();

        $appsRequest = DB::table($appTableName . ' as a');

        $appsRequest->leftJoin($userTableName . ' as u', 'a.updated_by', '=',  'u.id');

        if (trim($searchTerm) != '') {
            $appsRequest->where(function ($query) use ($searchTerm) {
                $query->where('a.code', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('a.name', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $appsRequest->select(
            'a.id as appId', 'a.code as appCode', 'a.name as appName', 'a.sort_order as appSortOrder',
            'u.name as updatedBy', 'a.updated_at as updatedAt', 'a.is_active as isActive'
        );

        if ((trim($sortColumn) != '') && ((strtolower(trim($sortDir)) == 'asc') || (strtolower(trim($sortDir)) == 'desc'))) {
            $appsRequest->orderBy($sortColumn, $sortDir);
        }

        $countRequest = $appsRequest;
        $returnResult['totalCount'] = $countRequest->count();

        if (is_numeric($start) && ((int)$start >= 0)) {
            $appsRequest->offset((int)$start);
        }

        if (is_numeric($limit) && ((int)$limit > 0)) {
            $appsRequest->limit((int)$limit);
        }

        $returnResult['filteredData'] = $appsRequest->get();

        return $returnResult;

    }

    public function processCouponEntityImport($csvData = [], $userId = 0) {

        $returnMessage = [];

        $totalRecords = 0;
        $insertedRecords = 0;
        $updatedRecords = 0;

        $chunkedArraySize = 500;
        foreach (array_chunk($csvData, $chunkedArraySize, true) as $chunkedKey => $chunkedSheetData) {

            $targetApps = [];
            $appNames = array_map('trim', array_column($chunkedSheetData, 'CouponEntityCode'));
            $appCodesSmall = array_map('strtolower', $appNames);
            $appCodesSnake = array_map([Str::class, 'snake'], $appCodesSmall);
            $targetAppsData = CouponEntity::select('id', 'code', 'name')->whereIn('code', $appCodesSnake)->get();
            if ($targetAppsData && (count($targetAppsData) > 0)) {
                $targetAppsArray = $targetAppsData->toArray();
                foreach ($targetAppsArray as $appEl) {
                    $targetApps[$appEl['code']] = $appEl;
                }
            }

            foreach ($chunkedSheetData as $row) {

                $appCode = Str::snake(strtolower(trim($row['CouponEntityCode'])));

                $tempRecord = [
                    'name' => trim($row['CouponEntity']),
                    'sort_order' => trim($row['SortOrder']),
                    'is_active' => ((strtolower($row['Status']) == 'true') || (strtolower($row['Status']) == 'active')) ? CouponEntity::ACTIVE_YES : CouponEntity::ACTIVE_NO,
                    'updated_by' => $userId,
                ];

                $appId = null;
                if (array_key_exists($appCode, $targetApps)) {
                    $appId = $targetApps[$appCode]['id'];
                }

                $totalRecords++;
                if (is_null($appId)) {
                    /*$tempRecord['code'] = $appCode;
                    $tempRecord['created_by'] = $userId;
                    $appObj = OfferApplication::create($tempRecord);
                    if ($appObj) {
                        $insertedRecords++;
                    }*/
                } else {
                    $appObj = CouponEntity::find($appId);
                    $appObj->fill($tempRecord)->save();
                    $updatedRecords++;
                }

            }

        }

        $skippedRecords = ($totalRecords - ($insertedRecords + $updatedRecords));

        if ($skippedRecords == $totalRecords) {
            return [
                'success' => false,
                'message' => 'No records are imported!'
            ];
        }

        return [
            'success' => true,
            'message' => 'Total Rows (' . $totalRecords . ') | Inserted (' . $insertedRecords . ') | Updated (' . $updatedRecords . ') | Not Inserted (' . $skippedRecords . ')'
        ];

    }

    public function processCouponEntityExport() {

        $appTableName = (new CouponEntity())->getTable();

        $appsRequest = DB::table($appTableName . ' as a');

        $appsRequest->select(
            'a.code as CouponEntityCode', 'a.name as CouponEntity', 'a.sort_order as SortOrder', 'a.is_active as Status'
        );

        $appsRequest->orderBy('SortOrder', 'ASC');

        return $appsRequest->get();

    }

    public function getCouponItemsFilteredData($applyTo = '', $searchTerm = '', $page = 0, $limit = 0) {

        if (is_null($applyTo) || !is_numeric($applyTo) || ((int)$applyTo <= 0)) {
            return [];
        }

        $returnResult = [];

        $applicationObj = CouponEntity::find((int)$applyTo);
        if ($applicationObj) {
            switch($applicationObj->code) {
                case CouponEntity::COUPON_ENTITY_CATEGORY:
                    $targetItems = $this->getCategoriesSearchData($searchTerm, $page, $limit);
                    if ($targetItems && (count($targetItems) > 0)) {
                        $appliedItems = [];
                        foreach ($targetItems as $itemEl) {
                            $appliedItems[$itemEl->categoryId] = [
                                'itemId' => $itemEl->categoryId,
                                'itemLabel' => $itemEl->categoryName,
                            ];
                        }
                        $returnResult = array_values($appliedItems);
                    }
                    break;
                case CouponEntity::COUPON_ENTITY_PRODUCT:
                    $targetItems = $this->getProductsSearchData($searchTerm, $page, $limit);
                    if ($targetItems && (count($targetItems) > 0)) {
                        $appliedItems = [];
                        foreach ($targetItems as $itemEl) {
                            $appliedItems[$itemEl->productId] = [
                                'itemId' => $itemEl->productId,
                                'itemLabel' => $itemEl->productName . ' (' . $itemEl->productSku . ')',
                            ];
                        }
                        $returnResult = array_values($appliedItems);
                    }
                    break;
                default:
                    break;
            }
        }

        return $returnResult;

    }

    public function fetchCouponList($customerId = 0) {

        if (is_null($customerId) || !is_numeric($customerId) || ((int)$customerId <= 0)) {
            return [
                'success' => false,
                'message' => 'Customer id is invalid!',
                'result' => []
            ];
        }

        $customerData = [];
        $customerObjData = Customer::where('id', $customerId)
            ->where('is_active', Customer::ACTIVE_YES)
            ->get();
        if ($customerObjData && (count($customerObjData) > 0)) {
            $customerObj = ($customerObjData instanceof Customer) ? $customerObjData : $customerObjData->first();
            $customerDataArray = $customerObj->toArray();
            $customerData = [
                'id' => $customerDataArray['id'],
                'f_name' => $customerDataArray['first_name'],
                'l_name' => $customerDataArray['last_name'],
                'mobile' => $customerDataArray['phone'],
                'email' => $customerDataArray['email'],
            ];
        }
        if (count($customerData) == 0) {
            return [
                'success' => false,
                'message' => 'Customer does not exists!',
                'result' => []
            ];
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $todayDateUTC = date('Y-m-d H:i:s');
        $todayDateClean = $this->getFormattedTime($todayDateUTC, 'Y-m-d H:i:s', true);

        $datetime = $todayDateClean;
        $currentTime = date("H:i:s", strtotime($todayDateClean));
        $todayDate = date('l,d M Y', (strtotime('0 day', strtotime($datetime))));
        $tomorrowsDate = date('l,d M Y', (strtotime('+1 day', strtotime($datetime))));

        $returnData = [];

        $couponList = [];
        $couponListData = Coupon::where('is_active', Coupon::ACTIVE_YES)
            ->whereRaw("start_date <= '" . $datetime . "'")
            ->whereRaw("end_date >= '" . $datetime . "'")
            ->get();
        if ($couponListData && (count($couponListData) > 0)) {
            $couponListDataArray = $couponListData->toArray();
            $couponIds = array_column($couponListDataArray, 'id');

            $currentCustomerUsage = [];
            $currentCustomerUsageData = Order::where('customer_id', $customerData['id'])
                ->whereIn('coupon_id', $couponIds)->get();
            if ($currentCustomerUsageData && (count($currentCustomerUsageData) > 0)) {
                foreach ($currentCustomerUsageData as $usageEl) {
                    $currentCustomerUsage[$usageEl->coupon_id][$usageEl->id] = $usageEl;
                }
            }

            foreach ($couponListData as $couponEl) {

                $maxUseCount = (int)$couponEl->max_usage_count;
                $maxUseCountPerUser = (int)$couponEl->max_count_per_user;
                $currentUsedCount = (int)$couponEl->used_count;
                $customerUsedCount = (array_key_exists($couponEl->id, $currentCustomerUsage)) ? count(array_keys($currentCustomerUsage[$couponEl->id])) : 0;

                if (($maxUseCount > 0) && ($maxUseCount <= $currentUsedCount)) {
                    continue;
                }
                if (($maxUseCountPerUser > 0) && ($maxUseCountPerUser <= $customerUsedCount)) {
                    continue;
                }

                $customerEligible = true;
                if ($couponEl->eligibleCustomers && ($couponEl->customer_eligibility == Coupon::CUSTOMER_ELIGIBILITY_SPECIFIC)) {
                    $customerEligible = false;
                    foreach ($couponEl->eligibleCustomers as $eligibleCustomer) {
                        if (($eligibleCustomer->target_id == $customerData['id']) && ($eligibleCustomer->is_active == CouponEligibilityMap::ACTIVE_YES)) {
                            $customerEligible = true;
                        }
                    }
                }
                if (!$customerEligible) {
                    continue;
                }

                $couponEl->couponType;
                if (in_array($couponEl->couponType->code, CouponType::COUPON_TYPE_ONE_USE_LIST) && ($customerUsedCount >= 1)) {
                    continue;
                }

                $couponList[] = [
                    'id' => $couponEl->id,
                    'code' => $couponEl->code,
                    'name' => $couponEl->name,
                    'description' => $couponEl->description,
                ];

            }
        }

        $returnData['success'] = (count($couponList) > 0) ? true : false;
        $returnData['message'] = (count($couponList) > 0) ?
            'The Coupons listed successfully!'
            : 'No Data found!';
        $returnData['result'] = (count($couponList) > 0) ? $couponList : [];

        return $returnData;

    }

    public function applyCouponToCart($customerId = 0, $couponCode = '') {

        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        if (is_null($customerId) || !is_numeric($customerId) || ((int)$customerId <= 0)) {
            return [
                'success' => false,
                'message' => 'Customer id is invalid!',
                'result' => []
            ];
        }

        if (is_null($couponCode) || (trim($couponCode) == '')) {
            return [
                'success' => false,
                'message' => 'Coupon code is invalid!',
                'result' => []
            ];
        }

        $customerData = [];
        $customerObjData = Customer::where('id', $customerId)
            ->where('is_active', Customer::ACTIVE_YES)
            ->get();
        if ($customerObjData && (count($customerObjData) > 0)) {
            $customerObj = ($customerObjData instanceof Customer) ? $customerObjData : $customerObjData->first();
            $customerDataArray = $customerObj->toArray();
            $customerData = [
                'id' => $customerDataArray['id'],
                'f_name' => $customerDataArray['first_name'],
                'l_name' => $customerDataArray['last_name'],
                'mobile' => $customerDataArray['phone'],
                'email' => $customerDataArray['email'],
            ];
        }
        if (count($customerData) == 0) {
            return [
                'success' => false,
                'message' => 'Customer does not exists!',
                'result' => []
            ];
        }

        $cartTableName = (new Cart())->getTable();

        $cartData = [];
        $cart = Cart::with('items.product.productImages')
            ->where('customer_id', $customerId)
            ->first();

        if (!$cart) {
            return [
                'success' => false,
                'message' => 'Cart not found.',
                'result' => []
            ];
        }

        $cartData = $cart->toArray();
        if (count($cartData) == 0) {
            return [
                'success' => false,
                'message' => 'Cart is empty!',
                'result' => []
            ];
        }

        $todayDateUTC = date('Y-m-d H:i:s');
        $todayDateClean = $this->getFormattedTime($todayDateUTC, 'Y-m-d H:i:s', true);

        $datetime = $todayDateClean;
        $currentTime = date("H:i:s", strtotime($todayDateClean));
        $todayDate = date('l,d M Y', (strtotime('0 day', strtotime($datetime))));
        $tomorrowsDate = date('l,d M Y', (strtotime('+1 day', strtotime($datetime))));

        $couponListData = Coupon::where('is_active', Coupon::ACTIVE_YES)
            ->whereRaw("start_date <= '" . $datetime . "'")
            ->whereRaw("end_date >= '" . $datetime . "'")
            ->whereRaw("code LIKE '" . $couponCode . "'")
            ->get();
        if (!$couponListData || (count($couponListData) == 0)) {
            return [
                'success' => false,
                'message' => 'Coupon  Expired / Invalid!',
                'result' => []
            ];
        }

        $returnData = [];

        $couponEl = ($couponListData instanceof Coupon) ? $couponListData : $couponListData->first();

        $currentCustomerUsage = [];
        $couponListDataArray = $couponListData->toArray();
        $couponIds = array_column($couponListDataArray, 'id');

        $currentCustomerUsageData = Order::where('customer_id', $customerData['id'])
            ->whereIn('coupon_id', $couponIds)->get();
        if ($currentCustomerUsageData && (count($currentCustomerUsageData) > 0)) {
            foreach ($currentCustomerUsageData as $usageEl) {
                $currentCustomerUsage[$usageEl->coupon_id][$usageEl->id] = $usageEl;
            }
        }

        $maxUseCount = $couponEl->max_usage_count;
        $maxUseCountPerUser = $couponEl->max_count_per_user;
        $currentUsedCount = (int)$couponEl->used_count;
        $customerUsedCount = (array_key_exists($couponEl->id, $currentCustomerUsage)) ? count(array_keys($currentCustomerUsage[$couponEl->id])) : 0;

        if (!is_null($maxUseCount) && ((int)$maxUseCount > 0) && ((int)$maxUseCount < $currentUsedCount)) {
            return [
                'success' => false,
                'message' => 'Coupon reached its max limit!',
                'result' => []
            ];
        }
        if (!is_null($maxUseCountPerUser) && ((int)$maxUseCountPerUser > 0) && ((int)$maxUseCountPerUser < $customerUsedCount)) {
            return [
                'success' => false,
                'message' => 'Coupon reached its max limit!',
                'result' => []
            ];
        }

        $customerEligible = true;
        if ($couponEl->eligibleCustomers && ($couponEl->customer_eligibility == Coupon::CUSTOMER_ELIGIBILITY_SPECIFIC)) {
            $customerEligible = false;
            foreach ($couponEl->eligibleCustomers as $eligibleCustomer) {
                if (($eligibleCustomer->target_id == $customerData['id']) && ($eligibleCustomer->is_active == CouponEligibilityMap::ACTIVE_YES)) {
                    $customerEligible = true;
                }
            }
        }
        if (!$customerEligible) {
            return [
                'success' => false,
                'message' => 'Not eligible to use the Coupon!!',
                'result' => []
            ];
        }

        $couponEl->couponType;
        if (in_array($couponEl->couponType->code, CouponType::COUPON_TYPE_ONE_USE_LIST) && ($customerUsedCount >= 1)) {
            return [
                'success' => false,
                'message' => 'Coupon reached its max limit!',
                'result' => []
            ];
        }

        $this->clearCouponData($cartData['id']);

        $productTableName = (new Product())->getTable();
        $cartItemTableName = (new CartItem())->getTable();

        $cartItemTargetData = DB::table($cartItemTableName . ' as cp')
            ->leftJoin($productTableName . ' as p', 'cp.product_id', '=',  'p.id')
            ->selectRaw("cp.id, cp.cart_id, cp.product_id, cp.quantity, cp.product_price, cp.price, cp.product_special_price, cp.product_special_price_from, cp.product_special_price_to, p.name, p.sku")
            ->where([
                'p.status' => Product::ACTIVE_YES,
                'cp.cart_id' => $cartData['id']
            ])->get();
        
        if (!$cartItemTargetData || ($cartItemTargetData->count() == 0)) {
            return [
                'success' => false,
                'message' => 'Cannot apply coupon, cart is empty!',
                'result' => []
            ];
        }
        $cartItemsList = json_decode($cartItemTargetData->toJson(), true);

        $minCartValue = (float)$couponEl->min_cart_value;
        $cartTotal = 0;
        $cartAmountCalculated = false;
        $matchProductCount = 0;
        $couponProductIds = [];

        if ($couponEl->couponEntity && ($couponEl->couponEntity->code != CouponEntity::COUPON_ENTITY_ALL)) {

            $productFound = false;
            foreach ($cartItemsList as $key => $value) {
                $cartAmountCalculated = true;

                $currentProductPrice = 0;
                $couponApplyCheck = $this->checkCouponValidByProductId($value['product_id'], $couponEl->id);
                if (!is_null($couponApplyCheck)) {
                    $matchProductCount += 1;
                    $productFound = true;
                    $couponProductIds[] = $value['product_id'];
                    $currentProductPrice = (double)$couponApplyCheck['productPrice']['price'];
                } else {
                    $currentProductObj = Product::find((int)$value['product_id']);
                    if ($currentProductObj) {
                        $currentProductPrice = (double)$currentProductObj->price;
                    }
                }

                $currentDate = now();
                $specialPrice = (double)$value['product_special_price'] ?? 0;
                $specialPriceFrom = $value['product_special_price_from'];
                $specialPriceTo = $value['product_special_price_to'];
                $productPrice = $currentProductPrice;
                $productQty = (double)$value['quantity'] ?? 0;

                $isSpecialPriceValid = false;
                if ($specialPrice && $specialPriceFrom && $specialPriceTo) {
                    $isSpecialPriceValid = $currentDate->between($specialPriceFrom, $specialPriceTo);
                }

                $finalPrice = $isSpecialPriceValid ? $specialPrice : $productPrice;
                $cartTotal += $finalPrice * $productQty;
            }

            if (!$productFound) {
                return [
                    'success' => false,
                    'message' => 'Product not found, cannot apply coupon!',
                    'result' => []
                ];
            }

        }

        if (!$cartAmountCalculated) {
            foreach ($cartItemsList as $key => $value) {
                $cartAmountCalculated = true;
                $currentProductPrice = 0;
                $currentProductObj = Product::find((int)$value['product_id']);
                if ($currentProductObj) {
                    $currentProductPrice = (double)$currentProductObj->price;
                }
                $currentDate = now();
                $specialPrice = (double)$value['product_special_price'] ?? 0;
                $specialPriceFrom = $value['product_special_price_from'];
                $specialPriceTo = $value['product_special_price_to'];
                $productPrice = $currentProductPrice;
                $productQty = (double)$value['quantity'] ?? 0;

                $isSpecialPriceValid = false;
                if ($specialPrice && $specialPriceFrom && $specialPriceTo) {
                    $isSpecialPriceValid = $currentDate->between($specialPriceFrom, $specialPriceTo);
                }
                $finalPrice = $isSpecialPriceValid ? $specialPrice : $productPrice;
                $cartTotal += $finalPrice * $productQty;
            }
        }

        if (($cartTotal - $minCartValue) < 0) {
            return [
                'success' => false,
                'message' => 'Minimum cart amount should be ' . $minCartValue,
                'result' => []
            ];
        }

        if (($couponEl->order_eligibility != Coupon::ORDER_ELIGIBILITY_ALL) && ((int)$couponEl->order_eligibility_value > 0)) {

            $previousOrderCount = 0;
            $eligibleOrderCount = (int)$couponEl->order_eligibility_value;

            $checkOrderPlaced = Order::where('customer_id', $customerData['id'])->where('is_active', Order::ACTIVE_YES)->get();
            if ($checkOrderPlaced && (count($checkOrderPlaced) > 0)) {
                $previousOrderCount = count($checkOrderPlaced);
            }

            if (($couponEl->order_eligibility == Coupon::ORDER_ELIGIBILITY_GREATER_THAN) && ($previousOrderCount <= $eligibleOrderCount)) {
                return [
                    'success' => false,
                    'message' => 'Need to place ' . ($eligibleOrderCount - $previousOrderCount) . ' more Orders to avail this coupon.',
                    'result' => []
                ];
            }

            if (($couponEl->order_eligibility == Coupon::ORDER_ELIGIBILITY_EQUALS) && ($previousOrderCount != $eligibleOrderCount)) {
                return [
                    'success' => false,
                    'message' => 'Need to place exactly ' . $eligibleOrderCount . ' Orders to avail this coupon.',
                    'result' => []
                ];
            }

            if (($couponEl->order_eligibility == Coupon::ORDER_ELIGIBILITY_LESS_THAN) && ($previousOrderCount != $eligibleOrderCount)) {
                return [
                    'success' => false,
                    'message' => 'Cannot avail this coupon as the Sale Order count exceeded the maximum allowed Orders.',
                    'result' => []
                ];
            }

        }

        $couponEl->couponMode;
        $couponEl->couponEntity;

        $canProceedCouponApply = true;
        if ($couponEl->couponType->code == CouponType::COUPON_TYPE_MANUAL) {
            $canProceedCouponApply = true;
        } elseif ($couponEl->couponType->code == CouponType::COUPON_TYPE_FIRST_TIME_USER) {
            $checkOrderPlaced = Order::where('customer_id', $customerData['id'])->where('is_active', Order::ACTIVE_YES)->get();
            if ($checkOrderPlaced && (count($checkOrderPlaced) > 0)) {
                $canProceedCouponApply = false;
                return [
                    'success' => false,
                    'message' => 'You are not eligible as you are already our customer.',
                    'result' => []
                ];
            }
        }

        if ($canProceedCouponApply) {

            if ($couponEl->couponEntity->code == CouponEntity::COUPON_ENTITY_ALL) {

                $discountAmountValue = 0;
                if ($couponEl->couponMode->code == CouponMode::COUPON_MODE_AMOUNT) {
                    $discountAmountValue = (($cartTotal - (float)$couponEl->discount_value) < 0) ? $cartTotal : (float)$couponEl->discount_value;
                } elseif ($couponEl->couponMode->code == CouponMode::COUPON_MODE_PERCENTAGE) {
                    $discountAmountValue = (($cartTotal * (float)$couponEl->discount_value) / 100);
                    if (((int)$couponEl->has_max_limit == Coupon::MAX_LIMIT_YES) && ($discountAmountValue > (float)$couponEl->max_discount_value)) {
                        $discountAmountValue = (float)$couponEl->max_discount_value;
                    }
                }

                if ($discountAmountValue > 0) {
                    $updatedData = [
                        'coupon_id' => $couponEl->id,
                        'total_coupon_amount' => $discountAmountValue
                    ];
                    $updatedCart = Cart::where('id', $cartData['id'])->update($updatedData);
                }

                return [
                    'success' => true,
                    'message' => 'Coupon Applied successfully!',
                    'result' => []
                ];

            } else {

                if ((count($couponProductIds) > 0) && ($matchProductCount > 0)) {

                    $discountAmountValue = 0;

                    if ($couponEl->couponMode->code == CouponMode::COUPON_MODE_AMOUNT) {
                        foreach ($cartItemsList as $key => $value) {
                            if (in_array($value['product_id'], $couponProductIds)) {
                                $currentProductPrice = 0;
                                $currentProductObj = Product::find((int)$value['product_id']);
                                if ($currentProductObj) {
                                    $currentProductPrice = (double)$currentProductObj->price;
                                }
                                $currentDate = now();
                                $specialPrice = (double)$value['product_special_price'] ?? 0;
                                $specialPriceFrom = $value['product_special_price_from'];
                                $specialPriceTo = $value['product_special_price_to'];
                                $productPrice = $currentProductPrice;
                                $productQty = (double)$value['quantity'] ?? 0;

                                $isSpecialPriceValid = false;
                                if ($specialPrice && $specialPriceFrom && $specialPriceTo) {
                                    $isSpecialPriceValid = $currentDate->between($specialPriceFrom, $specialPriceTo);
                                }
                                $finalPrice = $isSpecialPriceValid ? $specialPrice : $productPrice;
                                $rowTotal = $finalPrice * $productQty;
                                $productDiscountAmountValue = (($rowTotal - (float)$couponEl->discount_value) < 0) ? $rowTotal : (float)$couponEl->discount_value;
                                $discountAmountValue += $productDiscountAmountValue;
                            }
                        }
                    } elseif ($couponEl->couponMode->code == CouponMode::COUPON_MODE_PERCENTAGE) {
                        foreach ($cartItemsList as $key => $value) {
                            if (in_array($value['product_id'], $couponProductIds)) {
                                $currentProductPrice = 0;
                                $currentProductObj = Product::find((int)$value['product_id']);
                                if ($currentProductObj) {
                                    $currentProductPrice = (double)$currentProductObj->price;
                                }
                                $currentDate = now();
                                $specialPrice = (double)$value['product_special_price'] ?? 0;
                                $specialPriceFrom = $value['product_special_price_from'];
                                $specialPriceTo = $value['product_special_price_to'];
                                $productPrice = $currentProductPrice;
                                $productQty = (double)$value['quantity'] ?? 0;

                                $isSpecialPriceValid = false;
                                if ($specialPrice && $specialPriceFrom && $specialPriceTo) {
                                    $isSpecialPriceValid = $currentDate->between($specialPriceFrom, $specialPriceTo);
                                }
                                $finalPrice = $isSpecialPriceValid ? $specialPrice : $productPrice;
                                $rowTotal = $finalPrice * $productQty;
                                $productDiscountAmountValue = (($rowTotal * (float)$couponEl->discount_value) / 100);
                                if (((int)$couponEl->has_max_limit == Coupon::MAX_LIMIT_YES) && ($productDiscountAmountValue > (float)$couponEl->max_discount_value)) {
                                    $productDiscountAmountValue = (float)$couponEl->max_discount_value;
                                }
                                $discountAmountValue += $productDiscountAmountValue;
                            }
                        }
                    }

                    if ($discountAmountValue > 0) {
                        $updatedData = [
                            'coupon_id' => $couponEl->id,
                            'total_coupon_amount' => $discountAmountValue
                        ];
                        $updatedCart = Cart::where('id', $cartData['id'])->update($updatedData);
                    }

                    return [
                        'success' => true,
                        'message' => 'Coupon Applied successfully!',
                        'result' => []
                    ];

                } else {
                    return [
                        'success' => false,
                        'message' => 'Product not found, cannot apply coupon!',
                        'result' => []
                    ];
                }

            }

        }

        $returnData['success'] = false;
        $returnData['message'] = 'Coupon could not apply!';
        $returnData['result'] = [];

        return $returnData;

    }

    public function removeCouponFromCart($customerId = 0) {

        if (is_null($customerId) || !is_numeric($customerId) || ((int)$customerId <= 0)) {
            return [
                'success' => false,
                'message' => 'Customer id is invalid!',
                'result' => []
            ];
        }

        $customerData = [];
        $customerObjData = Customer::where('id', $customerId)
            ->where('is_active', Customer::ACTIVE_YES)
            ->get();
        if ($customerObjData && (count($customerObjData) > 0)) {
            $customerObj = ($customerObjData instanceof Customer) ? $customerObjData : $customerObjData->first();
            $customerDataArray = $customerObj->toArray();
            $customerData = [
                'id' => $customerDataArray['id'],
                'f_name' => $customerDataArray['first_name'],
                'l_name' => $customerDataArray['last_name'],
                'mobile' => $customerDataArray['phone'],
                'email' => $customerDataArray['email'],
            ];
        }
        if (count($customerData) == 0) {
            return [
                'success' => false,
                'message' => 'Customer does not exists!',
                'result' => []
            ];
        }

        $cart = Cart::with('items.product.productImages')
            ->where('customer_id', $customerId)
            ->first();

        if (!$cart) {
            return [
                'success' => false,
                'message' => 'Cart not found.',
                'result' => []
            ];
        }

        $this->clearCouponData($cart->id);

        $returnData['success'] = true;
        $returnData['message'] = 'The Coupons cleared successfully!';
        $returnData['result'] = [];

        return $returnData;

    }

    public function clearCouponData($cartId) {

        $updatedData = [
            'coupon_id' => null,
            'total_coupon_amount' => 0
        ];
        $updatedCart = Cart::where('id', $cartId)->update($updatedData);

        $productUpdateCart = CartItem::where('cart_id', $cartId)->update([
            'coupon_id' => null,
        ]);

        return true;

    }

    public function checkCouponValidByProductId($productId = null, $couponId = null) {

        if (is_null($productId) || !is_numeric($productId) || ((int)$productId <= 0)) {
            return null;
        }

        if (is_null($couponId) || !is_numeric($couponId) || ((int)$couponId <= 0)) {
            return null;
        }

        $productData = Product::where('id', (int)$productId)
            ->where('status', Product::ACTIVE_YES)
            ->limit(1)
            ->get();
        if (!$productData || (count($productData) == 0)) {
            return null;
        }

        $productDetailsObj = ($productData instanceof Product) ? $productData : $productData->first();

        $productPrice = (float)$productDetailsObj->price ?? 0;
        $productSpecialPrice = (float)$productDetailsObj->special_price ?? 0;
        $priceDifference = $productPrice - $productSpecialPrice;
        $priceDiscount = ($productPrice > 0) ? round((($priceDifference) / $productPrice) * 100) : 0;
        if (($productPrice > 0) && ($priceDifference > 0)) {
            return null;
        }

        $productDetails = $productDetailsObj->toArray();

        $todayDate = date('Y-m-d');

        $couponTableName = (new Coupon())->getTable();
        $couponModeTableName = (new CouponMode())->getTable();
        $couponEntityTableName = (new CouponEntity())->getTable();
        $couponTypeTableName = (new CouponType())->getTable();

        $currentTargetCouponData = DB::table($couponTableName . ' as o')
            ->leftJoin($couponModeTableName . ' as om', 'o.mode_id', '=',  'om.id')
            ->leftJoin($couponEntityTableName . ' as oa', 'o.entity_id', '=',  'oa.id')
            ->leftJoin($couponTypeTableName . ' as ot', 'o.type_id', '=',  'ot.id')
            ->where('o.is_active', Coupon::ACTIVE_YES)
            ->where('o.start_date', '<=', $todayDate)
            ->where('o.end_date', '>=', $todayDate)
            ->where('o.id', $couponId)
            ->select('o.*', 'om.code as modeCode', 'oa.code as entityCode', 'ot.code as typeCode')
            ->limit(1)
            ->get();
        if ($currentTargetCouponData->count() == 0) {
            return null;
        }

        $couponData = [];
        if ($currentTargetCouponData->count() > 0) {
            $currentTargetCouponArrayData = json_decode($currentTargetCouponData->toJson(), true);
            $couponData = $currentTargetCouponArrayData[0];
        }

        $returnData = [
            'productDetails' => $productDetails,
            'productPrice' => [
                'productId' => $productDetails['id'],
                'price' => $productPrice,
                'specialPrice' => $productSpecialPrice,
                'productDiscount' => $priceDiscount,
                'special_price_from' => $productDetails['special_price_from'],
                'special_price_to' => $productDetails['special_price_to'],
            ],
            'productCoupon' => $couponData
        ];

        $couponProductIds = $this->getProductIdsOfCouponById([$couponData['id']], $productId);
        if (count($couponProductIds) == 0) {
            if ($couponData['entityCode'] == CouponEntity::COUPON_ENTITY_ALL) {
                return $returnData;
            }
            return null;
        } else {
            return $returnData;
        }

    }

    public function getProductIdsOfCouponById($couponIds = [], $productId = '') {

        if (is_null($couponIds) || !is_array($couponIds) || (count($couponIds) == 0)) {
            return [];
        }

        $couponProductIds = [];

        $todayDateUTC = date('Y-m-d H:i:s');
        $todayDateClean = $this->getFormattedTime($todayDateUTC, 'Y-m-d H:i:s', true);
        $todayDate = date('Y-m-d', strtotime($todayDateClean));

        $couponTableName = (new Coupon())->getTable();
        $couponModeTableName = (new CouponMode())->getTable();
        $couponEntityTableName = (new CouponEntity())->getTable();
        $couponEntityMapTableName = (new CouponEntityMap())->getTable();
        $productTableName = (new Product())->getTable();
        $categoryTableName = (new Category())->getTable();
        $categoryProductMapTableName = (new CategoryProduct())->getTable();

        $currentTargetCouponData = DB::table($couponTableName . ' as o')
            ->leftJoin($couponModeTableName . ' as om', 'o.mode_id', '=',  'om.id')
            ->leftJoin($couponEntityTableName . ' as oa', 'o.entity_id', '=',  'oa.id')
            ->where('o.is_active', Coupon::ACTIVE_YES)
            ->where('o.start_date', '<=', $todayDate)
            ->where('o.end_date', '>=', $todayDate)
            ->whereIn('o.id', $couponIds)
            ->select('o.*', 'om.code as modeCode', 'oa.code as entityCode')
            ->get();
        if ($currentTargetCouponData->count() == 0) {
            return [];
        }

        $couponData = [];
        if ($currentTargetCouponData->count() > 0) {
            $currentTargetCouponArrayData = json_decode($currentTargetCouponData->toJson(), true);
            foreach ($currentTargetCouponArrayData as $couponEl) {

                switch ($couponEl['entityCode']) {
                    case CouponEntity::COUPON_ENTITY_PRODUCT:
                        $couponRequestDataQ = DB::table($productTableName . ' as p')
                            ->leftJoin($couponEntityMapTableName . ' as oam', 'oam.target_id', '=',  'p.id')
                            ->leftJoin($couponEntityTableName . ' as oa', 'oam.entity_id', '=',  'oa.id')
                            ->leftJoin($couponTableName . ' as o', 'oam.coupon_id', '=',  'o.id')
                            ->where('p.status', Product::ACTIVE_YES)
                            ->where('oa.is_active', CouponEntity::ACTIVE_YES)
                            ->where('oam.is_active', CouponEntityMap::ACTIVE_YES)
                            ->where('o.is_active', Coupon::ACTIVE_YES)
                            ->where('o.id', $couponEl['id']);
                        if (!is_null($productId) && (trim($productId) != '') && is_numeric($productId) && ((int)$productId > 0)) {
                            $couponRequestDataQ->where('p.id', (int)$productId);
                        }
                        $couponRequestData = $couponRequestDataQ
                            ->select('o.id as oId', 'p.id as pId')
                            ->get();
                        if ($couponRequestData->count() > 0) {
                            $couponListArrayData = json_decode($couponRequestData->toJson(), true);
                            foreach ($couponListArrayData as $targetEl) {
                                $couponProductIds[] = $targetEl['pId'];
                            }
                        }
                        break;
                    case CouponEntity::COUPON_ENTITY_CATEGORY:
                        $couponRequestDataQ = DB::table($productTableName . ' as p')
                            ->leftJoin($categoryProductMapTableName . ' as cpm', 'cpm.product_id', '=',  'p.id')
                            ->leftJoin($categoryTableName . ' as col', 'cpm.category_id', '=',  'col.id')
                            ->leftJoin($couponEntityMapTableName . ' as oam', 'oam.target_id', '=',  'col.id')
                            ->leftJoin($couponEntityTableName . ' as oa', 'oam.entity_id', '=',  'oa.id')
                            ->leftJoin($couponTableName . ' as o', 'oam.coupon_id', '=',  'o.id')
                            ->where('p.status', Product::ACTIVE_YES)
                            ->where('oa.is_active', CouponEntity::ACTIVE_YES)
                            ->where('oam.is_active', CouponEntityMap::ACTIVE_YES)
                            ->where('o.is_active', Coupon::ACTIVE_YES)
                            ->where('o.id', $couponEl['id']);
                        if (!is_null($productId) && (trim($productId) != '') && is_numeric($productId) && ((int)$productId > 0)) {
                            $couponRequestDataQ->where('p.id', (int)$productId);
                        }
                        $couponRequestData = $couponRequestDataQ
                            ->select('o.id as oId', 'p.id as pId')
                            ->get();
                        if ($couponRequestData->count() > 0) {
                            $couponListArrayData = json_decode($couponRequestData->toJson(), true);
                            foreach ($couponListArrayData as $targetEl) {
                                $couponProductIds[] = $targetEl['pId'];
                            }
                        }
                        break;
                    default:
                        break;
                }

            }
        }

        return array_unique($couponProductIds);

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
