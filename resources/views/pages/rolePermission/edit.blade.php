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
        <button type="submit" class="btn btn-success">Update role</button>
    </form>
@endsection
