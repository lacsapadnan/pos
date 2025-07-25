@extends('layouts.dashboard')

@section('title', 'Edit User')
@section('menu-title', 'Edit User')

@push('addon-style')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
@include('components.alert')
<form action="{{ route('user.update', $user->id) }}" method="post" class="mt-5">
    @csrf
    @method('PUT')
    <div class="mb-4">
        <label class="form-label" for="name">Nama</label>
        <input name="name" type="text" class="form-control" placeholder="Masukan nama user" value="{{ $user->name }}" />
    </div>
    <div class="mb-4">
        <label class="form-label" for="email">Email</label>
        <input name="email" type="email" class="form-control" placeholder="Masukan email user"
            value="{{ $user->email }}" />
    </div>
    <div class="mb-4">
        <label class="form-label" for="warehouse_id">Cabang</label>
        <select name="warehouse_id" class="form-select" aria-label="Select example">
            @forelse ($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ $user->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{
                $warehouse->name }}</option>
            @empty
            <option value="">Tidak ada role</option>
            @endforelse
        </select>
    </div>
    {{-- role select --}}
    <div class="mb-10">
        <label class="form-label" for="role">Role</label>
        <select name="role" class="form-select" aria-label="Select example">
            @forelse ($roles as $role)
            <option value="{{ $role->id }}" {{ $user->roles->first()->id == $role->id ? 'selected' : '' }}>{{
                $role->name }}</option>
            @empty
            <option value="">Tidak ada role</option>
            @endforelse
        </select>
    </div>
    <div class="mb-10">
        <label class="form-label">Permissions</label>
        <div class="row g-3">
            @foreach($groupedPermissions as $group => $groupPermissions)
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 card-title">{{ $group }}</h6>
                    </div>
                    <div class="card-body">
                        @foreach($groupPermissions as $permission)
                        <div class="mb-2 form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                value="{{ $permission->name }}" id="permission_{{ $permission->id }}" {{
                                in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                {{ $permission->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <button type="submit" class="btn btn-success">Update user</button>
</form>
@endsection
