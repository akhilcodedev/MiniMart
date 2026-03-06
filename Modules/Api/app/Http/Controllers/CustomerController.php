<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\Customer\Models\CustomerAddress;

class CustomerController extends Controller
{

    /**
     * function to get customer profile data
     * only authorised can access the api
     * @return json
     */
    public function getProfile()
    {
        try {
            $authUser = Auth::user();
            if ($authUser) {
                return response()->json([
                    'status' => true,
                    'message' => "Success",
                    'data' => $authUser
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Unauthorised",
                    'error' => $authUser
                ], 500);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => "Something went wrong",
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * function to get customer addresses , authenticated customer addresses will be returned
     * @return json
     */
    public function getAddresses()
    {
        try {
            $authUser = Auth::user();
            $userAddress = CustomerAddress::where('customer_id', $authUser->id)->get();
            return response()->json([
                'status' => true,
                'message' => "Success",
                'data' => $userAddress
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => "Something went wrong",
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Function to fetch customer single address by id , pass address id as parameter
     * @param $id
     * @return mixed
     */
    public function getAddressById($id){
        try {
            $authUser = Auth::user();
            if($id){
                $userAddress = CustomerAddress::where('customer_id', $authUser->id)->where('id', $id)->first();
                if($userAddress){
                    return response()->json([
                        'status' => true,
                        'message' => "Success",
                        'data' => $userAddress
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => "Address not found for the user ",
                    ], 404);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Address id required",
                ], 404);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => "Something went wrong",
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * update customer address, id passed as parameter and update data will avail in request
     * @param $id
     * @return mixed
     */
    public function updateAddress(Request $request, $id)
    {
        try {

            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'address_line1' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',
                'type' => 'nullable',
                'is_default' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Address ID is required',
                ], 400);
            }

            $userAddress = CustomerAddress::where('customer_id', $authUser->id)->where('id', $id)->first();
            if (!$userAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'Address not found for the user',
                ], 404);
            }

            $userAddress->update([
                'address_line1' => $request->input('address_line1', $userAddress->address_line1),
                'address_line2' => $request->input('address_line2', $userAddress->address_line2),
                'city' => $request->input('city', $userAddress->city),
                'state' => $request->input('state', $userAddress->state),
                'postal_code' => $request->input('postal_code', $userAddress->postal_code),
                'country' => $request->input('country', $userAddress->country),
                'type' => $request->input('type', $userAddress->type),
                'is_default' => $request->input('is_default', $userAddress->is_default),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Address updated successfully',
                'data' => $userAddress,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Update customer profile , if have profile image it will delete the old image and reinstall the new
     * @param Request $request
     * @return mixed
     */
    public function updateProfile(Request $request)
    {
        try {
            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:customers,email,' . $authUser->id,
                'phone' => 'nullable|string|max:15|unique:customers,phone,' . $authUser->id,
                'profile' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->hasFile('profile')) {
                if ($authUser->profile_path && Storage::disk('public')->exists($authUser->profile_path)) {
                    Storage::disk('public')->delete($authUser->profile_path);
                }
                $image = $request->file('profile');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imageName = str_replace(' ', '_', $imageName);
                $imageName = trim($imageName);
                $imagePath = $image->storeAs('customer', $imageName, 'public');
                $authUser->profile_path = $imagePath;
            }

            $authUser->update([
                'first_name' => $request->input('first_name', $authUser->first_name),
                'last_name' => $request->input('last_name', $authUser->last_name),
                'email' => $request->input('email', $authUser->email),
                'phone' => $request->input('phone', $authUser->phone),
            ]);

            $authUser->profile_path = $authUser->profile_path
                ? url('storage/' . $authUser->profile_path)
                : null;

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully.',
                'data' => $authUser,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * function to delete customer address and it will not revoke again , not available soft delete
     * @param $id
     * @return json
     */
    public function deleteAddress($id){
        try{
            $authUser = Auth::user();
            $address = CustomerAddress::where('id', $id)->where('customer_id',$authUser->id )->first();
            if($address){
                $address->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Address deleted successfully',
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Address not found',
                ], 404);
            }
        }catch (\Exception $exception){
            Log::error("Error while delete customer address :: ".$exception->getMessage().". Line :: ".$exception->getLine() );
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}
