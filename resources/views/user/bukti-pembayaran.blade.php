@extends('user.layout.app')

@section('title', 'Bukti Pembayaran')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Bukti Pembayaran</h3>

    <p><strong>Nama:</strong> {{ $reservasi->nama_pelanggan ?? $reservasi->pengguna->nama ?? '-' }}</p>

    @php
        // Ambil relasi meja (Collection)
        $mejaList = $reservasi->meja ?? collect();

        // Jika tidak ada lewat relasi, fallback ke combined_tables
        if ($mejaList->isEmpty() && $reservasi->combined_tables) {
            $ids = is_array($reservasi->combined_tables)
                    ? $reservasi->combined_tables
                    : json_decode($reservasi->combined_tables, true);
            $mejaList = \App\Models\Meja::whereIn('id', $ids)->get();
        }
    @endphp

    <p>
        <strong>No Meja:</strong>
        @if($mejaList->isNotEmpty())
            @foreach($mejaList as $meja)
                {{ $meja->nomor_meja }} ({{ $meja->area }})@if(!$loop->last), @endif
            @endforeach
        @else
            -
        @endif
    </p>

    <p>
        <strong>Waktu Kedatangan:</strong>
        {{ \Carbon\Carbon::parse($reservasi->waktu_kedatangan)->translatedFormat('d M Y H:i') }}
    </p>

    <p><strong>Kode Reservasi:</strong> {{ $reservasi->kode_reservasi }}</p>

    <div class="mt-4 text-center">
        <p>Silakan tunjukkan QR Code ini saat datang:</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ 
                urlencode(route('pelayan.scanqr.proses', $reservasi->kode_reservasi)) 
             }}&size=150x150" alt="QR Code">
    </div>

    <div class="text-center mt-4">
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>
@endsection
