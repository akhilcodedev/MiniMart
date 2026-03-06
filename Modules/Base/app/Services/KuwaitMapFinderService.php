<?php

namespace Modules\Base\Services;

use Illuminate\Support\Facades\Crypt;
use Modules\WebConfigurationManagement\Models\AdditionalConfiguration;
use Modules\WebConfigurationManagement\Models\CoreConfigData;

class KuwaitMapFinderService
{

    private $configKey = 'customConfigs.maps.kuwaitFinder';
    private $enableApiService = true;
    private $apiUsername = '';
    private $apiPassword = '';
    private $baseApiUrl = '';
    private $apiTokenUri = '';
    private $apiServiceUri = '';
    private $apiPaciSegment = '';
    private $apiAddressSegment = '';
    private $paciQueryApiSegment = '';
    private $governorateQueryApiSegment = '';
    private $neighborhoodQueryApiSegment = '';
    private $blockQueryApiSegment = '';
    private $streetQueryApiSegment = '';
    private $apiResultFormat = '';
    private $apiTokenExpiryTime = 60;

    public function __construct()
    {
        $this->setKuwaitMapFinderServiceConfig();
        $this->setKuwaitMapFinderServiceVariables();
    }

    private function setKuwaitMapFinderServiceConfig() {
        $mainConfigs = config($this->configKey, []);
        $tempEnabled = (isset($mainConfigs['enableApiService'])) ? (bool)$mainConfigs['enableApiService'] : true;
        $this->enableApiService = (is_bool($tempEnabled)) ? (bool)$tempEnabled : true;
        $this->apiUsername = (isset($mainConfigs['apiUsername'])) ? $mainConfigs['apiUsername'] : '';
        $this->apiPassword = (isset($mainConfigs['apiPassword'])) ? $mainConfigs['apiPassword'] : '';
        $preferences = AdditionalConfiguration::all()->toArray();
        if ($preferences) {
            $kuwaitFinderStatus = getConfigValue($preferences, 'kuwait_finder_status');
            $kuwaitFinderUsername = getConfigValue($preferences, 'kuwait_finder_username');
            $kuwaitFinderPassword = getConfigValue($preferences, 'kuwait_finder_password');
            if (
                isset($kuwaitFinderStatus) && is_numeric($kuwaitFinderStatus)
                && (((int)$kuwaitFinderStatus === 1) || ((int)$kuwaitFinderStatus === 0))
            ) {
                $this->enableApiService = !((int)$kuwaitFinderStatus === 0);
            }
            $this->apiUsername = (isset($kuwaitFinderUsername) && (trim($kuwaitFinderUsername) != ''))
                ? trim($kuwaitFinderUsername) : '';
            $this->apiPassword = (isset($kuwaitFinderPassword) && (trim($kuwaitFinderPassword) != ''))
                ? Crypt::decryptString(trim($kuwaitFinderPassword)) : '';
        }
    }

    private function setKuwaitMapFinderServiceVariables() {
        $mainConfigs = config($this->configKey, []);
        $this->baseApiUrl = (isset($mainConfigs['baseApiUrl'])) ? $mainConfigs['baseApiUrl'] : '';
        $this->apiTokenUri = (isset($mainConfigs['apiTokenUri'])) ? $mainConfigs['apiTokenUri'] : '';
        $this->apiServiceUri = (isset($mainConfigs['apiServiceUri'])) ? $mainConfigs['apiServiceUri'] : '';
        $this->apiPaciSegment = (isset($mainConfigs['apiPaciSegment'])) ? $mainConfigs['apiPaciSegment'] : '';
        $this->apiAddressSegment = (isset($mainConfigs['apiAddressSegment'])) ? $mainConfigs['apiAddressSegment'] : '';
        $this->paciQueryApiSegment = '/0/query';
        $this->governorateQueryApiSegment = '/3/query';
        $this->neighborhoodQueryApiSegment = '/2/query';
        $this->blockQueryApiSegment = '/1/query';
        $this->streetQueryApiSegment = '/0/query';
        $this->apiResultFormat = 'pjson';
        $this->apiTokenExpiryTime = 60;
    }

