<?php

namespace Modules\PaymentMethod\Services;

use Exception;
use Modules\Base\Services\RestApiService;
use Illuminate\Support\Facades\Log;
use Modules\PaymentMethod\Models\PaymentMethod;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TelrActions
{
    private $telrLiveMode = '';
    private $telrApiProrocol = '';
    private $telrApiDomain = '';
    private $telrApiOrderUri = '';
    private $telrApiUrl = '';
    private $telrStoreId = '';
    private $telrAuthKey = '';
    private $telrTransactionType = '';

    public function __construct()
    {
        $this->setTelrServiceVariables();
    }

    public function getLiveMode() {
        return $this->telrLiveMode;
    }

    public function getApiUrl() {
        return $this->telrApiUrl;
    }

    public function getStoreId() {
        return $this->telrStoreId;
    }

    public function getAuthKey() {
        return $this->telrAuthKey;
    }

    public function getTransactionType() {
        return $this->telrAuthKey;
    }

    public function createTelrOrder($postData = []) {

        try {

            if (is_null($postData) || !is_array($postData) || (count($postData) == 0)) {
                return null;
            }

            $requiredKeys = ['order', 'customer', 'return'];
            foreach ($requiredKeys as $keyEl) {
                if (!array_key_exists($keyEl, $postData)) {
                    return null;
                }
            }

            $postData['method'] = 'create';
            $postData['store'] = $this->getStoreId();
            $postData['authkey'] = $this->getAuthKey();

            $postData['order']['test'] = ($this->getLiveMode() == '1') ? 0 : 1;
            $postData['order']['trantype'] = $this->getTransactionType();

            $postUrl = $this->getApiUrl();
            if (trim($postUrl) == '') {
                return null;
            }

            $apiService = new RestApiService();
            $apiResult = $apiService->processPostApi($postUrl, $postData, 'json', [], false);
            if ($apiResult['status'] === false) {
                return null;
            }

            return $apiResult['response'];

        } catch (NotFoundExceptionInterface|ContainerExceptionInterface|Exception $ex) {
            Log::error('An exception is caught on Telr createTelrOrder :');
            Log::error($ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            Log::error($ex->getTraceAsString());
            return null;
        }

    }

    public function checkTelrOrder($orderRef = '') {

        try {

            if (is_null($orderRef) || (trim($orderRef) == '')) {
                return null;
            }

            $postUrl = $this->getApiUrl();
            if (trim($postUrl) == '') {
                return null;
            }

            $postData = [
                'method'  => 'check',
                'store'   => $this->getStoreId(),
                'authkey' => $this->getAuthKey(),
                'order'   => [
                    'ref' => $orderRef
                ],
            ];

            $apiService = new RestApiService();
            $apiResult = $apiService->processPostApi($postUrl, $postData, 'json', [], false);
            if ($apiResult['status'] === false) {
                return null;
            }

            return $apiResult['response'];

        } catch (NotFoundExceptionInterface|ContainerExceptionInterface|Exception $ex) {
            Log::error('An exception is caught on Telr checkTelrOrder :');
            Log::error($ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            Log::error($ex->getTraceAsString());
            return null;
        }

    }

    private function setTelrServiceVariables() {

        $telrObj = PaymentMethod::firstWhere('code', PaymentMethod::PAYMENT_METHOD_CODE_TELR);
        if ($telrObj) {
            $telrObj->attributes;
            if (isset($telrObj->attributes) && (count($telrObj->attributes) > 0)) {
                foreach ($telrObj->attributes as $attribute) {
                    if (($attribute->name == 'Transaction Type') && isset($attribute->value)) {
                        $this->telrTransactionType = $attribute->value ?? null;
                    }
                    if (($attribute->name == 'Store Id') && isset($attribute->value)) {
                        $this->telrStoreId = $attribute->value ?? null;
                    }
                    if (($attribute->name == 'Auth Key') && isset($attribute->value)) {
                        $this->telrAuthKey = $attribute->value ?? null;
                    }
                }
            }
            if (isset($this->telrTransactionType) && isset($this->telrStoreId) && isset($this->telrAuthKey)) {
                $this->telrLiveMode = ($telrObj->test_mode == PaymentMethod::TEST_MODE_NO) ? '1' : '0';
                $this->telrApiProrocol = 'https';
                $this->telrApiDomain = 'secure.telr.com';
                $this->telrApiOrderUri = 'gateway/order.json';
                $this->telrApiUrl = $this->telrApiProrocol . '://' . $this->telrApiDomain . '/' .$this->telrApiOrderUri;
            }
        }

    }

}
