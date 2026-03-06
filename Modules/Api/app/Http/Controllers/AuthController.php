<?php

namespace Modules\Api\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Cart\Models\Cart;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Api\Models\RefreshToken;
use Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Modules\Customer\Jobs\SendCustomerRegistrationEmail;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use Modules\WebConfigurationManagement\Models\OtpSetting;
use Modules\WebConfigurationManagement\Services\OtpService;
use Modules\WebConfigurationManagement\Models\EmailTemplate;
use Modules\WebConfigurationManagement\Models\OtpConfiguration;

class AuthController extends Controller
{
    /**
     * Log in a customer using email/password or phone/OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            if ($request->filled('email')) {
                $request->validate([
                    'email'    => 'required|email',
                    'password' => 'required',
                ]);

                $customer = Customer::where('email', $request->email)->first();
                if (!$customer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer not found for the provided email',
                    ], 404);
                }

                if (!Hash::check($request->password, $customer->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Incorrect password',
                    ], 401);
                }
            } elseif ($request->filled('phone')) {
                $rawPhone = preg_replace('/[^0-9+]/', '', $request->phone);
                $request->merge(['phone' => $rawPhone]);

                $request->validate([
                    'phone' => 'required|string',
                    'otp'   => 'nullable|digits:6',
                ]);

                $customer = Customer::where('phone', $rawPhone)->first();
                if (!$customer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer not found for the provided phone number',
                    ], 404);
                }

                $normalizedPhone = str_starts_with($rawPhone, '+') ? $rawPhone : '+91' . $rawPhone;

                if (!$request->filled('otp')) {
                    $otpService = app(OtpService::class);
                    $otpSent = $otpService->generateAndSendOtp($normalizedPhone);

                    if (!$otpSent) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to send OTP. Please try again.',
                        ], 500);
                    }

                    $staticEnabled = OtpSetting::where('key', 'static_otp_enabled')->value('value') === '1';
                    $staticCode = OtpSetting::where('key', 'static_otp_code')->value('value');

                    $response = [
                        'success' => true,
                        'message' => 'OTP sent successfully. Please verify the OTP.',
                    ];

                    if ($staticEnabled) {
                        Log::info('Static OTP enabled, returning static code');
                        $response['otp'] = $staticCode;
                        $response['message'] = 'Static OTP generated. Use it for verification.';
                    }

                    return response()->json($response, 200);
                } else {
                    $otpRecord = OtpConfiguration::where('mobile_number', $normalizedPhone)
                        ->where('otp', $request->otp)
                        ->where('expires_at', '>=', Carbon::now())
                        ->first();

                    if (!$otpRecord) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid or expired OTP',
                        ], 401);
                    }

                    $otpRecord->delete();
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide either an email or a phone number',
                ], 422);
            }

            $token = $customer->createToken('customer-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data'    => [
                    'customer' => [
                        'id'                => $customer->id,
                        'email'             => $customer->email,
                        'first_name'        => $customer->first_name,
                        'last_name'         => $customer->last_name,
                        'phone'             => str_starts_with($customer->phone, '+') ? $customer->phone : '+91' . $customer->phone,
                        'profile_image_url' => $customer->profile_path
                            ? asset('storage/' . $customer->profile_path)
                            : null,
                    ],
                    'token' => $token,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * function to register customer
     * email , password and first name are required
     * @param Request $request
     * @return  \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $rawPhone = $request->phone;
            $phone = preg_replace('/\D/', '', $rawPhone);
            if (str_starts_with($phone, '91') && strlen($phone) == 12) {
                $phone = substr($phone, 2);
            }
            $request->merge(['phone' => $phone]);

            $request->validate([
                'email'         => 'required|email|unique:customers,email',
                'password'      => 'required|string|min:6',
                'first_name'    => 'required|string|max:255',
                'phone'         => 'required|string|max:255',
                'last_name'     => 'nullable|string|max:255',
                'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'fingerprint_id' => 'nullable|string',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('customer', $imageName, 'public');
            }

            $customerCode = strtoupper(uniqid('CUST'));

            $customer = Customer::create([
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'profile_path'  => $imagePath,
                'customer_code' => $customerCode,
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'phone'         => $phone,
                'is_active'     => 1,
            ]);

            $token = $customer->createToken('customer-token')->plainTextToken;

            if ($request->filled('fingerprint_id')) {
                $newCart = Cart::where('guest_fingerprint_code', $request->fingerprint_id)->first();
                if ($newCart) {
                    $newCart->update([
                        'customer_id'   => $customer->id,
                        'customer_code' => $customer->customer_code,
                        'first_name'    => $customer->first_name,
                        'last_name'     => $customer->last_name,
                        'email'         => $customer->email,
                        'phone'         => $customer->phone,
                    ]);
                }
            }

            $content = [
                'email'         => $request->email,
                'customer_code' => $customerCode ?? '',
                'first_name'    => $request->first_name ?? '',
                'last_name'     => $request->last_name ?? '',
            ];

            $emailTemplate = EmailTemplate::where('slug', 'customer_register')->first();

            if ($emailTemplate) {
                EmailQueue::create([
                    'content'       => json_encode($content),
                    'type'          => 'customer_register',
                    'template_id'   => $emailTemplate->id,
                    'email'         => $request->email,
                    'is_dispatched' => 0,
                ]);
            } else {
                Log::error('Email template with slug "customer_register" not found.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data'    => [
                    'customer' => [
                        'id'                => $customer->id,
                        'email'             => $customer->email,
                        'first_name'        => $customer->first_name,
                        'last_name'         => $customer->last_name,
                        'phone'             => $customer->phone,
                        'profile_image_url' => $customer->profile_path
                            ? asset('storage/' . $customer->profile_path)
                            : null,
                    ],
                    'token'   => $token,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log out the authenticated user and invalidate the token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Send password reset link to the customer's email.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:customers,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $status = Password::broker('customers')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link sent successfully.'], 200)
            : response()->json(['error' => 'Failed to send password reset link.'], 500);
    }


    /**
     * Reset customer password using the token.
     * @param Request $request
     * @return mixed
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:customers,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($customer, $password) {
                $customer->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($customer));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully.'], 200)
            : response()->json(['error' => 'Failed to reset password.'], 500);
    }

    /**
     * function to change user password, authenticated user can only update
     * @param Request $request
     * @return json
     */
    public function changePassword(Request $request)
    {
        try {
            $authUser = Auth::user();
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (!Hash::check($request->current_password, $authUser->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The current password is incorrect.',
                ], 400);
            }

            $authUser->forceFill([
                'password' => Hash::make($request->new_password),
            ])->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh the access token using a refresh token.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => 'required',
            ]);

            $refreshToken = RefreshToken::where('token', hash('sha256', $request->refresh_token))->first();

            if (!$refreshToken || $refreshToken->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired refresh token',
                ], 401);
            }

            $user = $refreshToken->user;

            $user->tokens()->delete();
            $refreshToken->delete();

            $newAccessToken = $user->createToken('access-token')->plainTextToken;
            $newRefreshTokenString = Str::random(64);

            RefreshToken::create([
                'user_id' => $user->id,
                'token' => hash('sha256', $newRefreshTokenString),
                'expires_at' => Carbon::now()->addDays(7),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshTokenString,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