    private function getApiUsername() {
        return $this->apiUsername;
    }

    private function getApiPassword() {
        return $this->apiPassword;
    }

    private function getApiResultFormat() {
        return $this->apiResultFormat;
    }

    private function getApiTokenExpiryTime() {
        return $this->apiTokenExpiryTime;
    }

    private function getServiceApiToken() {

        $apiUrl = $this->getTokenApiUrl();
        if (trim($apiUrl) == '') {
            return null;
        }

        $serviceUrl = $this->getServiceApiUrl();
        if (trim($serviceUrl) == '') {
            return null;
        }

        $apiUsername = $this->getApiUsername();
        if (trim($apiUsername) == '') {
            return null;
        }

        $apiPassword = $this->getApiPassword();
        if (trim($apiPassword) == '') {
            return null;
        }

        $postData = [
            "username" => $apiUsername,
            "password" => $apiPassword,
            "client" => "referer",
            "referer" => $serviceUrl,
            "expiration" => $this->getApiTokenExpiryTime(),
            "f" => $this->getApiResultFormat(),
        ];

        $apiService = new RestApiService();
        $apiResult = $apiService->processPostApi($apiUrl, $postData, 'formdata', [], false);
        if ($apiResult['status'] === false) {
            return null;
        }

        return $apiResult['response'];

    }

    public function isServiceEnabled() {
        return $this->enableApiService;
    }

    public function getBasicApiUrl() {
        return trim($this->baseApiUrl);
    }

    public function getTokenApiUrl() {
        $baseUrl = $this->getBasicApiUrl();
        return ($this->isServiceEnabled() && (trim($baseUrl) != '') && (trim($this->apiTokenUri) != '')) ? trim($baseUrl) . trim($this->apiTokenUri) : '';
    }

    public function getServiceApiUrl() {
        $baseUrl = $this->getBasicApiUrl();
        return ($this->isServiceEnabled() && (trim($baseUrl) != '') && (trim($this->apiServiceUri) != '')) ? trim($baseUrl) . trim($this->apiServiceUri) : '';
    }

    public function getPaciApiUrl() {
        $serviceUrl = $this->getServiceApiUrl();
        return ($this->isServiceEnabled() && (trim($serviceUrl) != '') && (trim($this->apiPaciSegment) != '')) ? trim($serviceUrl) . trim($this->apiPaciSegment) : '';
    }

    public function getPaciQueryApiUrl() {
        $serviceApiUrl = $this->getPaciApiUrl();
        return ($this->isServiceEnabled() && (trim($serviceApiUrl) != '') && (trim($this->paciQueryApiSegment) != '')) ? trim($serviceApiUrl) . trim($this->paciQueryApiSegment) : '';
    }

    public function getAddressApiUrl() {
        $serviceUrl = $this->getServiceApiUrl();
        return ($this->isServiceEnabled() && (trim($serviceUrl) != '') && (trim($this->apiAddressSegment) != '')) ? trim($serviceUrl) . trim($this->apiAddressSegment) : '';
    }

    public function getGovernorateQueryApiUrl() {
        $serviceApiUrl = $this->getAddressApiUrl();
        return ($this->isServiceEnabled() && (trim($serviceApiUrl) != '') && (trim($this->governorateQueryApiSegment) != '')) ? trim($serviceApiUrl) . trim($this->governorateQueryApiSegment) : '';
    }

    public function getNeighborhoodQueryApiUrl() {
        $serviceApiUrl = $this->getAddressApiUrl();
        return ($this->isServiceEnabled() && (trim($serviceApiUrl) != '') && (trim($this->neighborhoodQueryApiSegment) != '')) ? trim($serviceApiUrl) . trim($this->neighborhoodQueryApiSegment) : '';
    }

