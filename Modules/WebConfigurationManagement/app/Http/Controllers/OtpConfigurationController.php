<?php

namespace Modules\WebConfigurationManagement\Http\Controllers;

use Carbon\Carbon;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\WebConfigurationManagement\Models\OtpSetting;
use Modules\WebConfigurationManagement\Services\OtpService;
use Modules\WebConfigurationManagement\Models\OtpConfiguration;

class OtpConfigurationController extends Controller
{


    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('webconfigurationmanagement::system-configuration.otp');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('webconfigurationmanagement::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string',
        ]);

        $mobileNumber = $request->mobile_number;

        if ($this->otpService->generateAndSendOtp($mobileNumber)) {
            return redirect()->route('otp.index')->with('success', 'OTP sent successfully!');
        } else {
            return redirect()->route('otp.index')->withErrors(['Failed to send OTP.']);
        }
    }

    /**
     * Sends OTP SMS using Twilio.
     */
    protected function sendOtpSms($mobileNumber, $otp)
    {
        $sid    = config('services.twilio.sid');
        $token  = config('services.twilio.token');
        $from   = config('services.twilio.from');

        try {
            $client = new Client($sid, $token);

            $message = $client->messages->create(
                $mobileNumber,
                [
                    'from' => $from,
                    'body' => "Your OTP is: {$otp}. It will expire in 10 minutes."
                ]
            );

            Log::info('SMS sent successfully. SID: ' . $message->sid);
            return $message;
        } catch (\Exception $e) {
            Log::error('Twilio SMS Sending Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Save static OTP settings.
     *
     * @param \Illuminate\Http\Request $request Request containing static OTP data.
     * @return \Illuminate\Http\RedirectResponse Redirects back with a status message.
     */
    public function saveStatic(Request $request)
    {
        $request->validate([
            'static_otp_enabled' => 'nullable|boolean',
            'static_otp_code' => 'required_if:static_otp_enabled,1|digits:6'
        ]);

        OtpSetting::updateOrCreate(
            ['key' => 'static_otp_enabled'],
            ['value' => $request->static_otp_enabled ? '1' : '0']
        );

        OtpSetting::updateOrCreate(
            ['key' => 'static_otp_code'],
            ['value' => $request->static_otp_code ?? '']
        );

        return redirect()->back()->withInput()->with('success', 'Static OTP settings saved.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('webconfigurationmanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('webconfigurationmanagement::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
