@extends('user.layout.app')

@section('title', 'Bukti Pembayaran')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Bukti Pembayaran</h3>

    <p><strong>Nama:</strong> {{ $reservasi->pengguna->nama }}</p>
    <p><strong>No Meja:</strong> {{ $reservasi->meja->nomor_meja }}</p>
    <p><strong>Waktu Kedatangan:</strong> {{ $reservasi->waktu_kedatangan }}</p>
    <p><strong>Kode Reservasi:</strong> {{ $reservasi->kode_reservasi }}</p>

    <div class="mt-4 text-center">
        <p>Silakan tunjukkan QR Code ini saat datang:</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ route('pelayan.scanqr.proses', $reservasi->kode_reservasi) }}&size=150x150" alt="QR Code">

    <div class="text-center mt-4">
        <a href="/" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>
@endsection