    public function getBlockQueryApiUrl() {
        $serviceApiUrl = $this->getAddressApiUrl();
        return ($this->isServiceEnabled() && (trim($serviceApiUrl) != '') && (trim($this->blockQueryApiSegment) != '')) ? trim($serviceApiUrl) . trim($this->blockQueryApiSegment) : '';
    }

    public function getStreetQueryApiUrl() {
        $serviceApiUrl = $this->getAddressApiUrl();
        return ($this->isServiceEnabled() && (trim($serviceApiUrl) != '') && (trim($this->streetQueryApiSegment) != '')) ? trim($serviceApiUrl) . trim($this->streetQueryApiSegment) : '';
    }

    public function searchLocationByCivilId($civilId = '') {

        if (is_null($civilId) || (trim($civilId) == '')) {
            return [];
        }

        $apiUrl = $this->getPaciQueryApiUrl();
        if (trim($apiUrl) == '') {
            return [];
        }

        $tokenResult = $this->getServiceApiToken();
        if (is_null($tokenResult)) {
            return [];
        }

        $apiToken = $tokenResult['token'];

        $queryParams = [
            "where" => "civilid=" . $civilId,
            "outFields" => "*",
            "f" => $this->getApiResultFormat(),
            "token" => $apiToken,
        ];

        $apiService = new RestApiService();
        $apiResult = $apiService->processGetApi($apiUrl, $queryParams, [], false);
        if ($apiResult['status'] === false) {
            return [];
        }

        $returnResult = [];

        $apiResponse = $apiResult['response'];
        if (array_key_exists('features', $apiResponse) && is_array($apiResponse['features']) && (count($apiResponse['features']) > 0)) {
            $firstFeatureData = $apiResponse['features'][array_key_first($apiResponse['features'])];
            if (array_key_exists('attributes', $firstFeatureData) && is_array($firstFeatureData['attributes']) && (count($firstFeatureData['attributes']) > 0)) {
                $firstFeature = $firstFeatureData['attributes'];
                $returnResult = [
                    'id' => $firstFeature['objectid'],
                    'civil_id' => $firstFeature['civilid'],
                    'type' => $firstFeature['featuretype'],
                    'location' => [
                        'latitude' => $firstFeature['lat'],
                        'longitude' => $firstFeature['lon'],
                        'coords' => $firstFeature['location'],
                    ],
                    'governorate' => [
                        'id' => $firstFeature['governorateid'],
                        'english' => $firstFeature['governorateenglish'],
                        'arabic' => $firstFeature['governoratearabic'],
                    ],
                    'neighborhood' => [
                        'id' => $firstFeature['neighborhoodid'],
                        'english' => $firstFeature['neighborhoodenglish'],
                        'arabic' => $firstFeature['neighborhoodarabic'],
                    ],
                    'block' => [
                        'id' => $firstFeature['surveyblock'],
                        'english' => $firstFeature['blockenglish'],
                        'arabic' => $firstFeature['blockarabic'],
                    ],
                    'parcel' => [
                        'id' => $firstFeature['surveyparcel'],
                        'english' => $firstFeature['parcelenglish'],
                        'arabic' => $firstFeature['parcelarabic'],
                    ],
                    'street' => [
                        'english' => $firstFeature['streetenglish'],
                        'arabic' => $firstFeature['streetarabic'],
                    ],
                    'unit' => [
                        'number' => $firstFeature['unit_no'],
                        'type' => [
                            'english' => $firstFeature['unittypeenglish'],
                            'arabic' => $firstFeature['unittypearabic'],
                        ],
                    ],
                    'building' => [
                        'civil_id' => $firstFeature['buildingcivilid'],
                        'name' => [
                            'english' => $firstFeature['buildingnameenglish'],
                            'arabic' => $firstFeature['buildingnamearabic'],
                        ],
                        'type' => [
                            'english' => $firstFeature['buildingtypeenglish'],
                            'arabic' => $firstFeature['buildingtypearabic'],
                        ],
                    ],
                    'house' => [
                        'floor_number' => $firstFeature['floor_no'],
                        'english' => $firstFeature['houseenglish'],
                        'arabic' => $firstFeature['housearabic'],
                    ],
                ];
            }
        }

        return $returnResult;

    }

