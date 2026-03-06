<?php

namespace Modules\UserPermission\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\UserPermission\Models\Role;

class RoleController extends Controller
{
    /**
     * List all roles.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {

        try {
            $roles = Role::with('creator')->get();
            $all_roles = Role::pluck('name')->toArray();
            $users = User::all();

            return view('userpermission::Role.index', compact('roles', 'all_roles', 'users'));
        } catch (Exception $e) {
            Log::error('Error fetching roles: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to retrieve roles. Please try again.');
        }
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {

        $all_roles = Role::whereNull('deleted_at')->pluck('name')->toArray();

        $user = User::all();
        return view('userpermission::Role.create',compact('all_roles','user'));
    }

    /**
     * Store a newly created role in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:255',
        ]);

        try {
            $validatedData['created_by'] = Auth::id();
            $validatedData['guard_name'] = $validatedData['guard_name'] ?? 'web';
            $validatedData['label'] = $request->input('name');

            $role = Role::create($validatedData);

            return redirect()->route('roles.index')->with('success', $role->name . ' role created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors('Failed to create role. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param Role $role
     * @return \Illuminate\View\View
     */
    public function edit(Role $role)
    {
        $all_roles = Role::whereNull('deleted_at')->pluck('name')->toArray();
        return view('userpermission::Role.edit', compact('role','all_roles'));
    }

    /**
     * Update the specified role in storage.
     *
     * @param Request $request
     * @param Role $role
     * @return RedirectResponse
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'guard_name' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:255',
        ]);

        try {
            $validatedData['updated_by'] = Auth::id();
            $validatedData['guard_name'] = $validatedData['guard_name'] ?? 'web';
            $validatedData['label'] = $request->input('name');
            $role->update($validatedData);
            return redirect()->route('roles.index')->with('success', $role->label . ' role updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating role: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to update role. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified role from storage.
     *
     * @param Role $role
     * @return RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        try {
            $roleName = $role->name;
            $label = $role->label;
            $role->delete();
            return redirect()->route('roles.index')->with('success', $label . ' role deleted successfully.');
        } catch (Exception $e) {
            Log::error('Error deleting role: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to delete role. Please try again.');
        }
    }

}
