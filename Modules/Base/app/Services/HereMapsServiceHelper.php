<?php

namespace Modules\Base\Services;

use Illuminate\Support\Facades\Session;
use Modules\Base\Services\RestApiService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Modules\WebConfigurationManagement\Models\CoreConfigData;


class HereMapsServiceHelper
{

    private $configKey = 'customConfigs.maps.hereMaps';
    private $useHereMapsApis = false;
    private $hereMapsApiKey = '';
    private $hereMapsOrganizationId = '';
    private $hereMapsUserId = '';
    private $hereMapsClientId = '';
    private $hereMapsOAuthKey = '';
    private $hereMapsOAuthSecret = '';
    private $hereMapsOAuthGrantType = '';
    private $hereMapsOAuthApiUrl = '';
    private $hereMapsDistanceMatrixApiUrl = '';
    private $hereMapsGeocodeApiUrl = '';
    private $hereMapsReverseGeocodeApiUrl = '';
    private $hereMapsSearchApiUrl = '';
    private $hereMapsAutoSuggestApiUrl = '';
    private $hereMapsAutoCompleteApiUrl = '';
    private $hereMapsBrowseApiUrl = '';
    private $hereMapsLookupApiUrl = '';
    private $hereMapsAccessBearerToken = '';

    public function __construct()
    {
        $this->setHereMapsServiceVariables();
    }

    /**
     * Check whether the HERE Maps Service is enabled.
     *
     * @return bool
     */
    public function isServiceEnabled()
    {
        return (bool)$this->useHereMapsApis;
    }

