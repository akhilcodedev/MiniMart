<?php

namespace Modules\WebConfigurationManagement\Services;

use Carbon\Carbon;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Modules\WebConfigurationManagement\Models\OtpSetting;
use Modules\WebConfigurationManagement\Models\OtpConfiguration;

class OtpService
{
    protected $twilioClient;

    /**
     * Initialize the Twilio client with credentials from config.
     */
    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->twilioClient = new Client($sid, $token);
    }

    /**
     * Generate and send an OTP to the given mobile number.
     *
     * @param string $mobileNumber
     * @return bool
     */
    public function generateAndSendOtp($mobileNumber)
    {
        $mobileNumber = preg_replace('/[^0-9+]/', '', $mobileNumber);
        $phoneForSms = str_starts_with($mobileNumber, '+') ? $mobileNumber : '+91' . $mobileNumber;
        Log::info('Phone number for SMS: ' . $phoneForSms);

        $staticEnabled = OtpSetting::where('key', 'static_otp_enabled')->value('value') === '1';
        $staticCode = OtpSetting::where('key', 'static_otp_code')->value('value');

        $otp = $staticEnabled && $staticCode ? $staticCode : str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(10);

        if (!$staticEnabled) {
            try {
                $this->sendOtpSms($phoneForSms, $otp);
            } catch (\Exception $e) {
                Log::error('OTP sending failed: ' . $e->getMessage());
                return false;
            }
        }

        OtpConfiguration::updateOrCreate(
            ['mobile_number' => $mobileNumber],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );
        return true;
    }


    /**
     * Store the OTP in the database.
     *
     * @param string $mobileNumber
     * @param string $otp
     * @return bool
     */
    private function storeOtp($mobileNumber, $otp)
    {
        $expiresAt = Carbon::now()->addMinutes(10);
        OtpConfiguration::updateOrCreate(
            ['mobile_number' => $mobileNumber],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );
        return true;
    }



    /**
     * Send OTP via SMS using Twilio.
     *
     * @param string $mobileNumber
     * @param int $otp
     * @return bool
     */
    private function sendOtpSms($mobileNumber, $otp)
    {
        $mobileNumber = preg_replace('/[^0-9+]/', '', $mobileNumber);

        if (!str_starts_with($mobileNumber, '+')) {
            $mobileNumber = '+' . $mobileNumber;
        }

        $from = config('services.twilio.from');

        try {
            $message = $this->twilioClient->messages->create(
                $mobileNumber,
                [
                    'from' => $from,
                    'body' => "Welcome to Store9 :) Your OTP is: {$otp}. It will expire in 10 minutes."
                ]
            );
            Log::info('SMS sent successfully. SID: ' . $message->sid);
            return true;
        } catch (\Exception $e) {
            Log::error('Twilio SMS Error: ' . $e->getMessage());
            return false;
        }
    }
}
