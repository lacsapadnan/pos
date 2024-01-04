@extends('layouts.dashboard')

@section('title', 'Update Password')
@section('menu-title', 'Update Password')


@section('content')
    <div class="mt-5 border-0 card card-p-0 card-flush">
        @include('components.alert')
        <div class="card-body">
            <form action="{{ route('newpassword.update', auth()->id()) }}" method="post">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="current_password">Password Lama</label>
                    <input name="current_password" type="password" class="form-control"
                        placeholder="Masukan password lama anda" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password Baru</label>
                    <input name="password" type="password" class="form-control" placeholder="Masukan password baru anda" />
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
@endsection
