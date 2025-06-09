@extends('user.layout.app')

@section('title', 'Bukti Pembayaran')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Bukti Pembayaran</h3>

    <p><strong>Nama:</strong> {{ $reservasi->nama_pelanggan ?? $reservasi->pengguna->nama ?? '-' }}</p>
    <p><strong>No Meja:</strong> {{ $reservasi->meja->first()->nomor_meja ?? '-' }}</p>
    <p><strong>Waktu Kedatangan:</strong> {{ \Carbon\Carbon::parse($reservasi->waktu_kedatangan)->translatedFormat('d M Y H:i') }}</p>
    <p><strong>Kode Reservasi:</strong> {{ $reservasi->kode_reservasi }}</p>

    <div class="mt-4 text-center">
        <p>Silakan tunjukkan QR Code ini saat datang:</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode(route('pelayan.scanqr.proses', $reservasi->kode_reservasi)) }}&size=150x150" alt="QR Code">
    </div>

    <div class="text-center mt-4">
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>
@endsection
