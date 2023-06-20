@extends('layouts.dashboard')

@section('title', 'Edit Customer')
@section('menu-title', 'Edit Customer')

@section('content')
    <div class="mt-5 card">
        <div class="card-body">
            <form action="{{ route('customer.update', $customer->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="mb-10">
                    <label class="form-label" for="name">Nama Customer</label>
                    <input name="name" type="text" class="form-control" placeholder="Masukan nama customer"
                        value="{{ $customer->name }}" />
                </div>
                <div class="mb-10">
                    <label class="form-label" for="name">Deskripsi</label>
                    <input name="description" type="text" class="form-control"
                        placeholder="Masukan deskripsi customer" value="{{ $customer->description }}" />
                </div>
                <button type="submit" class="btn btn-success">Simpan</button>
            </form>
        </div>
    </div>
@endsection
