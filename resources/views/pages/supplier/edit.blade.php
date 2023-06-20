@extends('layouts.dashboard')

@section('title', 'Edit Supplier')
@section('menu-title', 'Edit Supplier')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <form action="{{ route('supplier.update', $supplier->id) }}" method="post" class="mt-5">
        @csrf
        @method('PUT')
        <div class="mb-10">
            <label class="form-label" for="name">Nama supplier</label>
            <input name="name" type="text" class="form-control" placeholder="Masukan nama supplier" value="{{ $supplier->name }}" />
        </div>
        <div class="mb-10">
            <label class="form-label" for="name">No. Telp supplier</label>
            <input name="phone" type="number" class="form-control" placeholder="Masukan no.telp supplier" value="{{ $supplier->phone }}" />
        </div>
        <div class="mb-10">
            <label class="form-label" for="name">Alamat supplier</label>
            <textarea name="address" class="form-control" placeholder="Masukan alamat supplier">{{ $supplier->address }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">Update supplier</button>
    </form>
@endsection
