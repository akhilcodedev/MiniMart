<?php

namespace Modules\Base\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class RestApiService
{

    private string $apiUserTokenSessionKey = 'api_user_token';
    private string $apiChannel = 'zest_laravel';
    private int $timeoutSeconds = 600;
    private int $retryLoop = 5;
    private int $retryLoopInterval = 60;

    /**
     * RestApiService constructor.
     */
    public function __construct() {

    }

    /**
     * Get the Timeout Configs of the RESTFul API  Call.
     *
     * @return array
     */
    private function getApiTimeoutConfigs(): array
    {
        return [
            'timeout' => $this->timeoutSeconds,
            'retryLoop' => $this->retryLoop,
            'retryLoopInterval' => $this->retryLoopInterval,
        ];
    }

    /**
     * Get the Session Key for the RESTFul API Authentication token.
     *
     * @return string
     */
    private function getApiBearerTokenKey(): string
    {
        return $this->apiUserTokenSessionKey . '_' . $this->apiChannel;
    }

    /**
     * Get the Authentication Bearer Token for the API Calls.
     *
     * @param bool $force
     * @param string $url
     * @param string $username
     * @param string $password
     *
     * @return string|null
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getApiUserBearerToken(bool $force = false, string $url = '', string $username = '', string $password = ''): ?string
    {

        $sessionTokenKey = $this->getApiBearerTokenKey();
        if(session()->has($sessionTokenKey) && ($force === false)) {
            $cleanToken = trim(session()->get($sessionTokenKey));
            if (!is_null($cleanToken) && ($cleanToken != '')) {
                return $cleanToken;
            }
        }

        $authUrl = $url;
        $authCredentials = [
            'username' => $username,
            'password' => $password
        ];

        $apiResult = $this->processPostApi($authUrl, $authCredentials, 'json', [], false);

        if ($apiResult['status']) {
            $responseData = $apiResult['response'];
            session()->put($sessionTokenKey, $responseData);
            return $responseData;
        }

        return null;

    }

    /**
     * Process the RESTFul API Call.
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @param string $contentType
     * @param array $headers
     * @param bool $authenticate
     * @param bool $forceAuthenticate
     * @param string $username
     * @param string $password
     * @param bool $rawBody
     *
     * @return array
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function processRestApiCall(string $method = 'GET', string $url = '', array $params = [], string $contentType = 'json', array $headers = [], bool $authenticate = true, bool $forceAuthenticate = false, string $username = '', string $password = '', bool $rawBody = false): array
    {

        $httpMethods = ['GET', 'POST', 'PUT', 'DELETE'];
        $cleanMethod = strtoupper(str_replace(' ', '_', trim($method)));
        if(!in_array($cleanMethod, $httpMethods)) {
            return [
                'status' => false,
                'message' => 'Invalid HTTP API Method!',
                'response' => null,
                'code' => 400,
            ];
        }

        if (is_null($url) || (trim($url) == '')) {
            return [
                'status' => false,
                'message' => 'Invalid API URL!',
                'response' => null,
                'code' => 400,
            ];
        }

        if (!is_null($params) && !is_array($params)) {
            return [
                'status' => false,
                'message' => 'Invalid Params input!',
                'response' => null,
                'code' => 400,
            ];
        }

        if (!is_null($headers) && !is_array($headers)) {
            return [
                'status' => false,
                'message' => 'Invalid Headers input!',
                'response' => null,
                'code' => 400,
            ];
        }

        $contentTypeList = ['json', 'multipart', 'formdata', 'urlencoded'];
        $cleanContentType = strtolower(str_replace(' ', '_', trim($contentType)));
        $finalContentType = in_array($cleanContentType, $contentTypeList) ? $cleanContentType : 'json';

        $apiResponse = null;

        try {

            $timeoutSettings = $this->getApiTimeoutConfigs();
            $pendingRequest = Http::acceptJson()
                ->retry($timeoutSettings['retryLoop'], $timeoutSettings['retryLoopInterval']);

            if ($cleanMethod != 'GET') {
                switch ($finalContentType) {
                    case 'json':
                        $pendingRequest->asJson();
                        break;
                    case 'multipart':
                        $pendingRequest->asMultipart();
                        break;
                    case 'formdata':
                    case 'urlencoded':
                        $pendingRequest->asForm();
                        break;
                }
            }

            if(!is_null($headers) && is_array($headers) && (count($headers) > 0)) {
                $pendingRequest->withHeaders($headers);
            }

            if ($authenticate) {
                $authToken = $this->getApiUserBearerToken($forceAuthenticate, $url, $username, $password);
                if(!$authToken) {
                    return [
                        'status' => false,
                        'message' => 'The API could not authenticate!',
                        'response' => null,
                        'code' => 401,
                    ];
                }
                $pendingRequest->withToken($authToken);
            }

            switch ($cleanMethod) {
                case 'GET':
                    $apiResponse = $pendingRequest->get($url, $params);
                    break;
                case  'POST':
                    $apiResponse = $pendingRequest->post($url, $params);
                    break;
                case 'PUT':
                    $apiResponse = $pendingRequest->put($url, $params);
                    break;
                case 'DELETE':
                    $apiResponse = $pendingRequest->delete($url, $params);
                    break;
            }

            if (($apiResponse->status() === 401) && $authenticate) {
                $authToken = $this->getApiUserBearerToken(true, $url, $username, $password);
                if (!$authToken) {
                    return [
                        'status' => false,
                        'message' => 'The API could not authenticate!',
                        'response' => null,
                        'code' => $apiResponse->status(),
                    ];
                }
                $this->processRestApiCall($method, $url, $params, 'json', $headers, $authenticate);
            }

            if ($apiResponse->failed()) {
                return [
                    'status' => false,
                    'message' => 'The API call failed!',
                    'response' => null,
                    'code' => $apiResponse->status(),
                ];
            }

            return [
                'status' => true,
                'message' => '',
                'response' => (is_bool($rawBody) && ((bool)$rawBody === true)) ? $apiResponse->body() : $apiResponse->json(),
                'code' => $apiResponse->status(),
            ];

        } catch(\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'response' => null,
                'code' => 500,
            ];
        }

    }

    /**
     * Execute the GET method RESTFul API Call.
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param bool $authenticate
     * @param bool $forceAuthenticate
     * @param string $username
     * @param string $password
     * @param bool $rawBody
     *
     * @return array
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function processGetApi(string $url = '', array $params = [], array $headers = [], bool $authenticate = true, bool $forceAuthenticate = false, string $username = '', string $password = '', bool $rawBody = false): array
    {
        return $this->processRestApiCall('GET', $url, $params, '', $headers, $authenticate, $forceAuthenticate, $username, $password, $rawBody);
    }

    /**
     * Execute the POST method RESTFul API Call.
     *
     * @param string $url
     * @param array $params
     * @param string $contentType
     * @param array $headers
     * @param bool $authenticate
     * @param bool $forceAuthenticate
     * @param string $username
     * @param string $password
     * @param bool $rawBody
     *
     * @return array
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function processPostApi(string $url = '', array $params = [], string $contentType = 'json', array $headers = [], bool $authenticate = true, bool $forceAuthenticate = false, string $username = '', string $password = '', bool $rawBody = false): array
    {
        return $this->processRestApiCall('POST', $url, $params, $contentType, $headers, $authenticate, $forceAuthenticate, $username, $password, $rawBody);
    }

    /**
     * Execute the PUT method RESTFul API Call.
     *
     * @param string $url
     * @param array $params
     * @param string $contentType
     * @param array $headers
     * @param bool $authenticate
     * @param bool $forceAuthenticate
     * @param string $username
     * @param string $password
     * @param bool $rawBody
     *
     * @return array
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function processPutApi(string $url = '', array $params = [], string $contentType = 'json', array $headers = [], bool $authenticate = true, bool $forceAuthenticate = false, string $username = '', string $password = '', bool $rawBody = false): array
    {
        return $this->processRestApiCall('PUT', $url, $params, $contentType, $headers, $authenticate, $forceAuthenticate, $username, $password, $rawBody);
    }

    /**
     * Execute the DELETE method RESTFul API Call.
     *
     * @param string $url
     * @param array $params
     * @param string $contentType
     * @param array $headers
     * @param bool $authenticate
     * @param bool $forceAuthenticate
     * @param string $username
     * @param string $password
     * @param bool $rawBody
     *
     * @return array
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function processDeleteApi(string $url = '', array $params = [], string $contentType = 'json', array $headers = [], bool $authenticate = true, bool $forceAuthenticate = false, string $username = '', string $password = '', bool $rawBody = false): array
    {
        return $this->processRestApiCall('DELETE', $url, $params, $contentType, $headers, $authenticate, $forceAuthenticate, $username, $password, $rawBody);
    }

}
