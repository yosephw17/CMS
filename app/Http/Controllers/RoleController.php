<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
 public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'permissions' => 'nullable|array',
        'permissions.*' => 'exists:permissions,id', // Validate permission IDs
    ]);

    $role = Role::create([
        'name' => $request->name,
        'guard_name' => 'sanctum' // Explicitly set, though model defaults this
    ]);

    if ($request->has('permissions')) {
        $role->syncPermissions($request->permissions);
    }

    return response()->json([
        'success' => true,
        'message' => 'Role created successfully.',
        'data' => $role->load('permissions'),
    ]);
}

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        return response()->json([
            'success' => true,
            'data' => $role,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'permissions' => 'nullable|array',
        'permissions.*' => 'exists:permissions,id', // each permission ID must be valid
    ]);

    // Update role name
    $role->update(['name' => $request->name]);

    // Sync permissions
    if ($request->has('permissions')) {
        $role->permissions()->sync($request->permissions);
    }

    return response()->json([
        'success' => true,
        'message' => 'Role updated successfully.',
        'data' => $role->load('permissions'), // include permissions in the response
    ]);
}


    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }
}
