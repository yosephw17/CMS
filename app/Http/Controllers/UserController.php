<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->get();
        $roles = Role::all();

        return response()->json([
            'roles' => $roles,
            'users' => $users,
        ]);
    }

    public function edit($id)
    {
        $user = User::find($id);
        $userRole = $user->roles->pluck('name', 'name')->all();

        return response()->json([
            'user' => $user,
            'userRole' => $userRole
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirmPassword|min:4',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        Log::info("Request payload:", $request->all());
        $user->syncRoles($request->input('roles'));

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|same:confirmPassword|min:4',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $updateData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
        }

        $user->update($updateData);
        $user->syncRoles($request->input('roles'));

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => $user,
        ]);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}