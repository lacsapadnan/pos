@extends('layouts.dashboard')

@section('title', 'Edit Role')
@section('menu-title', 'Edit Role')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <form action="{{ route('role-permission.update', $role->id) }}" method="post" class="mt-5">
        @csrf
        @method('PUT')
        <div class="mb-10">
            <label class="form-label" for="name">Nama Role</label>
            <input name="name" type="text" class="form-control" placeholder="Masukan nama role" value="{{ $role->name }}" />
        </div>

        <div class="mb-10">
            <label class="form-label">Permissions</label>
            <div class="row gap-3 row-cols-2 row-cols-md-3 row-cols-lg-4 gap-3">
                @foreach($permissions as $permission)
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                {{ $permission->name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-success">Update role</button>
    </form>
@endsection
