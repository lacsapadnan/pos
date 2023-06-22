<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::all();
        return view('pages.rolePermission.index', compact('permissions'));
    }

    public function data()
    {
        $rolePermissions = Role::with('permissions')->get();
        return response()->json($rolePermissions);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // save role & permission
        $role = Role::create(['name' => $request->input('name')]);
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        return redirect()->route('role-permission.index')->with('success', 'Role berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('pages.rolePermission.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        // Update the role name
        $role->name = $request->input('name');
        $role->save();

        // Update the role permissions
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        return redirect()->route('role-permission.index')->with('success', 'Role berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->route('role-permission.index')->with('success', 'Role berhasil dihapus');
    }
}
