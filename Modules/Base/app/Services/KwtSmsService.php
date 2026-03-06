<?php

namespace Modules\Base\Services;

class KwtSmsService
{

    private $configKey = 'customConfigs.communication.sms.kwtSms';
    private $kwtSmsTestMode = '';
    private $kwtSmsApiUrl = '';
    private $kwtSmsUsername = '';
    private $kwtSmsPassword = '';
    private $kwtSmsLanguage = '';

    public function __construct()
    {
        $this->setKwtSmsServiceVariables();
    }

    private function setKwtSmsServiceVariables() {
        $mainConfigs = config($this->configKey, []);
        $this->kwtSmsTestMode = (isset($mainConfigs['kwtSmsTestMode'])) ? (bool)$mainConfigs['kwtSmsTestMode'] : false;
        $this->kwtSmsApiUrl = (isset($mainConfigs['kwtSmsApiUrl'])) ? $mainConfigs['kwtSmsApiUrl'] : '';
        $this->kwtSmsUsername = (isset($mainConfigs['kwtSmsUsername'])) ? $mainConfigs['kwtSmsUsername'] : '';
        $this->kwtSmsPassword = (isset($mainConfigs['kwtSmsPassword'])) ? $mainConfigs['kwtSmsPassword'] : '';
        $this->kwtSmsLanguage = (isset($mainConfigs['kwtSmsLanguage'])) ? $mainConfigs['kwtSmsLanguage'] : '';
    }

    public function getTestMode() {
        return $this->kwtSmsTestMode;
    }

    public function getApiUrl() {
        return $this->kwtSmsApiUrl;
    }

    public function getApiUsername() {
        return $this->kwtSmsUsername;
    }

    public function getApiPassword() {
        return $this->kwtSmsPassword;
    }

    public function getLanguage() {
        return $this->kwtSmsLanguage;
    }

    public function checkAccountBalance() {

        $apiUrl = $this->getApiUrl();
        $targetUri = "balance/";
        if (trim($apiUrl) == '') {
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

        $postUrl = $apiUrl . $targetUri;
        $postData = [
            "username" => $apiUsername,
            "password" => $apiPassword,
        ];

        $apiService = new RestApiService();
        $apiResult = $apiService->processPostApi($postUrl, $postData, 'json', [], false);
        if ($apiResult['status'] === false) {
            return null;
        }

        $apiResponse = $apiResult['response'];
        if (strtolower($apiResponse['result']) != 'ok') {
            return null;
        }

        return $apiResponse;

    }

    public function getSenderIds() {

        $apiUrl = $this->getApiUrl();
        $targetUri = "senderid/";
        if (trim($apiUrl) == '') {
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

        $postUrl = $apiUrl . $targetUri;
        $postData = [
            "username" => $apiUsername,
            "password" => $apiPassword,
        ];

        $apiService = new RestApiService();
        $apiResult = $apiService->processPostApi($postUrl, $postData, 'json', [], false);
        if ($apiResult['status'] === false) {
            return null;
        }

        $apiResponse = $apiResult['response'];
        if (strtolower($apiResponse['result']) != 'ok') {
            return null;
        }

        return $apiResponse;

    }

    public function sendSMS($mobileNumber = '', $messageContent = '') {

        if (is_null($mobileNumber) || (trim($mobileNumber) == '')) {
            return null;
        }

        if (is_null($messageContent) || (trim($messageContent) == '')) {
            return null;
        }

        $mobileNumberPlus = str_replace('+', '', $mobileNumber);
        $mobileNumberSpace = str_replace(' ', '', $mobileNumberPlus);
        $mobileNumberZero = ltrim($mobileNumberSpace, '00');
        if (trim($mobileNumberZero) == '') {
            return null;
        }

        $apiUrl = $this->getApiUrl();
        $targetUri = "send/";
        if (trim($apiUrl) == '') {
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

        $sendLang = $this->getLanguage();
        if (trim($sendLang) == '') {
            return null;
        }

        $testMode = $this->getTestMode();
        if (!is_bool($testMode)) {
            return null;
        }

        $senderIdResult = $this->getSenderIds();
        if (is_null($senderIdResult)) {
            return null;
        }

        $senderIdArray = (array_key_exists('senderid', $senderIdResult) && is_array($senderIdResult['senderid']) && (count($senderIdResult['senderid']) > 0)) ? $senderIdResult['senderid'] : [];
        if (count($senderIdArray) == 0) {
            return null;
        }

        $postUrl = $apiUrl . $targetUri;
        $postData = [
            "username" => $apiUsername,
            "password" => $apiPassword,
            "lang" => $sendLang,
            "test" => ($testMode) ? '1' : '0',
            "sender" => $senderIdArray[0],
            "mobile" => $mobileNumberZero,
            "message" => $messageContent,
        ];

        $apiService = new RestApiService();
        $apiResult = $apiService->processPostApi($postUrl, $postData, 'json', [], false);
        if ($apiResult['status'] === false) {
            return null;
        }

        $apiResponse = $apiResult['response'];
        if (strtolower($apiResponse['result']) != 'ok') {
            return null;
        }

        return $apiResponse;

    }

}
