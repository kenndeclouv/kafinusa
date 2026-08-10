@extends('errors.layout')

@section('title', '404 - Halaman Tidak Ditemukan')
@section('code', '404')
@section('message', 'Halaman Tidak Ditemukan')
@section('description', 'Maaf, halaman yang Anda tuju mungkin telah dihapus, pindah, atau Anda salah memasukkan alamat.')

@section('icon')
<flux:icon.magnifying-glass class="w-24 h-24 text-accent/50 mb-6" />
@endsection
