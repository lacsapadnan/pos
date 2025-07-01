<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::orderBy('id', 'asc')->get();
        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        return view('pages.user.index', compact('roles', 'warehouses'));
    }

    public function data()
    {
        $users = User::with('roles', 'warehouse', 'permissions')->orderBy('id', 'asc')->get();
        return response()->json($users);
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
    public function store(UserStoreRequest $request)
    {
        $validated = $request->validated();

        $role = Role::where('id', $validated['role'])->first();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'warehouse_id' => $validated['warehouse_id'] ?? null,
        ]);

        $user->roles()->attach($role);

        return redirect()->back()->with('success', 'User berhasil ditambahkan!');
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
        $user = User::with('roles', 'permissions')->where('id', $id)->first();
        $roles = Role::orderBy('id', 'asc')->get();
        $permissions = Permission::all();

        // Group permissions by module/object name (second part after space)
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);

            // Special case: group "absen masuk keluar" with "rekap" permissions
            if (strpos($permission->name, 'absen') !== false || strpos($permission->name, 'rekap') !== false) {
                return 'Rekap';
            }

            // Special case: group "laporan" and "laba rugi" permissions together
            if (strpos($permission->name, 'laporan') !== false || strpos($permission->name, 'laba') !== false || strpos($permission->name, 'rugi') !== false) {
                return 'Laba';
            }

            if (strpos($permission->name, 'kas') !== false && strpos($permission->name, 'kasbon') === false) {
                return 'Kas';
            }

            return count($parts) > 1 ? ucfirst($parts[1]) : ucfirst($parts[0]);
        });

        $userPermissions = $user->permissions->pluck('id')->toArray();
        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        return view('pages.user.edit', compact('user', 'roles', 'groupedPermissions', 'userPermissions', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $validated = $request->validated();

        $role = Role::where('id', $validated['role'])->first();
        $user = User::where('id', $id)->first();
        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'warehouse_id' => $validated['warehouse_id'] ?? $user->warehouse_id,
        ]);

        // Update the user roles or used current roles
        $user->roles()->sync($role);

        $permissions = $validated['permissions'] ?? [];
        $user->syncPermissions($permissions);
        return redirect()->route('user.index')->with('success', 'User berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::where('id', $id)->first();

        if ($user) {
            // Delete related cashflows
            $user->cashflows()->delete();
            $user->sellReturs()->delete();

            // Detach roles and permissions
            $user->roles()->detach();
            $user->permissions()->detach();

            // Force delete the user (ignoring foreign key constraints)
            $user->forceDelete();

            return redirect()->back()->with('success', 'User berhasil dihapus!');
        }

        return redirect()->back()->with('error', 'User tidak ditemukan.');
    }



    public function password()
    {
        return view('pages.user.password');
    }

    public function passwordUpdate(UpdatePasswordRequest $request, string $id)
    {
        $data = $request->validated();
        $id = auth()->user()->id;

        $user = User::where('id', $id)->first();
        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('dashboard')->with('success', 'Password berhasil diubah!');
    }
}
