<?php

namespace Modules\Base\Services;

use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\Sheet;
use Google\Service\Sheets\Request as BaseSheetRequest;
use Google\Service\Sheets\SheetProperties;
use Google\Service\Sheets\UpdateSpreadsheetPropertiesRequest;
use Google\Service\Sheets\AddSheetRequest;
use Google\Service\Sheets\AddSheetResponse;
use Google\Service\Sheets\UpdateSheetPropertiesRequest;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Google\Service\Sheets\BatchUpdateValuesResponse;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ClearValuesResponse;
use Google\Service\Sheets\BatchClearValuesRequest;
use Google\Service\Sheets\BatchClearValuesResponse;
use Google\Service\Sheets\UpdateCellsRequest;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\BatchUpdateSpreadsheetResponse;
use Google\Service\Drive;
use Google\Service\Drive\Permission as DrivePermission;
use Google\Service\Drive\Resource\Permissions as DriveResourcePermissions;
use Google\Exception as GoogleException;
use GuzzleHttp\Exception\GuzzleException;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\WebConfigurationManagement\Models\CoreConfigData;

class CustomGoogleSheetService
{

    const VALUE_INPUT_OPTION_UNSPECIFIED = 'INPUT_VALUE_OPTION_UNSPECIFIED';
    const VALUE_INPUT_OPTION_RAW = 'RAW';
    const VALUE_INPUT_OPTION_USER_ENTERED = 'USER_ENTERED';
    const VALUE_INPUT_OPTION_LIST = [
        self::VALUE_INPUT_OPTION_UNSPECIFIED => 'Unspecified',
        self::VALUE_INPUT_OPTION_RAW => 'Raw',
        self::VALUE_INPUT_OPTION_USER_ENTERED => 'User Entered',
    ];

    const SHEET_DIMENSION_UNSPECIFIED = 'DIMENSION_UNSPECIFIED';
    const SHEET_DIMENSION_ROWS = 'ROWS';
    const SHEET_DIMENSION_COLUMNS = 'COLUMNS';
    const SHEET_DIMENSION_LIST = [
        self::SHEET_DIMENSION_UNSPECIFIED => 'Unspecified',
        self::SHEET_DIMENSION_ROWS => 'Rows',
        self::SHEET_DIMENSION_COLUMNS => 'Columns',
    ];

    const SPREADSHEET_ACCESS_TYPE_OFFLINE = 'offline';
    const SPREADSHEET_ACCESS_TYPE_ONLINE = 'online';
    const SPREADSHEET_ACCESS_TYPE_LIST = [
        self::SPREADSHEET_ACCESS_TYPE_OFFLINE => 'Offline',
        self::SPREADSHEET_ACCESS_TYPE_ONLINE => 'Online',
    ];

    const SPREADSHEET_MAJOR_DIMENSION_ROWS = 'ROWS';
    const SPREADSHEET_MAJOR_DIMENSION_COLUMNS = 'COLUMNS';
    const SPREADSHEET_MAJOR_DIMENSION_LIST = [
        self::SPREADSHEET_MAJOR_DIMENSION_ROWS => 'Rows',
        self::SPREADSHEET_MAJOR_DIMENSION_COLUMNS => 'Columns',
    ];

    private $googleConfigKey = 'customConfigs.google';
    private $firebaseConfigKey = 'customConfigs.google.firebase';

    private $googleServiceAccountJsonPath = '';
    private $googleClientCredentialsJsonPath = '';
    private $sheetRangePool = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    private $serviceInstance = null;
    private $driveServiceInstance = null;
    private $clientApplicationName = 'DoDelivery Google Sheets API';
    private $googleClientAuthCode = '';

    public function __construct()
    {
        $this->setGoogleSheetsServiceVariables();
        $this->setGoogleSheetsClientService();
    }

