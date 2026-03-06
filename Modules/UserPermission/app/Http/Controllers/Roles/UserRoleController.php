<?php

namespace Modules\UserPermission\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Models\User;
//use Illuminate\Container\Attributes\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\UserManagement\Models\UserVendor;
use Modules\UserPermission\Models\UserRolePermission;
use Spatie\Permission\Models\Role;
use Exception;

class UserRoleController extends Controller
{
    /**
     * Show the form for assigning roles to users.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $authUser = Auth::user();

            $users = User::all();
            $roles = Role::whereNull('deleted_at')->get();
            $roleUnassignedUsers = User::doesntHave('roles')->get();
            $allRoles = Role::whereNull('deleted_at')->pluck('name')->toArray();

            return view('userpermission::Roles-assign.assign', compact('users', 'roles', 'roleUnassignedUsers', 'allRoles'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors('Failed to retrieve users or roles. Please try again!!!.');
        }
    }

    /**
     * Store the role assignment to a user.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_name' => 'required|exists:roles,name',
            ]);

            $user = User::findOrFail($validatedData['user_id']);
            $role = Role::where('name', $validatedData['role_name'])->first();

            if (!$role) {
                return redirect()->back()->withErrors('Role not found.');
            }

            if ($user->roles()->exists()) {
                return redirect()->back()->withErrors('This user already has a role assigned. Please edit the existing role.');
            }

            $user->assignRole($role);

            UserRolePermission::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'user_name' => $user->name,
            ]);

            return redirect()->back()->with('success', 'Role "' . $role->label . '" assigned to user "' . $user->name . '" successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors('Failed to assign role. Please try again!!!')->withInput();
        }
    }

    /**
     * Show the form for editing a user's roles.
     *
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function edit(int $userId)
    {
        try {
            $authUser = Auth::user();
            $user = User::findOrFail($userId);
            $allRoles = Role::pluck('name')->toArray();

            if ($authUser->is_superadmin == 1) {
                $roles = Role::all();
            } else {
                $userVendor = UserVendor::where('user_id', $authUser->id)->first();

                if (!$userVendor) {
                    throw new Exception('No vendor associated with this user.');
                }

                $vendorId = $userVendor->vendor_id;

                $roles = Role::where(function ($query) use ($authUser) {
                    if ($authUser->is_superadmin) {
                        $query->where('created_by', $authUser->id);
                    } else {
                        $query->where('created_by', $authUser->id);
                    }
                })->get();
            }

            return view('userpermission::User.edit', compact('user', 'roles','allRoles'));
        } catch (Exception $e) {
            Log::error('Error fetching user or roles: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to retrieve user or roles. Please try again.');
        }
    }

    /**
     * Update the roles assigned to the user.
     *
     * @param Request $request
     * @param int $userId
     * @return RedirectResponse
     */
    public function update(Request $request, int $userId): RedirectResponse
    {
        $validatedData = $request->validate([
            'role_name' => 'required|exists:roles,name',
        ]);

        try {
            $user = User::findOrFail($userId);
            $role = Role::where('name', $validatedData['role_name'])->first();

            if (!$role) {
                return redirect()->back()->withErrors('Role not found.');
            }

            if ($user->hasRole($role->name)) {
                return redirect()->back()->withErrors('This user already has this role assigned.');
            }

            $user->syncRoles([$role->name]);

            UserRolePermission::updateOrCreate(
                ['user_id' => $user->id, 'role_id' => $role->id],
                ['user_name' => $user->name]
            );

            return redirect()->back()->with('success', 'Role "' . $role->label . '" updated successfully');
        } catch (Exception $e) {
            Log::error('Error updating user role: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to update role. Please try again.')->withInput();
        }
    }

    /**
     * Delete the specified user.
     *
     * @param int $userId The ID of the user to delete.
     * @return \Illuminate\Http\RedirectResponse Redirects back with a status message.
     */
    public function deleteUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->delete();
            DB::commit();
            return redirect()->back()->with('success', 'User "' . $user->name . '" deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user [ID: ' . $userId . ']: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to delete the user. Please try again!');
        }
    }

}
