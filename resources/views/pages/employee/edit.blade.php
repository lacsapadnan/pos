@extends('layouts.dashboard')

@section('title', 'Edit Karyawan')
@section('menu-title', 'Edit Karyawan')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    @include('components.alert')
    <form action="{{ route('karyawan.update', $employee->id) }}" method="post" class="mt-5">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="form-label" for="name">Nama</label>
            <input name="name" type="text" class="form-control" placeholder="Masukan nama karyawan" value="{{ $employee->name }}" />
        </div>
        <div class="mb-4">
            <label class="form-label" for="email">Email</label>
            <input name="email" type="email" class="form-control" placeholder="Masukan email karyawan" value="{{ $employee->email }}" />
        </div>
        <div class="mb-4">
            <label class="form-label" for="phone">No. telp</label>
            <input name="phone" type="number" class="form-control" placeholder="Masukan No. telp karyawan" value="{{ $employee->phone }}" />
        </div>
        <div class="mb-4">
            <label class="form-label" for="warehouse_id">Cabang</label>
            <select name="warehouse_id" class="form-select" aria-label="Select example">
                @forelse ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ $employee->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                @empty
                    <option value="">Tidak ada role</option>
                @endforelse
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update karyawan</button>
    </form>
@endsection
