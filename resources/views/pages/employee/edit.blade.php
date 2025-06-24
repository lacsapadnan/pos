@extends('layouts.dashboard')

@section('title', 'Edit Karyawan')
@section('menu-title', 'Edit Karyawan')

@push('addon-style')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
@include('components.alert')
<form action="{{ route('karyawan.update', $employee->id) }}" method="post" class="mt-5" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="mb-4">
        <label class="form-label" for="name">Nama</label>
        <input name="name" type="text" class="form-control" placeholder="Masukan nama karyawan"
            value="{{ $employee->name }}" />
    </div>
    <div class="mb-4">
        <label class="form-label" for="nickname">Nickname</label>
        <input name="nickname" type="text" class="form-control" placeholder="Masukan nickname karyawan"
            value="{{ $employee->nickname }}" />
    </div>
    <div class="mb-4">
        <label class="form-label" for="ktp">Foto KTP</label>
        <input name="ktp" type="file" class="form-control" accept="image/*" />
        <div class="form-text">Upload gambar KTP baru (JPG, PNG, GIF, max 2MB)</div>
        @if($employee->ktp)
        <div class="mt-2">
            <label class="form-label">Foto KTP Saat Ini:</label><br>
            <img src="{{ asset('storage/' . $employee->ktp) }}" alt="KTP" class="img-thumbnail"
                style="max-width: 200px;">
        </div>
        @endif
    </div>
    <div class="mb-4">
        <label class="form-label" for="phone">No. telp</label>
        <input name="phone" type="number" class="form-control" placeholder="Masukan No. telp karyawan"
            value="{{ $employee->phone }}" />
    </div>
    <div class="mb-4">
        <label class="form-label" for="warehouse_id">Cabang</label>
        <select name="warehouse_id" class="form-select" aria-label="Select example">
            @forelse ($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ $employee->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{
                $warehouse->name }}</option>
            @empty
            <option value="">Tidak ada cabang</option>
            @endforelse
        </select>
    </div>
    <div class="mb-4">
        <label class="form-label" for="user_id">User <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select" aria-label="Select user" required>
            <option value="">Pilih User</option>
            @forelse ($users as $user)
            <option value="{{ $user->id }}" {{ $employee->user_id == $user->id ? 'selected' : '' }}>
                {{ $user->name }} ({{ $user->email }})
            </option>
            @empty
            <option value="">Tidak ada user</option>
            @endforelse
        </select>
        <div class="form-text">User ini akan digunakan untuk absensi dan penggajian</div>
    </div>
    <button type="submit" class="btn btn-success">Update karyawan</button>
</form>
@endsection