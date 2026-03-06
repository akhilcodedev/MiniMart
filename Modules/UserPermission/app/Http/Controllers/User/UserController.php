<?php

namespace Modules\UserPermission\Http\Controllers\User;

use Exception;
use App\Models\User;
use App\Models\TimeZone;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Configuration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Modules\UserManagement\Models\Country;
use Modules\UserManagement\Models\UserDevice;
use Modules\UserManagement\Models\CustomerSavedAddress;

class  UserController extends Controller
{
    /**
     * Display all users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $users = User::all();

            return view('userpermission::user.index', compact('users'));
        } catch (Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while fetching users.');
        }
    }

    /**
     * Display the admin profile page.
     *
     * @return \Illuminate\View\View The admin profile view.
     */
    public function adminProfilePage()
    {
        $user = Auth::user();
        $users = User::where('id', $user->id)->get();
        return view('userpermission::admin.admin-profile', compact('users'));
    }

    /**
     * Update the profile of the currently authenticated admin user.
     *
     * @param \Illuminate\Http\Request $request Request containing updated profile data.
     * @return \Illuminate\Http\RedirectResponse Redirects with a status message.
     */
    public function updateAdminProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        try {
            $user->update($validated);
            return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update profile.']);
        }
    }

    /**
     * Update the profile image of the authenticated admin user.
     *
     * @param \Illuminate\Http\Request $request Request containing image data or removal flag.
     * @return \Illuminate\Http\RedirectResponse Redirects back with a status message.
     */
    public function updateAdminProfileImage(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'image_path' => [
                'sometimes',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
                'dimensions:max_width=2000,max_height=2000'
            ],
            'avatar_remove' => 'sometimes|boolean'
        ]);

        try {
            if ($request->boolean('avatar_remove')) {
                if ($user->image_path) {
                    Storage::disk('public')->delete($user->image_path);
                    $user->image_path = null;
                }
            } elseif ($request->hasFile('image_path')) {
                if ($user->image_path) {
                    Storage::disk('public')->delete($user->image_path);
                }
                $filename = 'user-' . $user->id . '-' . time() . '.' . $request->file('image_path')->extension();
                $path = $request->file('image_path')->storeAs('image_path', $filename, 'public');
                $user->image_path = $path;
            }

            $user->save();
            return redirect()->back()->with('success', 'Profile image updated successfully.');
        } catch (\Exception $e) {
            Log::error("Profile image update failed for user {$user->id}: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update profile image. Please try again.']);
        }
    }

    /**
     * Update the user's password.
     * Assumes you have a named route 'user.password.update' pointing here.
     */
    public function updateAdminPassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'new_password' => [
                'required',
                'string',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed',
            ],
            'new_password_confirmation' => ['required'],
        ]);
        try {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
            return redirect()->back()->with('password_success', 'Password updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['password_error' => 'An error occurred while updating your password.']);
        }
    }

    /**
     * Store a newly created user.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 1,
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('user.index')->with('success', "{$newUser->name} user created successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while creating the user.');
        }
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        if (request()->ajax()) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        }

        return view('user.edit', compact('user'));
    }

    /**
     * Update the specified user.
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'success' => true,
            'message' => "{$user->name} has been updated successfully."
        ]);
    }

    /**
     * Remove the specified user.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->delete();
            DB::commit();
            return redirect()->route('user.index')->with('success', "{$user->name} has been permanently deleted successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while deleting ' . ($user->name ?? 'the user') . '.');
        }
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->back()->with('error', 'User not authenticated.');
            }

            $rules = [
                'name' => 'required|string|max:50',
                'phone_number' => 'required|min:7|max:15',
                'country_id' => 'required',
                'timezone' => 'required',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];

            $validation = Validator::make($request->all(), $rules);
            if ($validation->fails()) {
                return redirect()->back()->withInput()->withErrors($validation);
            }

            $data = $request->only('name', 'phone_number', 'country_id', 'timezone');

            if ($request->hasFile('image')) {
                $path = 'images/vendor-dashboard/profile/';
                $filename = time() . '.' . $request->image->extension();
                $request->image->move(public_path($path), $filename);
                $data['profile_image'] = $path . $filename;
            } else {
                $data['profile_image'] = $user->profile_image ?? '/assets/images/dodelivery-logos/dodelivery-logo.png';
            }

            $user->update($data);

            return redirect()->back()->with('success', 'Profile updated successfully!');
        } catch (Exception $exception) {
            Log::error('Error updating profile: ' . $exception->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the profile.');
        }
    }

    /**
     * Show the user's profile page.
     *
     * @return \Illuminate\View\View
     */
    public function userProfilePage()
    {
        $client_preference_detail = Configuration::first();
        $client = Auth::user();
        $countries = Country::all();
        $tzlist = TimeZone::all();

        return view('userpermission::user.user-profile', compact('client_preference_detail', 'client', 'countries', 'tzlist'));
    }

    public function customerProfileUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'password' => 'nullable|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }
        try {
            $user = auth()->user();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete the authenticated customer's account.
     *
     * @return \Illuminate\Http\JsonResponse Status message.
     */
    public function deleteCustomer()
    {
        $user = Auth::user();
        if ($user) {
            $user->delete();
            Auth::logout();

            return response()->json([
                'status' => true,
                'message' => 'Account deleted successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ], 404);
    }

    /**
     * Get saved customer addresses based on phone number.
     *
     * @param \Illuminate\Http\Request $request Request containing 'receiver_mobile'.
     * @return \Illuminate\Http\JsonResponse Address data or indicates non-existence.
     */
    public function getCustomerSavedAddress(Request $request)
    {
        $receiverMobile = $request->input('receiver_mobile');

        $customerAddresses = CustomerSavedAddress::where('phone_number', $receiverMobile)->get();

        if ($customerAddresses->isNotEmpty()) {
            $data = $customerAddresses->map(function ($address) {
                return [
                    'name' => $address->name,
                    'address' => $address->address,
                    'additional_address' => $address->additional_address,
                    'phone_number' => $address->phone_number,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                ];
            });

            return response()->json([
                'exists' => true,
                'data' => $data
            ]);
        } else {
            return response()->json(['exists' => false]);
        }
    }

    /**
     * Store or update a user's FCM token for web devices.
     *
     * @param \Illuminate\Http\Request $request Request containing fcm_token and user_id.
     * @return \Illuminate\Http\JsonResponse Status message.
     */
    public function storeFcm(Request $request)
    {
        if ($request->fcm_token) {
            $status = UserDevice::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                    'device_type' => 'web'
                ],
                [
                    'device_token' => $request->fcm_token
                ]
            );

            if ($status) {
                return response()->json(['message' => 'Token successfully stored.']);
            } else {
                return response()->json(['message' => 'Token not stored.']);
            }
        } else {
            return response()->json(['message' => 'Token not found']);
        }
    }
}