    public function getGovernorateList() {

        $apiUrl = $this->getGovernorateQueryApiUrl();
        if (trim($apiUrl) == '') {
            return [];
        }

        $tokenResult = $this->getServiceApiToken();
        if (is_null($tokenResult)) {
            return [];
        }

        $apiToken = $tokenResult['token'];

        $queryParams = [
            "where" => "1=1",
            "outFields" => "*",
            "f" => $this->getApiResultFormat(),
            "token" => $apiToken,
            "returnGeometry" => false,
        ];

        $apiService = new RestApiService();
        $apiResult = $apiService->processGetApi($apiUrl, $queryParams, [], false);
        if ($apiResult['status'] === false) {
            return [];
        }

        $returnResult = [];

        $apiResponse = $apiResult['response'];
        if (array_key_exists('features', $apiResponse) && is_array($apiResponse['features']) && (count($apiResponse['features']) > 0)) {
            foreach ($apiResponse['features'] as $featureEl) {
                if (array_key_exists('attributes', $featureEl) && is_array($featureEl['attributes']) && (count($featureEl['attributes']) > 0)) {
                    $attributeList = $featureEl['attributes'];
                    $returnResult[] = [
                        'id' => $attributeList['objectid'],
                        'gov_no' => $attributeList['gov_no'],
                        'english' => $attributeList['governorateenglish'],
                        'arabic' => $attributeList['governoratearabic'],
                        'location' => [
                            'latitude' => $attributeList['centroid_y'],
                            'longitude' => $attributeList['centroid_x'],
                            'coords' => $attributeList['location'],
                        ],
                    ];
                }
            }
        }

        return $returnResult;

    }

    public function getNeighboorhoodList($governorateNo = '') {

        $apiUrl = $this->getNeighborhoodQueryApiUrl();
        if (trim($apiUrl) == '') {
            return [];
        }

        $tokenResult = $this->getServiceApiToken();
        if (is_null($tokenResult)) {
            return [];
        }

        $apiToken = $tokenResult['token'];

        $queryParams = [
            "where" => "1=1",
            "outFields" => "*",
            "f" => $this->getApiResultFormat(),
            "token" => $apiToken,
            "returnGeometry" => false,
        ];

        if (!is_null($governorateNo) && (trim($governorateNo) != '')) {
            $queryParams['where'] = "gov_no=" . trim($governorateNo);
        }

        $apiService = new RestApiService();
        $apiResult = $apiService->processGetApi($apiUrl, $queryParams, [], false);
        if ($apiResult['status'] === false) {
            return [];
        }

        $returnResult = [];

        $apiResponse = $apiResult['response'];
        if (array_key_exists('features', $apiResponse) && is_array($apiResponse['features']) && (count($apiResponse['features']) > 0)) {
            foreach ($apiResponse['features'] as $featureEl) {
                if (array_key_exists('attributes', $featureEl) && is_array($featureEl['attributes']) && (count($featureEl['attributes']) > 0)) {
                    $attributeList = $featureEl['attributes'];
                    $returnResult[] = [
                        'id' => $attributeList['objectid'],
                        'nhood_no' => $attributeList['nhood_no'],
                        'english' => $attributeList['neighborhoodenglish'],
                        'arabic' => $attributeList['neighborhoodarabic'],
                        'location' => [
                            'latitude' => $attributeList['centroid_y'],
                            'longitude' => $attributeList['centroid_x'],
                            'coords' => $attributeList['location'],
                        ],
                        'governorate' => [
                            'gov_no' => $attributeList['gov_no'],
                            'english' => $attributeList['governorateenglish'],
                            'arabic' => $attributeList['governoratearabic'],
                        ],
                    ];
                }
            }
        }

        return $returnResult;

    }

