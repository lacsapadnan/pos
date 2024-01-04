<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'warehouse_id' => 'nullable|exists:warehouses,id'
        ], [
            'name.required' => 'Nama harus diisi!',
            'name.string' => 'Nama harus berupa string!',
            'name.max' => 'Nama maksimal 255 karakter!',
            'role.required' => 'Role harus diisi!',
            'role.string' => 'Role harus berupa string!',
            'role.max' => 'Role maksimal 255 karakter!',
            'email.required' => 'Email harus diisi!',
            'email.string' => 'Email harus berupa string!',
            'email.email' => 'Email harus berupa email!',
            'email.max' => 'Email maksimal 255 karakter!',
            'email.unique' => 'Email sudah terdaftar!',
            'password.required' => 'Password harus diisi!',
            'password.string' => 'Password harus berupa string!',
            'password.min' => 'Password minimal 8 karakter!',
            'warehouse_id.exists' => 'Cabang tidak ditemukan!',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $role = Role::where('id', $request->role)->first();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'warehouse_id' => $request->warehouse_id ?? null,
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
        $userPermissions = $user->permissions->pluck('id')->toArray();
        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        return view('pages.user.edit', compact('user', 'roles', 'permissions', 'userPermissions', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $validasi = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'warehouse_id' => 'nullable|exists:warehouses,id'
        ], [
            'name.required' => 'Nama harus diisi!',
            'name.string' => 'Nama harus berupa string!',
            'name.max' => 'Nama maksimal 255 karakter!',
            'role.required' => 'Role harus diisi!',
            'role.string' => 'Role harus berupa string!',
            'role.max' => 'Role maksimal 255 karakter!',
            'email.required' => 'Email harus diisi!',
            'email.string' => 'Email harus berupa string!',
            'email.email' => 'Email harus berupa email!',
            'email.max' => 'Email maksimal 255 karakter!',
            'email.unique' => 'Email sudah terdaftar!',
            'warehouse_id.exists' => 'Cabang tidak ditemukan!',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $role = Role::where('id', $request->role)->first();
        $user = User::where('id', $id)->first();
        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'warehouse_id' => $request->warehouse_id ?? $user->warehouse_id,
        ]);

        // Update the user roles or used current roles
        $user->roles()->sync($role);


        $permissions = $request->input('permissions', []);
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
