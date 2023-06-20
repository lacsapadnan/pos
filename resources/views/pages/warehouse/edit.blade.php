@extends('layouts.dashboard')

@section('title', 'Edit Cabang')
@section('menu-title', 'Edit Cabang')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <form action="{{ route('cabang.update', $warehouse->id) }}" method="post" class="mt-5">
        @csrf
        @method('PUT')
        <div class="mb-10">
            <label class="form-label" for="name">Nama cabang</label>
            <input name="name" type="text" class="form-control" placeholder="Masukan nama cabang" value="{{ $warehouse->name }}" />
        </div>
        <div class="mb-10">
            <label class="form-label" for="name">No. Telp cabang</label>
            <input name="phone" type="number" class="form-control" placeholder="Masukan no.telp cabang" value="{{ $warehouse->phone }}" />
        </div>
        <div class="mb-10">
            <label class="form-label" for="name">Alamat cabang</label>
            <textarea name="address" class="form-control" placeholder="Masukan alamat cabang">{{ $warehouse->address }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">Update Cabang</button>
    </form>
@endsection