    public function getBlockList($neighborhoodNo = '') {

        $apiUrl = $this->getBlockQueryApiUrl();
        if (trim($apiUrl) == '') {
            return [];
        }

        $tokenResult = $this->getServiceApiToken();
        if (is_null($tokenResult)) {
            return [];
        }

        $apiToken = $tokenResult['token'];

        $queryParams = [
            "where" => "1=1",
            "outFields" => "*",
            "f" => $this->getApiResultFormat(),
            "token" => $apiToken,
            "returnGeometry" => false,
        ];

        if (!is_null($neighborhoodNo) && (trim($neighborhoodNo) != '')) {
            $queryParams['where'] = "nhood_no=" . trim($neighborhoodNo);
        }

        $apiService = new RestApiService();
        $apiResult = $apiService->processGetApi($apiUrl, $queryParams, [], false);
        if ($apiResult['status'] === false) {
            return [];
        }

        $returnResult = [];

        $apiResponse = $apiResult['response'];
        if (array_key_exists('features', $apiResponse) && is_array($apiResponse['features']) && (count($apiResponse['features']) > 0)) {
            foreach ($apiResponse['features'] as $featureEl) {
                if (array_key_exists('attributes', $featureEl) && is_array($featureEl['attributes']) && (count($featureEl['attributes']) > 0)) {
                    $attributeList = $featureEl['attributes'];
                    $returnResult[] = [
                        'id' => $attributeList['objectid'],
                        'block_id' => $attributeList['blockid'],
                        'english' => $attributeList['blockenglish'],
                        'arabic' => $attributeList['blockarabic'],
                        'location' => [
                            'latitude' => $attributeList['centroid_y'],
                            'longitude' => $attributeList['centroid_x'],
                            'coords' => $attributeList['location'],
                        ],
                        'neighborhood' => [
                            'nhood_no' => $attributeList['nhood_no'],
                            'english' => $attributeList['neighborhoodenglish'],
                            'arabic' => $attributeList['neighborhoodarabic'],
                        ],
                        'governorate' => [
                            'english' => $attributeList['governorateenglish'],
                            'arabic' => $attributeList['governoratearabic'],
                        ],
                    ];
                }
            }
        }

        return $returnResult;

    }

