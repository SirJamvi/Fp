@extends('pelayan.layout.app')

@section('title', 'Bayar Sisa')

@section('content')
<div class="container mt-4">
    <h2>Bayar Sisa Pembayaran</h2>

    {{-- ALERT SECTION --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <div class="card mt-3">
        <div class="card-body">
            <h5>Kode Reservasi: <strong>{{ $reservasi->kode_reservasi }}</strong></h5>
            <p>Total Tagihan: <strong>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</strong></p>
            <p>Total Dibayar: <strong>Rp {{ number_format($totalDibayar, 0, ',', '.') }}</strong></p>
            <p>Sisa Tagihan: <strong class="text-danger">Rp {{ number_format($sisa, 0, ',', '.') }}</strong></p>
        </div>
    </div>

    <form method="POST" action="{{ route('pelayan.reservasi.bayarSisa.post', $reservasi->id) }}" class="mt-4" id="payment-form">
        @csrf
        <div class="mb-3">
            <label for="jumlah_dibayar" class="form-label">Jumlah Dibayar</label>
            <input type="number" name="jumlah_dibayar" id="jumlah_dibayar" class="form-control" 
                   min="1" step="1" max="{{ $sisa }}" value="{{ $sisa >= 1 ? $sisa : 1 }}" required>
            <small class="text-muted">Minimal: Rp 1 | Maksimal: Rp {{ number_format($sisa, 0, ',', '.') }}</small>
        </div>

        <div class="mb-3">
            <label for="metode" class="form-label">Metode Pembayaran</label>
            <select name="metode" id="metode" class="form-select" required>
                <option value="">Pilih Metode</option>
                <option value="tunai">Tunai</option>
                <option value="qris">QRIS</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Bayar Sisa</button>
        <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection