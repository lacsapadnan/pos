@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('menu-title', 'Dashboard')
@section('content')
    @include('components.alert')
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <h2>Selamat datang, {{ auth()->user()->name }}</h2>
        <h3>Cabang {{ auth()->user()->warehouse->name }}</h3>
    </div>
@endsection