    public function getStreetList($neighborhoodNo = '', $block = '') {

        $apiUrl = $this->getStreetQueryApiUrl();
        if (trim($apiUrl) == '') {
            return [];
        }

        $tokenResult = $this->getServiceApiToken();
        if (is_null($tokenResult)) {
            return [];
        }

        $apiToken = $tokenResult['token'];

        $queryParams = [
            "where" => "1=1",
            "outFields" => "*",
            "f" => $this->getApiResultFormat(),
            "token" => $apiToken,
            "returnGeometry" => false,
        ];

        $conditionArray = [];
        if (!is_null($neighborhoodNo) && (trim($neighborhoodNo) != '')) {
            $conditionArray[] = "nhood_no=" . trim($neighborhoodNo);
        }
        if (!is_null($block) && (trim($block) != '')) {
            $conditionArray[] = "(blockenglish='" . trim($block) . "' or blockarabic='" . trim($block) . "')";
        }

        if (count($conditionArray) > 0) {
            $queryParams['where'] = implode(" and ", $conditionArray);
        }

        $apiService = new RestApiService();
        $apiResult = $apiService->processGetApi($apiUrl, $queryParams, [], false);
        if ($apiResult['status'] === false) {
            return [];
        }

        $returnResult = [];

        $apiResponse = $apiResult['response'];
        if (array_key_exists('features', $apiResponse) && is_array($apiResponse['features']) && (count($apiResponse['features']) > 0)) {
            foreach ($apiResponse['features'] as $featureEl) {
                if (array_key_exists('attributes', $featureEl) && is_array($featureEl['attributes']) && (count($featureEl['attributes']) > 0)) {
                    $attributeList = $featureEl['attributes'];
                    $returnResult[$attributeList['nhood_no']][$attributeList['blockenglish']][$attributeList['streetenglish']][$attributeList['objectid']] = [
                        'id' => $attributeList['objectid'],
                        'street_number' => $attributeList['streetnumber'],
                        'english' => $attributeList['streetenglish'],
                        'arabic' => $attributeList['streetarabic'],
                        'location' => [
                            'latitude' => $attributeList['centroid_y'],
                            'longitude' => $attributeList['centroid_x'],
                            'coords' => $attributeList['location'],
                        ],
                        'details' => [
                            'english' => $attributeList['detailsenglish'],
                            'arabic' => $attributeList['detailsarabic'],
                        ],
                        'alternative_streets' => [
                            'english' => [
                                $attributeList['alternativestreetenglish1'],
                                $attributeList['alternativestreetenglish2'],
                                $attributeList['alternativestreetenglish3'],
                                $attributeList['alternativestreetenglish4'],
                            ],
                            'arabic' => [
                                $attributeList['alternativestreetarabic1'],
                                $attributeList['alternativestreetarabic2'],
                                $attributeList['alternativestreetarabic3'],
                                $attributeList['alternativestreetarabic4'],
                            ],
                        ],
                        'block' => [
                            'english' => $attributeList['blockenglish'],
                            'arabic' => $attributeList['blockarabic'],
                        ],
                        'neighborhood' => [
                            'nhood_no' => $attributeList['nhood_no'],
                            'english' => $attributeList['neighborhoodenglish'],
                            'arabic' => $attributeList['neighborhoodarabic'],
                        ],
                        'governorate' => [
                            'gov_no' => $attributeList['gov_no'],
                            'english' => $attributeList['governorateenglish'],
                            'arabic' => $attributeList['governoratearabic'],
                        ],
                    ];
                }
            }
        }

        if(count($returnResult) > 0) {
            $tempReturnResult = $returnResult;
            $returnResult = [];
            foreach ($tempReturnResult as $nHoodKey => $nHoodEl) {
                foreach ($nHoodEl as $blockEnglishKey => $blockEnglishEl) {
                    foreach ($blockEnglishEl as $streetEnglishKey => $streetEnglishEl) {
                        $returnResult[] = $streetEnglishEl[max(array_keys($streetEnglishEl))];
                    }
                }
            }
        }

        return $returnResult;

    }

