@extends('errors.layout')

@section('title', 'Anda Sedang Offline')
@section('code', 'Offline')
@section('message', 'Koneksi Terputus')
@section('description', 'Sepertinya Anda sedang tidak terhubung ke internet. Coba periksa koneksi Wi-Fi atau data seluler Anda.')

@section('icon')
<flux:icon.wifi class="w-24 h-24 text-zinc-400 mb-6" />
@endsection

@section('action')
<button onclick="window.location.reload()" class="w-full py-3 px-4 bg-accent hover:opacity-90 text-white rounded-full font-medium transition-colors text-center shadow-md cursor-pointer">
    Coba Lagi
</button>
@endsection
