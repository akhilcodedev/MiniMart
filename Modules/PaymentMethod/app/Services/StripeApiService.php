<?php

namespace Modules\PaymentMethod\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\PaymentMethod\Models\PaymentMethod;
use Stripe\Checkout\Session;
use Stripe\Collection;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentLink;
use Stripe\Price;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\WebhookEndpoint;

class StripeApiService
{

    protected $publishableKey;
    protected $secretKey;
    protected $webhookSecretKey;
    protected $client;

    public function __construct()
    {

        $stripeObj = PaymentMethod::firstWhere('code', PaymentMethod::PAYMENT_METHOD_CODE_STRIPE);

        $this->publishableKey = null;
        $this->secretKey = null;
        $this->client = null;
        if ($stripeObj) {
            $stripeObj->attributes;
            if (isset($stripeObj->attributes) && (count($stripeObj->attributes) > 0)) {
                foreach ($stripeObj->attributes as $attribute) {
                    if (($attribute->name == 'Publishable Key') && isset($attribute->value)) {
                        $this->publishableKey = $attribute->value ?? null;
                    }
                    if (($attribute->name == 'Secret Key') && isset($attribute->value)) {
                        $this->secretKey = $attribute->value ?? null;
                    }
                    if (($attribute->name == 'Webhook Secret Key') && isset($attribute->value)) {
                        $this->webhookSecretKey = $attribute->value ?? null;
                    }
                }
            }
        }

        if (!is_null($this->publishableKey) && !is_null($this->secretKey)) {
            try {
                $this->client = new StripeClient($this->secretKey);
            } catch (\Exception $ex) {
                $this->client = null;
            }
        }

    }

    /**
     * Get the Stripe Service Client Object
     * @return StripeClient|null
     */
    public function getServiceClient(): ?StripeClient
    {
        return $this->client;
    }

    /**
     * Get the Secret key used for the Stripe Webhook Events.
     * @return string|null
     */
    public function getWebhookSecretKey(): ?string
    {
        return $this->webhookSecretKey;
    }