    /**
     * Create a Google Spreadsheet and return its ID
     *
     * @param string|null $sheetTitle
     *
     * @return string|null
     */
    public function createGoogleSpreadsheetByTitle(?string $sheetTitle = ''): ?string
    {

        try {

            if (is_null($sheetTitle) || (trim($sheetTitle) == '')) {
                return null;
            }

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return null;
            }

            $spreadSheetMainObj = new Spreadsheet([
                'properties' => [
                    'title' => $sheetTitle
                ]
            ]);

            $spreadsheetObj = $this->serviceInstance->spreadsheets->create($spreadSheetMainObj, [
                'fields' => 'spreadsheetId'
            ]);

            return $spreadsheetObj->spreadsheetId;

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet Creation Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     *
     *
     * @param string|null $spreadsheetId
     * @param string|null $userEmail
     *
     * @return bool
     */
    public function setUserPermissionForGoogleSpreadsheet(?string $spreadsheetId = '', ?string $userEmail = ''): bool
    {

        try {

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return false;
            }

            if (is_null($this->driveServiceInstance) || !($this->driveServiceInstance instanceof Drive)) {
                return false;
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return false;
            }

            if (is_null($userEmail) || (trim($userEmail) == '')) {
                return false;
            }

            $divePermissionObj = new DrivePermission();
            $divePermissionObj->setType('user');
            $divePermissionObj->setRole('writer');
            $divePermissionObj->setEmailAddress($userEmail);
            $sheetPermissionObj = $this->driveServiceInstance->permissions->create($spreadsheetId, $divePermissionObj, ['sendNotificationEmail' => true]);

            return true;

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet Permission Setting Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return false;
        }

    }

    /**
     * Get the Google Spreadsheet by ID
     *
     * @param string|null $spreadsheetId
     *
     * @return Spreadsheet|null
     */
    public function getGoogleSpreadsheetById(?string $spreadsheetId = ''): ?Spreadsheet
    {

        try {

            if (is_null($spreadsheetId) || (trim($spreadsheetId) == '')) {
                return null;
            }

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return null;
            }

            return $this->serviceInstance->spreadsheets->get($spreadsheetId);

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet Retrieval Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Fetch all the WorkSheets in the Google Spreadsheet.
     *
     * @param string|null $spreadsheetId
     *
     * @return array
     */
    public function getAllWorkSheetInGoogleSpreadsheet(?string $spreadsheetId = ''): array
    {

        try {

            if (is_null($spreadsheetId) || (trim($spreadsheetId) == '')) {
                return [];
            }

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return [];
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return [];
            }

            $workSheetArray = [];
            $currentWorkSheets = $spreadsheetObj->getSheets();
            foreach ($currentWorkSheets as $currentWorkSheet) {
                $workSheetArray[] = [
                    'id' => $currentWorkSheet->getProperties()->getSheetId(),
                    'title' => $currentWorkSheet->getProperties()->getTitle(),
                    'index' => $currentWorkSheet->getProperties()->getIndex(),
                    'type' => $currentWorkSheet->getProperties()->getSheetType(),
                ];
            }

            return $workSheetArray;

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet WorkSheet Retrieve Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return [];
        }

    }

