@extends('errors.layout')

@section('title', '500 - Kesalahan Server')
@section('code', '500')
@section('message', 'Terjadi Kesalahan di Server')
@section('description', 'Maaf, server sedang mengalami masalah. Silakan coba lagi dalam beberapa saat.')

@section('icon')
<flux:icon.server class="w-24 h-24 text-red-500/80 mb-6" />
@endsection