    /**
     * Create a Stripe Customer Object.
     * @param array|null $data
     * @return Customer|null
     */
    public function createStripeCustomerObject(array $data = null): ?Customer
    {

        if (is_null($data) || !is_array($data) || (count($data) == 0)) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->customers->create($data);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe createStripeCustomerObject :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Retrieve the Stripe Customer data using the Customer ID.
     * @param string|null $customerId
     * @return Customer|null
     */
    public function fetchCustomerById(?string $customerId = ''): ?Customer
    {

        if (is_null($customerId) || !is_string($customerId) || (trim($customerId) == '')) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->customers->retrieve($customerId, []);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe fetchCustomerById :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Retrieve the Stripe Customer data using the E-Mail ID.
     * @param string|null $mailId
     * @return Customer|null
     */
    public function searchCustomerByMailId(?string $mailId = null): ?Customer
    {

        if (is_null($mailId) || !is_string($mailId) || (trim($mailId) == '') || (filter_var(trim($mailId), FILTER_VALIDATE_EMAIL) === false)) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            $customerList = $serviceClient->customers->search([
                'query' => 'email:\'' . $mailId . '\'',
            ]);
            return (count($customerList->data) > 0) ? $customerList->data[array_key_first($customerList->data)] : null;
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe searchCustomerByMailId :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Create a Stripe Price Object
     * @param $data array|null
     * @return Price|null
     */
    public function createPriceObject(array $data = null): ?Price
    {

        if (is_null($data) || !is_array($data) || (count($data) == 0)) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->prices->create($data);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe createPriceObject :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Retrieve the Stripe Price data using the Price ID.
     * @param string|null $priceId
     * @return Price|null
     */
    public function fetchPriceById(?string $priceId = ''): ?Price
    {

        if (is_null($priceId) || !is_string($priceId) || (trim($priceId) == '')) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->prices->retrieve($priceId, []);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe fetchPriceById :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Create a Stripe Checkout Session
     * @param array|null $data
     * @return Session|null
     */
    public function createCheckoutSession(array $data = null): ?Session
    {

        if (is_null($data) || !is_array($data) || (count($data) == 0)) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->checkout->sessions->create($data);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe createCheckoutSession :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Retrieve the Stripe Checkout Session data using the Session ID.
     * @param string|null $sessionId
     * @return Session|null
     */
    public function fetchCheckoutSessionById(?string $sessionId = ''): ?Session
    {

        if (is_null($sessionId) || !is_string($sessionId) || (trim($sessionId) == '')) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->checkout->sessions->retrieve($sessionId, [
                'expand' => ['line_items'],
            ]);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe fetchCheckoutSessionById :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Check the Payment status of a Stripe Checkout Session using the Session ID.
     * @param string|null $sessionId
     * @return array|null
     */
    public function checkPaymentStatusBySessionId(?string $sessionId = ''): ?array
    {

        $checkoutSession = $this->fetchCheckoutSessionById($sessionId);
        if (is_null($checkoutSession)) {
            return null;
        }

        if ($checkoutSession->payment_status != 'unpaid') {
            return [
                'paid' => true,
                'sessionData' => $checkoutSession
            ];
        } else {
            return [
                'paid' => false,
                'sessionData' => $checkoutSession
            ];
        }

    }

    /**
     * Create a Stripe Payment Link Object
     * @param array|null $data
     * @return PaymentLink|null
     */
    public function createPaymentLink(array $data = null): ?PaymentLink
    {

        if (is_null($data) || !is_array($data) || (count($data) == 0)) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->paymentLinks->create($data);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe createPaymentLink :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Retrieve the Stripe Payment Link data using the Link ID.
     * @param string|null $linkId
     * @return PaymentLink|null
     */
    public function fetchPaymentLinkById(?string $linkId = ''): ?PaymentLink
    {

        if (is_null($linkId) || !is_string($linkId) || (trim($linkId) == '')) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->paymentLinks->retrieve($linkId, []);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe fetchPaymentLinkById :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Create a Stripe Payment Intent Object
     * @param array|null $data
     * @return PaymentIntent|null
     */
    public function createPaymentIntent(array $data = null): ?PaymentIntent
    {

        if (is_null($data) || !is_array($data) || (count($data) == 0)) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->paymentIntents->create($data);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe createPaymentIntent :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Retrieve the Stripe Payment Intent data using the Link ID.
     * @param string|null $intentId
     * @return PaymentIntent|null
     */
    public function fetchPaymentIntentById(?string $intentId = ''): ?PaymentIntent
    {

        if (is_null($intentId) || !is_string($intentId) || (trim($intentId) == '')) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->paymentIntents->retrieve($intentId, []);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe fetchPaymentIntentById :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Retrieve the Stripe Payment Intent data collection using the limit.
     * @param int|null $limit
     * @return Collection|null
     */
    public function fetchAllPaymentIntents(?int $limit = 10): ?Collection
    {

        $limitClean = (!is_null($limit) && is_numeric($limit) && ((int)$limit > 0)) ? (int)$limit : 10;

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->paymentIntents->all(['limit' => $limitClean]);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe fetchAllPaymentIntents :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Register a server URL to handle all the Stripe Webhook Events.
     * @param string|null $webhookUrl
     * @return WebhookEndpoint|null
     */
    public function registerStripeWebhook(?string $webhookUrl = null): ?WebhookEndpoint
    {

        if (is_null($webhookUrl) || !is_string($webhookUrl) || (trim($webhookUrl) == '')) {
            return null;
        }

        $serviceClient = $this->getServiceClient();
        if (is_null($serviceClient)) {
            return null;
        }

        try {
            return $serviceClient->webhookEndpoints->create([
                'enabled_events' => ['*'],
                'url' => trim($webhookUrl),
            ]);
        } catch (Exception $exception) {
            Log::error('An exception is caught on Stripe registerStripeWebhook :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return null;
        }

    }

    /**
     * Checks whether the given string is a valid JSON string.
     * @param $data string|null
     * @return bool
     */
    public function checkIsValidJSONString(?string $data): bool
    {
        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

}
