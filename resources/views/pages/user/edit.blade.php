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
            <input name="email" type="email" class="form-control" placeholder="Masukan email user" value="{{ $user->email }}" />
        </div>
        <div class="mb-4">
            <label class="form-label" for="warehouse_id">Cabang</label>
            <select name="warehouse_id" class="form-select" aria-label="Select example">
                @forelse ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ $user->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
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
                    <option value="{{ $role->id }}" {{ $user->roles->first()->id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @empty
                    <option value="">Tidak ada role</option>
                @endforelse
            </select>
        </div>
        <div class="mb-10">
            <label class="form-label">Permissions</label>
            @php
                // Group permissions by the word after the prefix
                $groupedPermissions = [];

                foreach($permissions as $permission) {
                    // Split the name into the prefix and the remaining part (title)
                    $parts = explode(' ', $permission->name, 2); // Split into 2 parts: prefix and title
                    $prefix = $parts[0]; // The prefix (simpan, baca, update, hapus)
                    $title = isset($parts[1]) ? ucfirst($parts[1]) : ''; // The title part (e.g., 'karyawan')

                    // Group by title
                    if ($title) {
                        $groupedPermissions[$title][] = $permission;
                    } else {
                        $groupedPermissions['Others'][] = $permission;
                    }
                }
            @endphp

            <div class="row">
                @foreach($groupedPermissions as $title => $group)
                    @if(count($group) > 0)
                        <div class="col-12 col-md-6" style="margin-bottom: 48px"> <!-- 6 col on medium and larger screens -->
                            <h5>{{ $title }} Permissions</h5>
                            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gap-3">
                                @foreach($group as $permission)
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-success">Update user</button>
    </form>
@endsection