    public function getHouseList($governorateNo = '', $neighborhoodNo = '', $block = '', $street = '') {

        $apiUrl = $this->getPaciQueryApiUrl();
        if (trim($apiUrl) == '') {
            return [];
        }

        $tokenResult = $this->getServiceApiToken();
        if (is_null($tokenResult)) {
            return [];
        }

        $apiToken = $tokenResult['token'];

        $queryParams = [
            "outFields" => "*",
            "f" => $this->getApiResultFormat(),
            "token" => $apiToken,
        ];

        $conditionArray = [];
        if (!is_null($governorateNo) && (trim($governorateNo) != '')) {
            $conditionArray[] = "governorateid=" . trim($governorateNo);
        }
        if (!is_null($neighborhoodNo) && (trim($neighborhoodNo) != '')) {
            $conditionArray[] = "neighborhoodid=" . trim($neighborhoodNo);
        }
        if (!is_null($block) && (trim($block) != '')) {
            $conditionArray[] = "(blockenglish='" . trim($block) . "' or blockarabic='" . trim($block) . "')";
        }
        if (!is_null($street) && (trim($street) != '')) {
            $conditionArray[] = "(streetenglish='" . trim($street) . "' or streetarabic='" . trim($street) . "')";
        }

        if (count($conditionArray) > 0) {
            $queryParams['where'] = implode(" and ", $conditionArray);
        }

        $apiService = new RestApiService();
        $apiResult = $apiService->processGetApi($apiUrl, $queryParams, [], false);
        if ($apiResult['status'] === false) {
            return [];
        }

        $returnResult = [];

        $apiResponse = $apiResult['response'];
        if (array_key_exists('features', $apiResponse) && is_array($apiResponse['features']) && (count($apiResponse['features']) > 0)) {
            foreach ($apiResponse['features'] as $featureEl) {
                if (array_key_exists('attributes', $featureEl) && is_array($featureEl['attributes']) && (count($featureEl['attributes']) > 0)) {
                    $firstFeature = $featureEl['attributes'];
                    $returnResult[$firstFeature['neighborhoodid']][$firstFeature['blockenglish']][$firstFeature['streetenglish']][$firstFeature['houseenglish']][$firstFeature['objectid']] = [
                        'id' => $firstFeature['objectid'],
                        /*'civil_id' => $firstFeature['civilid'],*/
                        /*'type' => $firstFeature['featuretype'],*/
                        'floor_number' => $firstFeature['floor_no'],
                        'english' => $firstFeature['houseenglish'],
                        'arabic' => $firstFeature['housearabic'],
                        'location' => [
                            'latitude' => $firstFeature['lat'],
                            'longitude' => $firstFeature['lon'],
                            'coords' => $firstFeature['location'],
                        ],
                        'governorate' => [
                            'id' => $firstFeature['governorateid'],
                            'english' => $firstFeature['governorateenglish'],
                            'arabic' => $firstFeature['governoratearabic'],
                        ],
                        'neighborhood' => [
                            'id' => $firstFeature['neighborhoodid'],
                            'english' => $firstFeature['neighborhoodenglish'],
                            'arabic' => $firstFeature['neighborhoodarabic'],
                        ],
                        'block' => [
                            'id' => $firstFeature['surveyblock'],
                            'english' => $firstFeature['blockenglish'],
                            'arabic' => $firstFeature['blockarabic'],
                        ],
                        /*'parcel' => [
                            'id' => $firstFeature['surveyparcel'],
                            'english' => $firstFeature['parcelenglish'],
                            'arabic' => $firstFeature['parcelarabic'],
                        ],*/
                        'street' => [
                            'english' => $firstFeature['streetenglish'],
                            'arabic' => $firstFeature['streetarabic'],
                        ],
                        /*'unit' => [
                            'number' => $firstFeature['unit_no'],
                            'type' => [
                                'english' => $firstFeature['unittypeenglish'],
                                'arabic' => $firstFeature['unittypearabic'],
                            ],
                        ],*/
                        /*'building' => [
                            'civil_id' => $firstFeature['buildingcivilid'],
                            'name' => [
                                'english' => $firstFeature['buildingnameenglish'],
                                'arabic' => $firstFeature['buildingnamearabic'],
                            ],
                            'type' => [
                                'english' => $firstFeature['buildingtypeenglish'],
                                'arabic' => $firstFeature['buildingtypearabic'],
                            ],
                        ],*/
                        /*'house' => [
                            'floor_number' => $firstFeature['floor_no'],
                            'english' => $firstFeature['houseenglish'],
                            'arabic' => $firstFeature['housearabic'],
                        ],*/
                    ];
                }
            }
        }

        if(count($returnResult) > 0) {
            $tempReturnResult = $returnResult;
            $returnResult = [];
            foreach ($tempReturnResult as $nHoodKey => $nHoodEl) {
                foreach ($nHoodEl as $blockEnglishKey => $blockEnglishEl) {
                    foreach ($blockEnglishEl as $streetEnglishKey => $streetEnglishEl) {
                        foreach ($streetEnglishEl as $houseEnglishKey => $houseEnglishEl) {
                            $returnResult[] = $houseEnglishEl[max(array_keys($houseEnglishEl))];
                        }
                    }
                }
            }
        }

        return $returnResult;

    }

}
