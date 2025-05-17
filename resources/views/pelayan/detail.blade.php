@extends('pelayan.layout.app')

@section('title', $title)

@section('content')
<div class="container mt-4">
    <h4><i class="bi bi-info-circle-fill text-primary"></i> {{ $title }}</h4>

    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Detail Pesanan:</label>
        <div class="col-sm-10">
            <textarea class="form-control" rows="4" readonly>
@foreach ($orders as $order)
{{ $order->menu->name }} ({{ $order->quantity }}x),
@endforeach
            </textarea>
        </div>
    </div>

    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Informasi Meja:</label>
        <div class="col-sm-10">
            <textarea class="form-control" rows="3" readonly>
{{ $reservasi->meja->nomor_meja }} - {{ $reservasi->meja->area }}
            </textarea>
        </div>
    </div>

    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Konfirmasi Kehadiran:</label>
        <div class="col-sm-10">
            <select class="form-select" disabled>
                <option selected>Hadir</option> {{-- Ubah kalau ada logic absensi --}}
            </select>
        </div>
    </div>

    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Harga:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" value="Rp. {{ number_format($totalHarga, 0, ',', '.') }}" readonly>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="#" class="btn btn-primary">Kirim ke koki</a>
        <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
