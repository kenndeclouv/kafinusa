@extends('errors.layout')

@section('title', '403 - Akses Ditolak')
@section('code', '403')
@section('message', 'Akses Ditolak')
@section('description', 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.')

@section('icon')
<flux:icon.lock-closed class="w-24 h-24 text-yellow-500/80 mb-6" />
@endsection
