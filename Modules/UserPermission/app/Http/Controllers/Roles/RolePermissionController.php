<?php

namespace Modules\UserPermission\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Exception;
use Illuminate\Support\Facades\File;


class RolePermissionController extends Controller
{
    /**
     * Show the form for assigning permissions to roles.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $authUser = Auth::user();
            $all_roles = Role::pluck('name')->toArray();

            $json = File::get(base_path('public/permission_acl.json'));
            $modules = collect(json_decode($json, true)['modules'])->pluck('label')->toArray();

            $rolesWithPermissions = Role::whereHas('permissions')->pluck('id')->toArray();

            $unassignedRoles = Role::whereNotIn('id', $rolesWithPermissions)
                ->where('created_by', auth()->id())
                ->whereNull('deleted_at')
                ->get();

            $roles = Role::with('permissions')->get();
            $permissions = Permission::all()->groupBy('module');


            return view('userpermission::Role.assign-permissions', compact(
                'roles', 'permissions', 'unassignedRoles', 'modules', 'all_roles', 'authUser'
            ));
        } catch (Exception $e) {
            Log::error('Error fetching roles or permissions: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to retrieve roles or permissions. Please try again.');
        }
    }

    /**
     * Assign permissions to the role.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'role_type' => 'required|in:1,2',
        ]);
        try {
            $role = Role::findOrFail($validated['role_id']);
            $roleName = $role->name;

            $role->role_type = $validated['role_type'];
            $role->save();

            if (isset($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            }

            return redirect()->back()->with('success', "Permissions assigned successfully to role '{$roleName}'.");
        } catch (Exception $e) {
            return redirect()->back()->withErrors('Failed to assign permissions. Please try again.')->withInput();
        }
    }

    /**
     * Assign permissions to a specific role.
     *
     * @param \Illuminate\Http\Request $request Request containing role_id and permissions.
     * @return \Illuminate\Http\RedirectResponse Redirects back with a status message.
     */
    public function assignUser(Request $request): RedirectResponse
    {
        // Validate input
        $validatedData = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            // Fetch the role
            $role = Role::findOrFail($validatedData['role_id']);

            // Assign permissions if provided
            if (!empty($validatedData['permissions'])) {
                $permissionNames = Permission::whereIn('id', $validatedData['permissions'])->pluck('name')->toArray();
                $role->givePermissionTo($permissionNames);
            }

            return redirect()->back()->with('success', 'Permissions assigned successfully to role "' . $role->label . '".');
        } catch (Exception $e) {
            Log::error('Error assigning permissions: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to assign permissions. Please try again.')->withInput();
        }
    }


    /**
     * Show the form to edit permissions for a specific role.
     *
     * @param  int  $roleId
     * @return \Illuminate\View\View
     */
    public function edit(int $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            $permissions = Permission::all()->groupBy('module');
            $rolePermissions = $role->permissions->pluck('id')->toArray();
            return view('userpermission::Role.edit-permission', compact('role', 'permissions', 'rolePermissions'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors('Failed to retrieve role or permissions. Please try again.');
        }
    }

    /**
     * Update the permissions assigned to the role.
     *
     * @param  Request  $request
     * @param  int  $roleId
     * @return RedirectResponse
     */
    public function update(Request $request, int $roleId): RedirectResponse
    {
        $validatedData = $request->validate([
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
            'role-type' => 'required|in:1,2'
        ]);
        try {
            $role = Role::findOrFail($roleId);
            $role->role_type = $validatedData['role-type'];
            $role->save();

            if (array_key_exists('permissions', $validatedData)) {
                $permissionNames = Permission::whereIn('id', $validatedData['permissions'])->pluck('name')->toArray();
                $role->syncPermissions($permissionNames);
            } else {
                $role->syncPermissions([]);
            }

            return redirect()->route('roles.assign-permissions.index')
                ->with('success', 'Permissions and role type updated successfully for role ' . $role->label);
        } catch (Exception $e) {
            Log::error('Error updating permissions and role type for role ' . $role->label . ': ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to update permissions and role type for role: ' . $role->label . '. Please try again.')
                ->withInput();
        }
    }

    /**
     * Update permissions for a specific role.
     *
     * @param \Illuminate\Http\Request $request Request containing updated permissions.
     * @param int $roleId The ID of the role to update.
     * @return \Illuminate\Http\RedirectResponse Redirects with a status message.
     */
    public function updateUser(Request $request, int $roleId): RedirectResponse
    {

        $validatedData = $request->validate([
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role = Role::findOrFail($roleId);

            if (array_key_exists('permissions', $validatedData)) {
                $permissionNames = Permission::whereIn('id', $validatedData['permissions'])->pluck('name')->toArray();
                $role->syncPermissions($permissionNames);
            } else {
                $role->syncPermissions([]);
            }

            return redirect()->route('roles.assign-permissions.index')
                ->with('success', 'Permissions updated successfully for role ' . $role->label);
        } catch (Exception $e) {
            Log::error('Error updating permissions for role ' . $role->label . ': ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to update permissions for role: ' . $role->label . '. Please try again.')
                ->withInput();
        }
    }

}