    /**
     * Get the HERE Maps Service API Key.
     *
     * @return string
     */
    public function getHereMapsApiKey()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsApiKey;
    }

    /**
     * Get the HERE Maps Service Organization ID.
     *
     * @return string
     */
    public function getHereMapsOrganizationId()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsOrganizationId;
    }

    /**
     * Get the HERE Maps Service User ID.
     *
     * @return string
     */
    public function getHereMapsUserId()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsUserId;
    }

    /**
     * Get the HERE Maps Service Client ID.
     *
     * @return string
     */
    public function getHereMapsClientId()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsClientId;
    }

    /**
     * Get the HERE Maps Service OAuth Key.
     *
     * @return string
     */
    public function getHereMapsOAuthKey()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsOAuthKey;
    }

    /**
     * Get the HERE Maps Service OAuth Secret.
     *
     * @return string
     */
    public function getHereMapsOAuthSecret()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsOAuthSecret;
    }

    /**
     * Get the HERE Maps Service OAuth API URL.
     *
     * @return string
     */
    public function getHereMapsOAuthApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsOAuthApiUrl;
    }

    /**
     * Get the HERE Maps Service OAuth Grant Type.
     *
     * @return string
     */
    public function getHereMapsOAuthGrantType()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsOAuthGrantType;
    }

    /**
     * Get the HERE Maps Service OAuth API Bearer Token.
     *
     * @return string
     */
    public function getHereMapsBearerToken()
    {

        /*if (trim($this->hereMapsAccessBearerToken) == '') {
            $this->setHereMapsBearerToken();
        }*/

        if (Session::has('here_access_token')) {
            $token = Session::get('here_access_token');
            if (isset($token)) {
                $this->hereMapsAccessBearerToken = $token[0];
            } else {
                $this->setHereMapsBearerToken();
            }
        } else {
            $this->setHereMapsBearerToken();
        }

        return $this->hereMapsAccessBearerToken;

    }

    /**
     * Get the HERE Maps Service Distance Matrix API URL.
     *
     * @return string
     */
    public function getHereMapsDistanceMatrixApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsDistanceMatrixApiUrl;
    }

    /**
     * Get the HERE Maps Service Geocode API URL.
     *
     * @return string
     */
    public function getHereMapsGeocodeApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsGeocodeApiUrl;
    }

    /**
     * Get the HERE Maps Service Reverse Geocode API URL.
     *
     * @return string
     */
    public function getHereMapsReverseGeocodeApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsReverseGeocodeApiUrl;
    }

    /**
     * Get the HERE Maps Service Search API URL.
     *
     * @return string
     */
    public function getHereMapsSearchApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsSearchApiUrl;
    }

    /**
     * Get the HERE Maps Service Auto-Suggest API URL.
     *
     * @return string
     */
    public function getHereMapsAutoSuggestApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsAutoSuggestApiUrl;
    }

    /**
     * Get the HERE Maps Service Auto-Complete API URL.
     *
     * @return string
     */
    public function getHereMapsAutoCompleteApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsAutoCompleteApiUrl;
    }

    /**
     * Get the HERE Maps Service Browse API URL.
     *
     * @return string
     */
    public function getHereMapsBrowseApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsBrowseApiUrl;
    }

    /**
     * Get the HERE Maps Service Lookup API URL.
     *
     * @return string
     */
    public function getHereMapsLookupApiUrl()
    {
        if (!$this->isServiceEnabled()) {
            return '';
        }
        return $this->hereMapsLookupApiUrl;
    }

    /**
     * Get the API information of the HERE Maps Distance Matrix API.
     *
     * @return string|null
     */
    public function getDistanceMatrixApiInfo()
    {

        try {

            $apiUrl = $this->getHereMapsDistanceMatrixApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Distance Matrix API Information API Error : Invalid empty API URL.');
                return null;
            }

            $apiUrlFull = $apiUrl . '/openapi';
            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrlFull, [], [], false, false, '', '', true);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getDistanceMatrixApiInfo();
                }
                Log::info('HERE Maps Distance Matrix API Information API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            return $apiResult['response'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Distance Matrix API Information API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the API Health of the HERE Maps Distance Matrix API.
     *
     * @return string|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDistanceMatrixApiHealth()
    {

        try {

            $apiUrl = $this->getHereMapsDistanceMatrixApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Distance Matrix API Health API Error : Invalid empty API URL.');
                return null;
            }

            $apiUrlFull = $apiUrl . '/health';
            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrlFull, [], [], false, false, '', '', true);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getDistanceMatrixApiHealth();
                }
                Log::info('HERE Maps Distance Matrix API Health API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            return $apiResult['response'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Distance Matrix API Information API Health Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the API Version Info of the HERE Maps Distance Matrix API.
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDistanceMatrixApiVersionInfo()
    {

        try {

            $apiUrl = $this->getHereMapsDistanceMatrixApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Distance Matrix API Version Info API Error : Invalid empty API URL.');
                return null;
            }

            $apiUrlFull = $apiUrl . '/version';
            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrlFull, [], [], false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getDistanceMatrixApiVersionInfo();
                }
                Log::info('HERE Maps Distance Matrix API Version Info API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            return $apiResult['response'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Distance Matrix API Version Info API Health Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the Profile List of the HERE Maps Distance Matrix API.
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDistanceMatrixApiProfileList()
    {

        try {

            $apiUrl = $this->getHereMapsDistanceMatrixApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Distance Matrix Profile List API Error : Invalid empty API URL.');
                return null;
            }

            $apiUrlFull = $apiUrl . '/profiles';
            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrlFull, [], [], false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getDistanceMatrixApiProfileList();
                }
                Log::info('HERE Maps Distance Matrix Profile List API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            return $apiResult['response'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Distance Matrix Profile List API Health Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the Profile Details of the HERE Maps Distance Matrix API by Profile ID.
     *
     * @param string|null $profileId
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDistanceMatrixApiProfileById($profileId = '')
    {

        try {

            if (is_null($profileId) || (trim($profileId) == '')) {
                Log::info('HERE Maps Distance Matrix Profile Details API Error : Invalid empty address query.');
                return null;
            }

            $apiUrl = $this->getHereMapsDistanceMatrixApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Distance Matrix Profile Details API Error : Invalid empty API URL.');
                return null;
            }

            $apiUrlFull = $apiUrl . '/profiles/' . trim($profileId);
            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrlFull, [], [], false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getDistanceMatrixApiProfileById($profileId);
                }
                Log::info('HERE Maps Distance Matrix Profile Details API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            return $apiResult['response'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Distance Matrix Profile Details API Health Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the estimates for the HERE Maps Distance Matrix for the Co-ordinates.
     *
     * @param array|null $latitudes Array of Latitude Co-ordinates for the distance estimates.
     * @param array|null $longitudes Array of Longitude Co-ordinates for the distance estimates.
     * @param string|null $vehicleType The Type of the Vehicle: 'car' or 'motorbike'. Default: 'car'.
     * @param bool|null $avoidHighway Set whether to avoid Highway [for 'motorbike']: true or false. Default: false.
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDistanceDurationEstimationForCoords( $latitudes = [],  $longitudes = [],  $vehicleType = 'car',  $avoidHighway = false)
    {

        $submitResult = $this->processDistanceMatrixEstimate($latitudes, $longitudes, $vehicleType, $avoidHighway);
        if (is_null($submitResult)) {
            Log::info('HERE Maps Distance Matrix Fetch Error : Could not process submit request.');
            return null;
        }

        if (array_key_exists('matrix', $submitResult)) {
            return $submitResult;
        }

        $allowedStatuses = ['accepted', 'inprogress', 'completed'];
        if (
            !array_key_exists('status', $submitResult)
            || (
                array_key_exists('status', $submitResult)
                && !in_array(strtolower($submitResult['status']), $allowedStatuses)
            )
        ) {
            Log::info('HERE Maps Distance Matrix Fetch Error : Could not process submit request.');
            return null;
        }

        /*$allowedLoopStatuses = ['accepted', 'inprogress'];
        $statusResult = $this->checkDistanceMatrixStatus($submitResult['statusUrl']);
        if (is_null($statusResult)) {
            Log::info('HERE Maps Distance Matrix Fetch Error : Could not process status request.');
            return null;
        }

        if (array_key_exists('matrix', $statusResult)) {
            return $statusResult;
        }

        if (
            !array_key_exists('status', $statusResult)
            || (
                array_key_exists('status', $statusResult)
                && !in_array(strtolower($statusResult['status']), $allowedStatuses)
            )
        ) {
            Log::info('HERE Maps Distance Matrix Fetch Error : Could not process status request.');
            return null;
        }

        $whileLoopIndex = 1;
        while(in_array(strtolower($statusResult['status']), $allowedLoopStatuses)) {

            usleep(30000);

            $whileLoopIndex++;

            $statusResult = $this->checkDistanceMatrixStatus($statusResult['statusUrl']);
            if (is_null($statusResult)) {
                Log::info('HERE Maps Distance Matrix Fetch Error #' . $whileLoopIndex . ' : Could not process status request.');
                return null;
            }

            if (array_key_exists('matrix', $statusResult)) {
                return $statusResult;
            }

            if (
                !array_key_exists('status', $statusResult)
                || (
                    array_key_exists('status', $statusResult)
                    && !in_array(strtolower($statusResult['status']), $allowedStatuses)
                )
            ) {
                Log::info('HERE Maps Distance Matrix Fetch Error #' . $whileLoopIndex . ' : Could not process status request.');
                return null;
            }

        }

        $matrixId = $statusResult['matrixId'];*/

        $matrixId = $submitResult['matrixId'];

        $finalResult = $this->getDistanceMatrixProcessResult($matrixId);
        if (is_null($finalResult)) {
            Log::info('HERE Maps Distance Matrix Result Error : Could not process result request.');
            return null;
        }

        if (array_key_exists('matrix', $finalResult)) {
            return $finalResult;
        } else {
            Log::info('HERE Maps Distance Matrix Result Error : No result found.');
            return null;
        }

    }

    /**
     * Get the HERE Maps Geocode details of the given Address.
     *
     * @param string|null $addressQuery The Address string to be searched.
     * @param double|null $latitude The Latitude value of the Center of the Search Context.
     * @param double|null $longitude The Longitude value of the Center of the Search Context.
     *
     * @return array|null
     */
    public function getGeocodeDetails( $addressQuery = '', $latitude = null, $longitude = null)
    {

        try {

            if (is_null($addressQuery) || (trim($addressQuery) == '')) {
                Log::info('HERE Maps Geocode API Error : Invalid empty address query.');
                return null;
            }

            $apiUrl = $this->getHereMapsGeocodeApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Geocode API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Geocode API Error : Invalid Access Token.');
                return null;
            }

            $locale = App::getLocale();

            $getQueryParams = [
                'q' => urlencode(trim($addressQuery)),
                'lang' => $locale,
            ];

            if (!is_null($latitude) && !is_null($longitude)) {
                $getQueryParams['at'] = $latitude . ',' . $longitude;
            }

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrl, $getQueryParams, $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getGeocodeDetails($addressQuery, $latitude, $longitude);
                }
                Log::info('HERE Maps Geocode API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            $apiResponse = $apiResult['response'];
            if (!array_key_exists('items', $apiResponse) || !is_array($apiResponse['items']) || (count($apiResponse['items']) == 0)) {
                Log::info('HERE Maps Geocode API Error : No data found from API!');
                return null;
            }

            return $apiResponse['items'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Geocode API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the HERE Maps Reverse Geocode details of the given Address.
     *
     * @param double|null $latitude The Latitude value of the Center of the Search Context.
     * @param double|null $longitude The Longitude value of the Center of the Search Context.
     *
     * @return array|null
     */
    public function getReverseGeocodeDetails(float $latitude = null, float $longitude = null)
    {

        try {

            if (is_null($latitude) || is_null($longitude)) {
                Log::info('HERE Maps Reverse Geocode API Error : Invalid empty latitude and longitude query.');
                return null;
            }

            $apiUrl = $this->getHereMapsReverseGeocodeApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Reverse Geocode API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Reverse Geocode API Error : Invalid Access Token.');
                return null;
            }

            $locale = App::getLocale();

            $coordsQuery = $latitude . ',' . $longitude;
            $getQueryParams = [
                'at' => $coordsQuery,
                'lang' => $locale,
            ];

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrl, $getQueryParams, $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getReverseGeocodeDetails($latitude, $longitude);
                }
                Log::info('HERE Maps Reverse Geocode API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            $apiResponse = $apiResult['response'];
            if (!array_key_exists('items', $apiResponse) || !is_array($apiResponse['items']) || (count($apiResponse['items']) == 0)) {
                Log::info('HERE Maps Reverse Geocode API Error : No data found from API!');
                return null;
            }

            return $apiResponse['items'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Reverse Geocode API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the HERE Maps Search Discover details of the given Address.
     *
     * @param string|null $addressQuery The Address string to be searched.
     * @param double|null $latitude The Latitude value of the Center of the Search Context.
     * @param double|null $longitude The Longitude value of the Center of the Search Context.
     *
     * @return array|null
     */
    public function getSearchDiscoverDetails($addressQuery = '', float $latitude = null, float $longitude = null)
    {

        try {

            if (is_null($addressQuery) || (trim($addressQuery) == '')) {
                Log::info('HERE Maps Search Discover API Error : Invalid empty address query.');
                return null;
            }

            if (is_null($latitude) || is_null($longitude)) {
                Log::info('HERE Maps Search Discover API Error : Invalid empty latitude and longitude query.');
                return null;
            }

            $apiUrl = $this->getHereMapsSearchApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Search Discover API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Search Discover API Error : Invalid Access Token.');
                return null;
            }

            $locale = App::getLocale();

            $coordsQuery = $latitude . ',' . $longitude;
            $getQueryParams = [
                'q' => urlencode(trim($addressQuery)),
                'at' => $coordsQuery,
                'lang' => $locale,
            ];

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrl, $getQueryParams, $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getSearchDiscoverDetails($addressQuery, $latitude, $longitude);
                }
                Log::info('HERE Maps Search Discover API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            $apiResponse = $apiResult['response'];
            if (!array_key_exists('items', $apiResponse) || !is_array($apiResponse['items']) || (count($apiResponse['items']) == 0)) {
                Log::info('HERE Maps Search Discover API Error : No data found from API!');
                return null;
            }

            return $apiResponse['items'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Search Discover API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the HERE Maps Auto-Suggestion Search details of the given Address.
     *
     * @param string|null $addressQuery The Address string to be searched.
     * @param double|null $latitude The Latitude value of the Center of the Search Context.
     * @param double|null $longitude The Longitude value of the Center of the Search Context.
     *
     * @return array|null
     */
    public function getAutoSuggestionsDetails($addressQuery = '', float $latitude = null, float $longitude = null)
    {

        try {

            if (is_null($addressQuery) || (trim($addressQuery) == '')) {
                Log::info('HERE Maps Auto-Suggestion API Error : Invalid empty address query.');
                return null;
            }

            if (is_null($latitude) || is_null($longitude)) {
                Log::info('HERE Maps Auto-Suggestion API Error : Invalid empty latitude and longitude query.');
                return null;
            }

            $apiUrl = $this->getHereMapsAutoSuggestApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Auto-Suggestion API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Auto-Suggestion API Error : Invalid Access Token.');
                return null;
            }

            $locale = App::getLocale();

            $coordsQuery = $latitude . ',' . $longitude;
            $getQueryParams = [
                'q' => urlencode(trim($addressQuery)),
                'at' => $coordsQuery,
                'lang' => $locale,
            ];

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrl, $getQueryParams, $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getAutoSuggestionsDetails($addressQuery, $latitude, $longitude);
                }
                Log::info('HERE Maps Auto-Suggestion API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            $apiResponse = $apiResult['response'];
            if (!array_key_exists('items', $apiResponse) || !is_array($apiResponse['items']) || (count($apiResponse['items']) == 0)) {
                Log::info('HERE Maps Search Discover API Error : No data found from API!');
                return null;
            }

            return $apiResponse['items'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Search Discover API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the HERE Maps Auto-Complete Search details of the given Address.
     *
     * @param string|null $addressQuery The Address string to be searched.
     * @param float $latitude The Latitude value of the Center of the Search Context.
     * @param float $longitude The Longitude value of the Center of the Search Context.
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getAutoCompleteDetails($addressQuery = '', float $latitude = null, float $longitude = null)
    {

        try {

            if (is_null($addressQuery) || (trim($addressQuery) == '')) {
                Log::info('HERE Maps Auto-Complete Search API Error : Invalid empty address query.');
                return null;
            }

            $apiUrl = $this->getHereMapsAutoCompleteApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Auto-Complete Search API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Auto-Complete Search API Error : Invalid Access Token.');
                return null;
            }

            $locale = App::getLocale();

            $getQueryParams = [
                'q' => urlencode(trim($addressQuery)),
                'lang' => $locale,
            ];

            if (!is_null($latitude) && !is_null($longitude)) {
                $getQueryParams['at'] = $latitude . ',' . $longitude;
            }

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrl, $getQueryParams, $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getAutoCompleteDetails($addressQuery, $latitude, $longitude);
                }
                Log::info('HERE Maps Auto-Complete Search API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            $apiResponse = $apiResult['response'];
            if (!array_key_exists('items', $apiResponse) || !is_array($apiResponse['items']) || (count($apiResponse['items']) == 0)) {
                Log::info('HERE Maps Auto-Complete Search API Error : No data found from API!');
                return null;
            }

            return $apiResponse['items'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Auto-Complete Search API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the HERE Maps Browse Search details of the given Address.
     *
     * @param float $latitude The Latitude value of the Center of the Search Context.
     * @param float $longitude The Longitude value of the Center of the Search Context.
     * @param string|null $nameQuery The Name/Title string to be searched.
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getBrowseSearchDetails(float $latitude = null, float $longitude = null, $nameQuery = '')
    {

        try {

            if (is_null($latitude) || is_null($longitude)) {
                Log::info('HERE Maps Browse Search API Error : Invalid empty latitude and longitude query.');
                return null;
            }

            $apiUrl = $this->getHereMapsBrowseApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Browse Search API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Browse Search API Error : Invalid Access Token.');
                return null;
            }

            $locale = App::getLocale();

            $coordsQuery = $latitude . ',' . $longitude;
            $getQueryParams = [
                'at' => $coordsQuery,
                'lang' => $locale,
            ];

            if (!is_null($nameQuery) && (trim($nameQuery) != '')) {
                $getQueryParams['name'] = urlencode(trim($nameQuery));
            }

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrl, $getQueryParams, $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getBrowseSearchDetails($latitude, $longitude, $nameQuery);
                }
                Log::info('HERE Maps Browse Search API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            $apiResponse = $apiResult['response'];
            if (!array_key_exists('items', $apiResponse) || !is_array($apiResponse['items']) || (count($apiResponse['items']) == 0)) {
                Log::info('HERE Maps Browse Search API Error : No data found from API!');
                return null;
            }

            return $apiResponse['items'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Browse Search API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Get the HERE Maps Address details of the given Place ID.
     *
     * @param string|null $placeId The Place ID string to be searched.
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getAddressDetailsByPlaceId($placeId = '')
    {

        try {

            if (is_null($placeId) || (trim($placeId) == '')) {
                Log::info('HERE Maps Address Lookup Search API Error : Invalid empty address query.');
                return null;
            }

            $apiUrl = $this->getHereMapsLookupApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Address Lookup Search API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Address Lookup Search API Error : Invalid Access Token.');
                return null;
            }

            $locale = App::getLocale();

            $getQueryParams = [
                'id' => urlencode(trim($placeId)),
                'lang' => $locale,
            ];

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrl, $getQueryParams, $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getAddressDetailsByPlaceId($placeId);
                }
                Log::info('HERE Maps Address Lookup Search API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            $apiResponse = $apiResult['response'];
            if (!array_key_exists('id', $apiResponse) || (trim($apiResponse['id']) != trim($placeId))) {
                Log::info('HERE Maps Address Lookup Search API Error : No data found from API!');
                return null;
            }

            return $apiResponse;

        } catch (\Exception $ex) {
            Log::info('HERE Maps Browse Search API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    private function setHereMapsServiceVariables()
    {
        $domainClientPreferences = CoreConfigData::all()->toArray();;
        $configDataValues = ($domainClientPreferences) ? getConfigValue($domainClientPreferences, [
            'use_here_maps_apis', 'here_maps_apis_api_key', 'here_maps_apis_organization_id', 'here_maps_apis_user_id',
            'here_maps_apis_client_id', 'here_maps_apis_oauth_key', 'here_maps_apis_oauth_secret', 'here_maps_apis_oauth_url',
        ]) : [];
        $domainClientAdditionalPreferences = (object)$configDataValues;

        $this->useHereMapsApis = ($domainClientAdditionalPreferences)
            ? (bool)$domainClientAdditionalPreferences->use_here_maps_apis : false;
        $this->hereMapsApiKey = ($domainClientAdditionalPreferences && (trim($domainClientAdditionalPreferences->here_maps_apis_api_key) != ''))
            ? $domainClientAdditionalPreferences->here_maps_apis_api_key : '';
        $this->hereMapsOrganizationId = ($domainClientAdditionalPreferences && (trim($domainClientAdditionalPreferences->here_maps_apis_organization_id) != ''))
            ? $domainClientAdditionalPreferences->here_maps_apis_organization_id : '';
        $this->hereMapsUserId = ($domainClientAdditionalPreferences && (trim($domainClientAdditionalPreferences->here_maps_apis_user_id) != ''))
            ? $domainClientAdditionalPreferences->here_maps_apis_user_id : '';
        $this->hereMapsClientId = ($domainClientAdditionalPreferences && (trim($domainClientAdditionalPreferences->here_maps_apis_client_id) != ''))
            ? $domainClientAdditionalPreferences->here_maps_apis_client_id : '';
        $this->hereMapsOAuthKey = ($domainClientAdditionalPreferences && (trim($domainClientAdditionalPreferences->here_maps_apis_oauth_key) != ''))
            ? $domainClientAdditionalPreferences->here_maps_apis_oauth_key : '';
        $this->hereMapsOAuthSecret = ($domainClientAdditionalPreferences && (trim($domainClientAdditionalPreferences->here_maps_apis_oauth_secret) != ''))
            ? $domainClientAdditionalPreferences->here_maps_apis_oauth_secret : '';
        $oAuthApiUrl = ($domainClientAdditionalPreferences && (trim($domainClientAdditionalPreferences->here_maps_apis_oauth_url) != ''))
            ? $domainClientAdditionalPreferences->here_maps_apis_oauth_url : '';
        $mainConfigs = config($this->configKey, []);
        $tempOAuthApiUrl = (isset($mainConfigs['hereMapsOAuthApiUrl'])) ? $mainConfigs['hereMapsOAuthApiUrl'] : '';
        $this->hereMapsOAuthApiUrl = (trim($oAuthApiUrl) != '') ? $oAuthApiUrl : $tempOAuthApiUrl;
        $this->hereMapsOAuthGrantType = (isset($mainConfigs['hereMapsOAuthGrantType'])) ? $mainConfigs['hereMapsOAuthGrantType'] : 'client_credentials';
        $this->hereMapsDistanceMatrixApiUrl = (isset($mainConfigs['hereMapsDistanceMatrixApiUrl'])) ? $mainConfigs['hereMapsDistanceMatrixApiUrl'] : '';
        $this->hereMapsGeocodeApiUrl = (isset($mainConfigs['hereMapsGeocodeApiUrl'])) ? $mainConfigs['hereMapsGeocodeApiUrl'] : '';
        $this->hereMapsReverseGeocodeApiUrl = (isset($mainConfigs['hereMapsReverseGeocodeApiUrl'])) ? $mainConfigs['hereMapsReverseGeocodeApiUrl'] : '';
        $this->hereMapsSearchApiUrl = (isset($mainConfigs['hereMapsSearchApiUrl'])) ? $mainConfigs['hereMapsSearchApiUrl'] : '';
        $this->hereMapsAutoSuggestApiUrl = (isset($mainConfigs['hereMapsAutoSuggestApiUrl'])) ? $mainConfigs['hereMapsAutoSuggestApiUrl'] : '';
        $this->hereMapsAutoCompleteApiUrl = (isset($mainConfigs['hereMapsAutoCompleteApiUrl'])) ? $mainConfigs['hereMapsAutoCompleteApiUrl'] : '';
        $this->hereMapsBrowseApiUrl = (isset($mainConfigs['hereMapsBrowseApiUrl'])) ? $mainConfigs['hereMapsBrowseApiUrl'] : '';
        $this->hereMapsLookupApiUrl = (isset($mainConfigs['hereMapsLookupApiUrl'])) ? $mainConfigs['hereMapsLookupApiUrl'] : '';
    }

    private function setHereMapsBearerToken($token = '')
    {
        if (is_null($token) || (trim($token) == '')) {
            $token = $this->generateOAuthAccessBearerToken();
            Session::push('here_access_token', $token);
        }
        $this->hereMapsAccessBearerToken = is_null($token) ? '' : $token;
    }

    private function generateOAuthAccessBearerToken()
    {

        try {

            $grantType = $this->getHereMapsOAuthGrantType();
            $oauthConsumerKey = $this->getHereMapsOAuthKey();
            $authUrl = $this->getHereMapsOAuthApiUrl();
            $accessKeySecret = $this->getHereMapsOAuthSecret();
            if ((trim($grantType) == '') || (trim($oauthConsumerKey) == '') || (trim($authUrl) == '') || (trim($accessKeySecret) == '')) {
                return null;
            }

            $timer = (string)time();
            $oauthNonce = uniqid(mt_rand(1, 1000));
            $oauthSignatureMethod = 'HMAC-SHA256';
            $oauthTimestamp = $timer;
            $oauthVersion = '1.0';

            $postDataString = 'grant_type=' . $grantType;
            $parameterString = 'grant_type=' . $grantType;
            $parameterString .= '&oauth_consumer_key=' . $oauthConsumerKey;
            $parameterString .= '&oauth_nonce=' . $oauthNonce;
            $parameterString .= '&oauth_signature_method=' . $oauthSignatureMethod;
            $parameterString .= '&oauth_timestamp=' . $oauthTimestamp;
            $parameterString .= '&oauth_version=' . $oauthVersion;

            $encodedParameterString = urlencode($parameterString);
            $encodedBaseString = 'POST' . '&' . urlencode($authUrl) . '&' . $encodedParameterString;
            $signingKey = $accessKeySecret . '&';
            $signature = hash_hmac('SHA256', $encodedBaseString, $signingKey, true);
            $encodedSignature = urlencode(base64_encode($signature));

            $oAuthHeaderString = 'oauth_consumer_key="' . $oauthConsumerKey . '",';
            $oAuthHeaderString .= 'oauth_signature_method="' . $oauthSignatureMethod . '",';
            $oAuthHeaderString .= 'oauth_timestamp="' . $oauthTimestamp . '",';
            $oAuthHeaderString .= 'oauth_nonce="' . $oauthNonce . '",';
            $oAuthHeaderString .= 'oauth_version="' . $oauthVersion . '",';
            $oAuthHeaderString .= 'oauth_signature="' . $encodedSignature . '"';

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $authUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postDataString,
                CURLOPT_HTTPHEADER => [
                    'Authorization: OAuth ' . $oAuthHeaderString,
                    'Content-Type: application/x-www-form-urlencoded'
                ],
            ]);

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $errorMsg = curl_error($curl);
            }
            curl_close($curl);

            if (isset($errorMsg)) {
                Log::info('HERE Maps OAuth Access Token Creation Error : ' . $errorMsg);
                return null;
            } else {

                $jsonResponseResult = $this->jsonValidateAndParse($response);
                if ($jsonResponseResult['success'] === false) {
                    Log::info('HERE Maps OAuth Access Token Creation Error : ' . $jsonResponseResult['error']);
                    return null;
                }

                $jsonResponseData = $jsonResponseResult['result'];
                return $jsonResponseData['access_token'];

            }

        } catch (\Exception $ex) {
            Log::info('HERE Maps OAuth Access Token Creation Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Setting up the estimates for the HERE Maps Distance Matrix for the Co-ordinates.
     *
     * @param array|null $latitudes Array of Latitude Co-ordinates for the distance estimates.
     * @param array|null $longitudes Array of Longitude Co-ordinates for the distance estimates.
     * @param string|null $vehicleType The Type of the Vehicle: 'car' or 'motorbike'. Default: 'car'.
     * @param bool|null $avoidHighway Set whether to avoid Highway [for 'motorbike']: true or false. Default: false.
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function processDistanceMatrixEstimate( $latitudes = [],  $longitudes = [],  $vehicleType = 'car',  $avoidHighway = false)
    {

        try {

            if (is_null($latitudes) || !is_array($latitudes) || (count($latitudes) < 2)) {
                Log::info('HERE Maps Distance Matrix Submit API Error : Invalid empty latitude array.');
                return null;
            }

            if (is_null($longitudes) || !is_array($longitudes) || (count($longitudes) < 2)) {
                Log::info('HERE Maps Distance Matrix Submit API Error : Invalid empty longitude array.');
                return null;
            }

            if (count($latitudes) != count($longitudes)) {
                Log::info('HERE Maps Distance Matrix Submit API Error : Latitude and longitude array lengths are not equal.');
                return null;
            }

            $apiUrl = $this->getHereMapsDistanceMatrixApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Distance Matrix Submit API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Distance Matrix Submit API Error : Invalid Access Token.');
                return null;
            }

            $originCoords = [];
            $destinationCoords = [];
            foreach ($latitudes as $currentKey => $currentEl) {
                if ($currentKey != array_key_last($latitudes)) {
                    $originCoords[] = [
                        'lat' => (double)$currentEl,
                        'lng' => (double)$longitudes[$currentKey],
                    ];
                }
                if ($currentKey != array_key_first($latitudes)) {
                    $destinationCoords[] = [
                        'lat' => (double)$currentEl,
                        'lng' => (double)$longitudes[$currentKey],
                    ];
                }
            }
            if ((count($originCoords) == 0) || (count($destinationCoords) == 0)) {
                Log::info('HERE Maps Distance Matrix Submit API Error : Latitude and longitude array lengths are empty.');
                return null;
            }

            $allowedVehicleTypes = ['car' => 'car', 'motorbike' => 'scooter'];
            $vehicleTypeClean = (
                !is_null($vehicleType)
                && (trim($vehicleType) != '')
                && array_key_exists(strtolower(trim($vehicleType)), $allowedVehicleTypes)
            ) ? $allowedVehicleTypes[strtolower(trim($vehicleType))] : $allowedVehicleTypes[array_key_first($allowedVehicleTypes)];

            $avoidHighwayClean = (!is_null($avoidHighway) && is_bool($avoidHighway)) ? (bool)$avoidHighway : false;

            $postData = [
                'origins' => $originCoords,
                'destinations' => $destinationCoords,
                'regionDefinition' => [
                    'type' => 'world',
                ],
                'transportMode' => $vehicleTypeClean,
                'scooter' => [
                    'allowHighway' => !$avoidHighwayClean
                ],
                'matrixAttributes' => [
                    'travelTimes',
                    'distances',
                ],
            ];

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];

            $apiUrlFull = $apiUrl . '/matrix';
            $apiService = new RestApiService();
            $apiResult = $apiService->processPostApi($apiUrlFull, $postData, 'json', $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->processDistanceMatrixEstimate($latitudes, $longitudes, $vehicleType, $avoidHighway);
                }
                Log::info('HERE Maps Distance Matrix Submit API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            return $apiResult['response'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Distance Matrix Submit API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Check the Status of the HERE Maps Distance Matrix Estimation.
     *
     * @param string $statusUrl
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function checkDistanceMatrixStatus($statusUrl = '')
    {

        try {

            if (is_null($statusUrl) || (trim($statusUrl) == '')) {
                Log::info('HERE Maps Distance Matrix Status API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Distance Matrix Status API Error : Invalid Access Token.');
                return null;
            }

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept-Encoding' => 'gzip',
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($statusUrl, [], $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->checkDistanceMatrixStatus($statusUrl);
                }
                Log::info('HERE Maps Distance Matrix Status API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            return $apiResult['response'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Distance Matrix Status API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    /**
     * Fetch the result of the HERE Maps Distance Matrix Estimation.
     *
     * @param string $matrixId
     *
     * @return array|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getDistanceMatrixProcessResult($matrixId = '')
    {

        try {

            if (is_null($matrixId) || (trim($matrixId) == '')) {
                Log::info('HERE Maps Distance Matrix Result API Error : Invalid empty API URL.');
                return null;
            }

            $apiUrl = $this->getHereMapsDistanceMatrixApiUrl();
            if (trim($apiUrl) == '') {
                Log::info('HERE Maps Distance Matrix Result API Error : Invalid empty API URL.');
                return null;
            }

            $accessToken = $this->getHereMapsBearerToken();
            if (trim($accessToken) == '') {
                Log::info('HERE Maps Distance Matrix Result API Error : Invalid Access Token.');
                return null;
            }

            $customHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept-Encoding' => 'gzip',
            ];

            $apiUrlFull = $apiUrl . '/matrix/' . trim($matrixId);
            $apiService = new RestApiService();
            $apiResult = $apiService->processGetApi($apiUrlFull, [], $customHeaders, false);
            if ($apiResult['status'] === false) {
                if($apiResult['code'] == 401){
                    Session::forget('here_access_token');
                    return $this->getDistanceMatrixProcessResult($matrixId);
                }
                Log::info('HERE Maps Distance Matrix Result API Error : HTTP Client Status Invalid :: ' . $apiResult['message']);
                return null;
            }

            return $apiResult['response'];

        } catch (\Exception $ex) {
            Log::info('HERE Maps Distance Matrix Result API Exception Error : ' . $ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            return null;
        }

    }

    private function jsonValidateAndParse($string)
    {
        // decode the JSON data
        $result = json_decode($string);

        $error = '';
        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            return ['success' => false, 'error' => $error];
        }

        return ['success' => true, 'result' => json_decode($string, true)];

    }

}

