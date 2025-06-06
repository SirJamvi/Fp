@extends('pelayan.layout.app')

@section('title', $title)

@section('content')
<div class="container mt-4">
    <h4><i class="bi bi-info-circle-fill text-primary"></i> {{ $title }}</h4>

    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Detail Pesanan:</label>
        <div class="col-sm-10">
            <textarea class="form-control" rows="4" readonly>@foreach ($orders as $order)
{{ $order->menu->name }} ({{ $order->quantity }}x),
@endforeach
            </textarea>
        </div>
    </div>

    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Informasi Meja:</label>
        <div class="col-sm-10">
            <textarea class="form-control" rows="3" readonly>@php
    // Ambil nilai raw combined_tables
    $rawCombined = $reservasi->combined_tables;

    // Pastikan kita punya array: bila sudah array, pakai langsung; kalau JSON string, decode; kalau gagal, kosongkan
    if (is_array($rawCombined)) {
        $tableIds = $rawCombined;
    } else {
        $decoded = @json_decode($rawCombined, true);
        $tableIds = is_array($decoded) ? $decoded : [];
    }

    // Tarik data Meja berdasarkan ID yang ada di combined_tables
    $mejas = \App\Models\Meja::whereIn('id', $tableIds)->get();
@endphp

@if(count($mejas) > 0)
    @foreach($mejas as $mejaObj)
{{ $mejaObj->nomor_meja }} – {{ $mejaObj->area }}@if(!$loop->last)
@endif
    @endforeach
@else
    -
@endif
            </textarea>
        </div>
    </div>

    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Konfirmasi Kehadiran:</label>
        <div class="col-sm-10">
            <select class="form-select" disabled>
                <option {{ $reservasi->kehadiran_status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                <option {{ $reservasi->kehadiran_status === 'belum_dikonfirmasi' ? 'selected' : '' }}>Belum Dikonfirmasi</option>
                <option {{ $reservasi->kehadiran_status === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
            </select>
        </div>
    </div>

    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Total Harga:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" value="Rp. {{ number_format($totalHarga, 0, ',', '.') }}" readonly>
        </div>
    </div>

    <div class="d-flex gap-2">
        @php
            $from = request('from');
            $backRoute = $from === 'dinein'
                ? route('pelayan.dinein')
                : route('pelayan.reservasi');
        @endphp

        <a href="{{ $backRoute }}" class="btn btn-secondary">Kembali</a>
    </div>

</div>
@endsection
