<?php

namespace Modules\Base\Services;

use Modules\WebConfigurationManagement\Models\CoreConfigData;
use Google\Client as GoogleClient;
use Google\Service\FirebaseCloudMessaging;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomFcmPushService
{

    private $firebaseConfigKey = 'customConfigs.google.firebase';
    private $fcmConfigKey = 'customConfigs.google.firebase.fcm';
    private $useLegacyHttpMode = '';
    private $fcmServerKey = '';
    private $firebaseProjectId = '';
    private $firebaseServiceConfigType = '';
    private $firebaseServiceAccountClientId = '';
    private $firebaseServiceAccountClientEmail = '';
    private $firebaseServiceAccountPrivateKey = '';
    private $fcmServiceAccountJsonPath = '';
    private $fcmDomainProtocol = '';
    private $fcmDomainUrl = '';
    private $fcmSendUri = '';
    private $fcmLegacySendApiUrl = '';
    private $fcmSendV1Prefix = '';
    private $fcmSendV1Uri = '';
    private $fcmSendV1ApiUrl = '';

    public function __construct()
    {
        $this->setFirebaseServiceVariables();
    }

    /**
     * Send Push Notifications using Firebase Cloud Messaging (FCM).
     *
     * @param string|array|null $deviceTokens The array of FCM Device Tokens.
     * @param array|null $notificationData The array of Extra Data for the Notification.
     * @param array|null $notificationConfigs The array of Configurations for the Notification.
     * @param string|null $priority The priority of the Notification: 'High' or 'Normal'. Default: 'High'.
     * @param int|null $timeToLive The Lifespan of an Undelivered Notification in seconds: 0 to 2419200. Default: null.
     *
     * @return mixed|null
     */
    public function sendPushNotification($deviceTokens = null, array $notificationData = null, array $notificationConfigs = null, ?string $priority = 'high', ?int $timeToLive = null) {
        if ($this->useLegacyHttpMode) {
            return $this->sendLegacyHttpPushNotification($deviceTokens, $notificationData, $notificationConfigs, $priority, $timeToLive);
        } else {
            return $this->sendNormalHttpPushNotification($deviceTokens, $notificationData, $notificationConfigs, $priority, $timeToLive);
        }
    }

    private function setFirebaseServiceVariables() {
        $domainClientPreferences = CoreConfigData::all()->toArray();
        $fcmConfigValues = ($domainClientPreferences) ? getConfigValue($domainClientPreferences, ['use_fcm_legacy_http'. 'fcm_project_id'. 'fcm_server_key']) : [];
        $fcmServerKeyRaw =  CoreConfigData::where('config_path', 'webconfigurations_fcm_server_key')->first();
        $fcmServerProjectIDRaw =  CoreConfigData::where('config_path', 'webconfigurations_firebase_project_id')->first();

        $tempLegacyMode = $fcmConfigValues['use_fcm_legacy_http'] ?? '';
        $this->useLegacyHttpMode = (trim($tempLegacyMode) != '')
            && is_bool(trim($tempLegacyMode)) && (bool)trim($tempLegacyMode);
        $this->firebaseProjectId = $fcmServerProjectIDRaw->value ?? '';

        /*$this->firebaseServiceConfigType = 'service_account';
        $this->firebaseServiceAccountClientId = ($domainClientPreferences && (trim($domainClientPreferences->firebase_service_account_client_id) != ''))
            ? $domainClientPreferences->firebase_service_account_client_id : '';
        $this->firebaseServiceAccountClientEmail = ($domainClientPreferences && (trim($domainClientPreferences->firebase_service_account_client_email) != ''))
            ? $domainClientPreferences->firebase_service_account_client_email : '';
        $this->firebaseServiceAccountPrivateKey = ($domainClientPreferences && (trim($domainClientPreferences->firebase_service_account_private_key) != ''))
            ? $domainClientPreferences->firebase_service_account_private_key : '';*/
        $this->fcmServerKey = $fcmServerKeyRaw->value ?? '';
        $mainConfigs = config($this->firebaseConfigKey, []);
        $fcmConfigs = config($this->fcmConfigKey, []);
        $this->fcmServiceAccountJsonPath = (isset($fcmConfigs['fcmServiceAccountJsonPath'])) ? $fcmConfigs['fcmServiceAccountJsonPath'] : '';
        $this->fcmDomainProtocol = (isset($fcmConfigs['domainProtocol'])) ? $fcmConfigs['domainProtocol'] : 'https';
        $this->fcmDomainUrl = (isset($fcmConfigs['domainUrl'])) ? $fcmConfigs['domainUrl'] : 'fcm.googleapis.com';
        $this->fcmSendUri = (isset($fcmConfigs['sendUri'])) ? $fcmConfigs['sendUri'] : 'fcm/send';
        $this->fcmLegacySendApiUrl = (
            (trim($this->fcmDomainProtocol) != '') && (trim($this->fcmDomainUrl) != '') && (trim($this->fcmSendUri) != '')
        ) ? $this->fcmDomainProtocol . '://' . $this->fcmDomainUrl . '/' . $this->fcmSendUri : '';
        $this->fcmSendV1Prefix = (isset($fcmConfigs['sendV1Prefix'])) ? $fcmConfigs['sendV1Prefix'] : 'v1/projects';
        $this->fcmSendV1Uri = (isset($fcmConfigs['sendV1Uri'])) ? $fcmConfigs['sendV1Uri'] : 'messages:send';
        $this->fcmSendV1ApiUrl = (
            (trim($this->fcmDomainProtocol) != '') && (trim($this->fcmDomainUrl) != '') && (trim($this->fcmSendV1Prefix) != '')
            && (trim($this->fcmSendV1Uri) != '') && (trim($this->firebaseProjectId) != '')
        ) ? $this->fcmDomainProtocol . '://' . $this->fcmDomainUrl . '/' . $this->fcmSendV1Prefix . '/' . $this->firebaseProjectId . '/' . $this->fcmSendV1Uri : '';
    }

    private function sendLegacyHttpPushNotification($deviceToken = null, $notificationData = null, $notificationConfigs = null, $priority = 'high', $timeToLive = 0) {

        try {

            if (is_null($deviceToken) || ((is_array($deviceToken) && (count($deviceToken) == 0)) || (!is_array($deviceToken) && (trim($deviceToken) == '')))) {
                return null;
            }

            $registrationIds = [];
            if (is_array($deviceToken)) {
                foreach ($deviceToken as $item) {
                    if (trim($item) != '') {
                        $registrationIds[] = trim($item);
                    }
                }
            } else {
                $registrationIds[] = trim($deviceToken);
            }
            if (count($registrationIds) == 0) {
                return null;
            }

            if (is_null($notificationData) || !is_array($notificationData) || (count($notificationData) == 0)) {
                return null;
            }

            if (is_null($notificationConfigs) || !is_array($notificationConfigs) || (count($notificationConfigs) == 0)) {
                return null;
            }

            if (trim($this->fcmServerKey) == '') {
                return null;
            }

            if (trim($this->fcmLegacySendApiUrl) == '') {
                return null;
            }

            $availablePriorities = [
                'normal' => [
                    'android' => 'normal',
                    'apn' => '5',
                    'webpush' => 'normal'
                ],
                'high' => [
                    'android' => 'high',
                    'apn' => '10',
                    'webpush' => 'high'
                ],
            ];
            $priorityClean = (!is_null($priority) && (trim($priority) != '') && array_key_exists(strtolower(trim($priority)), $availablePriorities)) ? strtolower(trim($priority)) : 'high';
            $timeToLiveClean = (!is_null($timeToLive) && is_numeric($timeToLive) && ((int)trim($timeToLive) >= 0)) ? (int)trim($timeToLive) : null;
            if (!is_null($timeToLiveClean) && ((int)$timeToLiveClean > 2419200)) {
                $timeToLiveClean = 2419200;
            }

            $postData = [
                'content_available' => true,
                'registration_ids'  => $registrationIds,
                'priority' => $priorityClean,
                'data' => $notificationData,
                'notification' => $notificationConfigs,
            ];
            if (!is_null($timeToLiveClean)) {
                $postData['time_to_live'] = $timeToLiveClean;
            }

            $postUrl = $this->fcmLegacySendApiUrl;
            $headers = [
                'Authorization' => 'key=' . $this->fcmServerKey,
            ];

            return $this->processPostApi($postUrl, $postData, $headers);

        } catch(\Exception $ex) {
            Log::info('Firebase Push Notification Error : ' . $ex->getMessage());
            return null;
        }

    }

    private function sendNormalHttpPushNotification($deviceToken = null, $notificationData = null, $notificationConfigs = null, $priority = 'high', $timeToLive = 0) {

        try {

            if (is_null($deviceToken) || ((is_array($deviceToken) && (count($deviceToken) == 0)) || (!is_array($deviceToken) && (trim($deviceToken) == '')))) {
                return null;
            }

            $registrationIds = [];
            if (is_array($deviceToken)) {
                foreach ($deviceToken as $item) {
                    if (trim($item) != '') {
                        $registrationIds[] = trim($item);
                    }
                }
            } else {
                $registrationIds[] = trim($deviceToken);
            }
            if (count($registrationIds) == 0) {
                return null;
            }

            if (is_null($notificationData) || !is_array($notificationData) || (count($notificationData) == 0)) {
                return null;
            }

            if (is_null($notificationConfigs) || !is_array($notificationConfigs) || (count($notificationConfigs) == 0)) {
                Log::info('We have entered notificationConfigs Negative impact.');
                return null;
            }

            if (trim($this->fcmServiceAccountJsonPath) == '') {
                return null;
            }

            /*$serviceAccountConfig = [];
            if (trim($this->firebaseServiceConfigType) != '') {
                $serviceAccountConfig['type'] = $this->firebaseServiceConfigType;
            }
            if (trim($this->firebaseServiceAccountClientId) != '') {
                $serviceAccountConfig['client_id'] = $this->firebaseServiceAccountClientId;
            }
            if (trim($this->firebaseServiceAccountClientEmail) != '') {
                $serviceAccountConfig['client_email'] = $this->firebaseServiceAccountClientEmail;
            }
            if (trim($this->firebaseServiceAccountPrivateKey) != '') {
                $serviceAccountConfig['private_key'] = $this->firebaseServiceAccountPrivateKey;
            }
            if (count($serviceAccountConfig) < 4) {
                return null;
            }*/

            if (trim($this->fcmSendV1ApiUrl) == '') {
                return null;
            }

            $availablePriorities = [
                'normal' => [
                    'android' => 'normal',
                    'apn' => '5',
                    'webpush' => 'normal'
                ],
                'high' => [
                    'android' => 'high',
                    'apn' => '10',
                    'webpush' => 'high'
                ],
            ];


            $priorityClean = (!is_null($priority) && (trim($priority) != '') && array_key_exists(strtolower(trim($priority)), $availablePriorities)) ? strtolower(trim($priority)) : 'high';
            $timeToLiveClean = (!is_null($timeToLive) && is_numeric($timeToLive) && ((int)trim($timeToLive) >= 0)) ? (int)trim($timeToLive) : null;
            if (!is_null($timeToLiveClean) && ((int)$timeToLiveClean > 2419200)) {
                $timeToLiveClean = 2419200;
            }


            $notifyDataTemp = $notificationData;
            $notificationData = [];
            foreach ($notifyDataTemp as $currentDataKey => $currentDataValue) {
                $notificationData[$currentDataKey] = is_numeric($currentDataValue) ? (string)$currentDataValue : $currentDataValue;
            }


            $soundFileName = null;
            if (array_key_exists('sound', $notificationConfigs)) {
                $soundFileName = $notificationConfigs['sound'];
                unset($notificationConfigs['sound']);
            }

            $androidChannelId = null;
            if (array_key_exists('android_channel_id', $notificationConfigs)) {
                $androidChannelId = $notificationConfigs['android_channel_id'];
                unset($notificationConfigs['android_channel_id']);
            }

            $soundPlay = null;
            if (array_key_exists('soundPlay', $notificationConfigs)) {
                $soundPlay = $notificationConfigs['soundPlay'];
                unset($notificationConfigs['soundPlay']);
            }

            $iconImage = null;
            if (array_key_exists('icon', $notificationConfigs)) {
                $iconImage = $notificationConfigs['icon'];
                unset($notificationConfigs['icon']);
            }

            $showInForeground = null;
            if (array_key_exists('show_in_foreground', $notificationConfigs)) {
                $showInForeground = $notificationConfigs['show_in_foreground'];
                unset($notificationConfigs['show_in_foreground']);
            }

            $clickAction = null;
            if (array_key_exists('click_action', $notificationConfigs)) {
                $clickAction = $notificationConfigs['click_action'];
                unset($notificationConfigs['click_action']);
            }

            $postData = [
                'message' => [
                    /*'content_available' => true,*/
                    'android' => [
                        'priority' => $availablePriorities[$priorityClean]['android'],
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => $availablePriorities[$priorityClean]['apn'],
                        ],
                    ],
                    'webpush' => [
                        'headers' => [
                            'Urgency' => $availablePriorities[$priorityClean]['webpush'],
                        ],
                    ],
                    'data' => $notificationData,
                    'notification' => $notificationConfigs,
                ],
            ];


            if (!is_null($timeToLiveClean)) {
                $postData['message']['android']['ttl'] = $timeToLiveClean . 's';
                $postData['message']['apns']['headers']['apns-expiration'] = strtotime('+' . $timeToLiveClean . 'secs');
                $postData['message']['webpush']['headers']['TTL'] = (string)$timeToLiveClean;
            }

            if (!is_null($iconImage)) {
                $postData['message']['android']['notification']['icon'] = $iconImage;
                $postData['message']['webpush']['notification']['icon'] = $iconImage;
            }

            if (!is_null($soundFileName)) {
                $postData['message']['android']['notification']['sound'] = $soundFileName;
                $postData['message']['apns']['payload']['aps']['sound'] = $soundFileName;
            } else {
                $postData['message']['android']['notification']['default_sound'] = true;
                $postData['message']['apns']['payload']['aps']['sound'] = 'default';
            }

            if (!is_null($androidChannelId)) {
                $postData['message']['android']['notification']['channel_id'] = $androidChannelId;
            }

            if (!is_null($clickAction)) {
                $postData['message']['data']['click_action'] = $clickAction;
                $postData['message']['android']['notification']['click_action'] = $clickAction;
            }

            $returnResponses = [];
            foreach ($registrationIds as $currentDeviceToken) {
                $postData['message']['token'] = $currentDeviceToken;
                $googleApiClient = new GoogleClient();
                /*$googleApiClient->setAuthConfig($serviceAccountConfig);*/
                $googleApiClient->setAuthConfig(public_path(trim($this->fcmServiceAccountJsonPath)));
                $googleApiClient->addScope(FirebaseCloudMessaging::FIREBASE_MESSAGING);
                $httpClient = $googleApiClient->authorize();
                $returnResponse = $httpClient->post($this->fcmSendV1ApiUrl, ['json' => $postData]);
                $returnResponses[] = $returnResponse->getBody()->getContents();
                unset($googleApiClient);
            }

            return $returnResponses;

        } catch(\Exception $ex) {
            Log::info('Firebase Push Notification Error : ' . $ex->getMessage()." :: Line" . $ex->getLine());
            return null;
        } catch (GuzzleException $gEx) {
            Log::info('Firebase Push Notification Guzzle Exception Error : ' . $gEx->getMessage());
            return null;
        }

    }

    private function processPostApi($url = '', $params = [], $headers = []) {
        $apiService = new RestApiService();
        $apiResult = $apiService->processPostApi($url, $params, 'json', $headers, false);
        if ($apiResult['status'] === false) {
            return null;
        }
        return $apiResult['response'];
    }

}