    /**
     * Create a WorkSheet in the Google Spreadsheet.
     *
     * @param string|null $spreadsheetId
     * @param string|null $sheetName
     *
     * @return array|null
     */
    public function createWorkSheetInGoogleSpreadsheet(?string $spreadsheetId = '', ?string $sheetName = ''): ?array
    {

        try {

            if (is_null($spreadsheetId) || (trim($spreadsheetId) == '')) {
                return null;
            }

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return null;
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return null;
            }

            if (is_null($sheetName) || (trim($sheetName) == '')) {
                return null;
            }

            $isSheetNamePresent = false;
            $currentWorkSheets = $this->getAllWorkSheetInGoogleSpreadsheet($spreadsheetId);
            foreach ($currentWorkSheets as $currentWorkSheet) {
                if ($currentWorkSheet['title'] == $sheetName) {
                    $isSheetNamePresent = true;
                }
            }

            if (!$isSheetNamePresent) {
                $sheetRequests = [];
                $newSheetProps = new SheetProperties();
                $newSheetProps->setTitle($sheetName);
                $addSheetRequest = new AddSheetRequest();
                $addSheetRequest->setProperties($newSheetProps);
                $sheetRequest = new BaseSheetRequest();
                $sheetRequest->setAddSheet($addSheetRequest);
                $sheetRequests[] = $sheetRequest;
                $batchSheetRequest = new BatchUpdateSpreadsheetRequest();
                $batchSheetRequest->setRequests($sheetRequests);
                $sheetBatchUpdateResult = $this->serviceInstance->spreadsheets->batchUpdate($spreadsheetId, $batchSheetRequest);
            }

            return $this->getAllWorkSheetInGoogleSpreadsheet($spreadsheetId);

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet WorkSheet Create Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Update the WorkSheet Title in the Google Spreadsheet.
     *
     * @param string|null $spreadsheetId
     * @param string|null $workSheetId
     * @param string|null $sheetName
     *
     * @return array|null
     */
    public function updateWorkSheetTitleInGoogleSpreadsheet(?string $spreadsheetId = '', ?string $workSheetId = '', ?string $sheetName = ''): ?array
    {

        try {

            if (is_null($spreadsheetId) || (trim($spreadsheetId) == '')) {
                return null;
            }

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return null;
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return null;
            }

            if (is_null($workSheetId) || (trim($workSheetId) == '')) {
                return null;
            }

            if (is_null($sheetName) || (trim($sheetName) == '')) {
                return null;
            }

            $sheetRequests = [];
            $currentWorkSheets = $this->getAllWorkSheetInGoogleSpreadsheet($spreadsheetId);
            foreach ($currentWorkSheets as $currentWorkSheet) {
                $targetSheetId = $currentWorkSheet['id'];
                $targetSheetTitle = ($currentWorkSheet['id'] == $workSheetId) ? $sheetName : $currentWorkSheet['title'];
                $newSheetProps = new SheetProperties();
                $newSheetProps->setSheetId($targetSheetId);
                $newSheetProps->setTitle($targetSheetTitle);
                $updateSheetPropsRequest = new UpdateSheetPropertiesRequest();
                $updateSheetPropsRequest->setProperties($newSheetProps);
                $updateSheetPropsRequest->setFields('title');
                $sheetRequest = new BaseSheetRequest();
                $sheetRequest->setUpdateSheetProperties($updateSheetPropsRequest);
                $sheetRequests[] = $sheetRequest;
            }

            if (count($sheetRequests) > 0) {
                $batchSheetRequest = new BatchUpdateSpreadsheetRequest();
                $batchSheetRequest->setRequests($sheetRequests);
                $sheetBatchUpdateResult = $this->serviceInstance->spreadsheets->batchUpdate($spreadsheetId, $batchSheetRequest);
            }

            return $this->getAllWorkSheetInGoogleSpreadsheet($spreadsheetId);

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet WorkSheet Update Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Set the Headers for the Sheet of the Google Spreadsheet.
     *
     * @param array|null $headers
     * @param string|null $spreadsheetId
     * @param string|null $sheetName
     *
     * @return string|null
     */
    public function setHeadersForGoogleSpreadsheet(?array $headers = [], ?string $spreadsheetId = '', ?string $sheetName = ''): ?string
    {

        try {

            if (is_null($headers) || !is_array($headers) || (count($headers) == 0)) {
                return null;
            }

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return null;
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return null;
            }

            if (is_null($sheetName) || (trim($sheetName) == '')) {
                return null;
            }

            $updateOptions = [
                'valueInputOption' => self::VALUE_INPUT_OPTION_RAW,
            ];
            $headerRow = [$headers];
            $targetRow = 1;
            $headerRange = $sheetName . '!' . $this->sheetRangePool[0] . $targetRow;
            $valueRangeObj = new ValueRange();
            $valueRangeObj->setValues($headerRow);
            $headerUpdateResult = $this->serviceInstance->spreadsheets_values->update($spreadsheetId, $headerRange, $valueRangeObj, $updateOptions);
            return $headerUpdateResult->getSpreadsheetId();

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet Header Set Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the Headers for the Sheet of the Google Spreadsheet.
     *
     * @param string|null $spreadsheetId
     * @param string|null $sheetName
     *
     * @return array
     */
    public function getHeadersForGoogleSpreadsheet(?string $spreadsheetId = '', ?string $sheetName = ''): array
    {

        try {

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return [];
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return [];
            }

            if (is_null($sheetName) || (trim($sheetName) == '')) {
                return [];
            }

            $targetRow = 1;
            $headerRange = $sheetName . '!' . $this->sheetRangePool[0] . $targetRow . ':' . $this->sheetRangePool[(count($this->sheetRangePool) - 1)] . $targetRow;
            $headerGetResult = $this->serviceInstance->spreadsheets_values->get($spreadsheetId, $headerRange);
            $headersTotalArray = $headerGetResult->getValues();
            return (count($headersTotalArray) > 0) ? $headersTotalArray[array_key_first($headersTotalArray)] : [];

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet Header Get Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return [];
        }

    }

    /**
     * Get all the data set in the Sheet of the Google Spreadsheet.
     *
     * @param string|null $spreadsheetId
     * @param string|null $sheetName
     *
     * @return array
     */
    public function getAllDataInGoogleSheet(?string $spreadsheetId = '', ?string $sheetName = ''): array
    {

        try {

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return [];
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return [];
            }

            if (is_null($sheetName) || (trim($sheetName) == '')) {
                return [];
            }

            $headerRange = $sheetName;
            $sheetDataResult = $this->serviceInstance->spreadsheets_values->get($spreadsheetId, $headerRange);
            $sheetEntireData = $sheetDataResult->getValues();
            if (is_null($sheetEntireData)) {
                return [];
            }

            $headers = array_shift($sheetEntireData);

            $chunkedArraySize = 500;
            $returnArray = [];
            foreach (array_chunk($sheetEntireData, $chunkedArraySize) as $chunkedKey => $chunkedSheetData) {
                foreach ($chunkedSheetData as $row) {
                    $returnArray[] = array_combine($headers, $row);
                }
            }

            return $returnArray;

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet All Data Fetch Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return [];
        }

    }

    /**
     * Insert the Data in the Sheet of the Google Spreadsheet.
     *
     * @param array|null $insertData
     * @param string|null $spreadsheetId
     * @param string|null $sheetName
     *
     * @return string|null
     */
    public function insertRowInGoogleSheet(?array $insertData = [], ?string $spreadsheetId = '', ?string $sheetName = ''): ?string
    {

        try {

            if (is_null($insertData) || !is_array($insertData) || (count($insertData) == 0)) {
                return null;
            }

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return null;
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return null;
            }

            if (is_null($sheetName) || (trim($sheetName) == '')) {
                return null;
            }

            $headerData = $this->getHeadersForGoogleSpreadsheet($spreadsheetId, $sheetName);
            if (count($headerData) == 0) {
                return null;
            }

            $targetArrayFirstKeyPossibleValues = [
                0,
                array_key_first($headerData)
            ];

            if (!in_array(array_key_first($insertData), $targetArrayFirstKeyPossibleValues)) {
                return null;
            }

            if (array_key_first($insertData) == 0) {

                if (!is_array($insertData[0]) || (count($insertData[0]) == 0)) {
                    return null;
                }

                if (count($headerData) < count($insertData[0])) {
                    return null;
                }

                $updateValueRangeObjs = [];
                $currentRangeRowIndex = 1;
                foreach ($insertData as $datum) {
                    $currentRangeRowIndex++;
                    $currentValueRowRange = $sheetName . '!' . $this->sheetRangePool[0] . $currentRangeRowIndex . ':' . $this->sheetRangePool[(count($datum) - 1)] . $currentRangeRowIndex;
                    $valueRangeObj = new ValueRange();
                    $valueRangeObj->setValues([array_values($datum)]);
                    $valueRangeObj->setMajorDimension(self::SHEET_DIMENSION_ROWS);
                    $valueRangeObj->setRange($currentValueRowRange);
                    $updateValueRangeObjs[] = $valueRangeObj;
                }

                if (count($updateValueRangeObjs) == 0) {
                    return null;
                }

                $batchUpdateValueRequestObj = new BatchUpdateValuesRequest();
                $batchUpdateValueRequestObj->setValueInputOption(self::VALUE_INPUT_OPTION_USER_ENTERED);
                $batchUpdateValueRequestObj->setIncludeValuesInResponse(true);
                $batchUpdateValueRequestObj->setData($updateValueRangeObjs);

                $batchUpdateDataResult = $this->serviceInstance->spreadsheets_values->batchUpdate($spreadsheetId, $batchUpdateValueRequestObj);
                return $batchUpdateDataResult->getSpreadsheetId();

            } else {

                if (count($headerData) < count($insertData)) {
                    return null;
                }

                $updateOptions = [
                    'valueInputOption' => self::VALUE_INPUT_OPTION_USER_ENTERED,
                    'includeValuesInResponse' => true,
                ];
                $headerRange = $sheetName;
                $valueRangeObj = new ValueRange();
                $valueRangeObj->setValues([array_values($insertData)]);

                $appendDataResult = $this->serviceInstance->spreadsheets_values->append($spreadsheetId, $headerRange, $valueRangeObj, $updateOptions);
                return $appendDataResult->getSpreadsheetId();

            }

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet Data Save Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Clear the Row Data in the Sheet of the Google Spreadsheet.
     *
     * @param int $startRowNumber
     * @param int $endRowNumber
     * @param string|null $spreadsheetId
     * @param string|null $sheetName
     *
     * @return string|null
     */
    public function deleteRowsInGoogleSheet(int $startRowNumber = 0, int $endRowNumber = 0, ?string $spreadsheetId = '', ?string $sheetName = ''): ?string
    {

        try {

            if (is_null($startRowNumber) || !is_numeric($startRowNumber) || ((int)$startRowNumber <= 0)) {
                return null;
            }

            if (is_null($endRowNumber) || !is_numeric($endRowNumber) || ((int)$endRowNumber <= 0)) {
                return null;
            }

            if (is_null($this->serviceInstance) || !($this->serviceInstance instanceof Sheets)) {
                return null;
            }

            $spreadsheetObj = $this->getGoogleSpreadsheetById($spreadsheetId);
            if (is_null($spreadsheetObj)) {
                return null;
            }

            if (is_null($sheetName) || (trim($sheetName) == '')) {
                return null;
            }

            $headerData = $this->getHeadersForGoogleSpreadsheet($spreadsheetId, $sheetName);
            if (count($headerData) == 0) {
                return null;
            }

            $targetStartRow = (int)$startRowNumber + 1;
            $targetEndRow = (int)$startRowNumber + 1;
            $headerRange = $sheetName . '!' . $this->sheetRangePool[0] . $targetStartRow . ':' . $this->sheetRangePool[(count($headerData) - 1)] . $targetEndRow;
            $clearRequestObj = new ClearValuesRequest();
            $clearDataResult = $this->serviceInstance->spreadsheets_values->clear($spreadsheetId, $headerRange, $clearRequestObj);
            return $clearDataResult->getSpreadsheetId();

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Spreadsheet Data Clear Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    private function setGoogleSheetsServiceVariables() {
        $domainClientPreferences = CoreConfigData::all()->toArray();
        $sheetsConfigValues = ($domainClientPreferences) ? getConfigValue($domainClientPreferences, ['google_client_auth_code']) : [];
        $mainConfigs = config($this->googleConfigKey, []);
        $this->googleClientAuthCode = $sheetsConfigValues['fcm_server_key'] ?? '';
        $this->googleServiceAccountJsonPath = (isset($mainConfigs['googleServiceAccountJsonPath'])) ? $mainConfigs['googleServiceAccountJsonPath'] : '';
        $this->googleClientCredentialsJsonPath = (isset($mainConfigs['googleClientAuthKeysJsonPath'])) ? $mainConfigs['googleClientAuthKeysJsonPath'] : '';
    }

    private function setGoogleSheetsClientService() {

        try {

            if (trim($this->googleServiceAccountJsonPath) == '') {
                return null;
            }

            $googleApiClient = new GoogleClient();
            $googleApiClient->setApplicationName($this->clientApplicationName);
            $googleApiClient->addScope([Sheets::DRIVE_FILE, Sheets::SPREADSHEETS]);
            $googleApiClient->setAccessType(self::SPREADSHEET_ACCESS_TYPE_OFFLINE);
            $googleApiClient->setAuthConfig(public_path(trim($this->googleServiceAccountJsonPath)));
            $googleApiClient->fetchAccessTokenWithAssertion();

            /*if (trim($this->googleClientAuthCode) == '') {
                return null;
            }

            if (trim($this->googleClientCredentialsJsonPath) == '') {
                return null;
            }

            $googleApiClient = new GoogleClient();
            $googleApiClient->setApplicationName($this->clientApplicationName);
            $googleApiClient->addScope([Sheets::DRIVE_FILE, Sheets::SPREADSHEETS]);
            $googleApiClient->setAccessType(self::SPREADSHEET_ACCESS_TYPE_OFFLINE);
            $googleApiClient->setAuthConfig(public_path(trim($this->googleClientCredentialsJsonPath)));
            $accessToken = $googleApiClient->fetchAccessTokenWithAuthCode($this->googleClientAuthCode);
            $googleApiClient->setAccessToken($accessToken);*/

            $this->serviceInstance = new Sheets($googleApiClient);
            $this->driveServiceInstance = new Drive($googleApiClient);

        } catch (GoogleException|GuzzleException|Exception $ex) {
            Log::info('Google Sheets Service Initiation Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            $this->serviceInstance = null;
            $this->driveServiceInstance = null;
        }

    }

    private function separateDbConnection($schemaName){
        $default = [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => $schemaName,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null
        ];
        Config::set("database.connections.$schemaName", $default);
    }

}
