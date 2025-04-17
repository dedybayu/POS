@extends('layouts.template')

@section('content')
    <!-- Default box -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hallo {{auth()->user()->nama}}, Apa Kabar</h3>

            <div class="card-tools"></div>
        </div>
        <div class="card-body">
            Selamat datang, ini halaman utama
        </div>
    </div>
@endsection