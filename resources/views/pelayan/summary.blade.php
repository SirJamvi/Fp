@extends('pelayan.layout.app')

@section('title', $title ?? 'Ringkasan Pesanan')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f0f2f5; }
    .summary-container { max-width: 750px; margin: 2rem auto; background-color: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 6px 18px rgba(0,0,0,0.1); }
    .summary-header h2 { color: #343a40; border-bottom: 2px solid #0d6efd; padding-bottom: 1rem; margin-bottom: 1.5rem; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1.5rem; margin-bottom: 1.5rem; }
    .info-grid p { margin-bottom: 0.25rem; font-size: 0.95rem; }
    .info-grid strong { color: #495057; min-width: 120px; display: inline-block; }
    .items-table th { background-color: #f8f9fa; }
    .item-notes { font-size: 0.85em; color: #6c757d; padding-left: 1.5rem !important; }
    .grand-total-value { font-size: 1.75rem; color: #198754; }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm summary-container">
        <div class="card-header summary-header">
            <h2><i class="bi bi-journals me-2"></i> Ringkasan Pesanan #{{ $reservasi->kode_reservasi }}</h2>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>ID Order:</strong> {{ $reservasi->kode_reservasi }}</p>
                    <p><strong>Pelanggan:</strong> {{ $reservasi->nama_pelanggan ?? 'N/A' }}</p>
                    <p><strong>Waktu Pesan:</strong> {{ \Carbon\Carbon::parse($reservasi->waktu_kedatangan ?? $reservasi->created_at)->translatedFormat('l, d M Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>No. Meja:</strong>
                        @php
                            $combinedTables = $orderSummary['combined_tables'] ?? [];
                        @endphp
                        @if(count($combinedTables) > 0)
                            @foreach($combinedTables as $mejaData)
                                {{ $mejaData['nomor_meja'] }} ({{ $mejaData['area'] }})@if(!$loop->last), @endif
                            @endforeach
                        @else
                            N/A
                        @endif
                    </p>
                    <p><strong>Pelayan:</strong> {{ $reservasi->staffYangMembuat->name ?? (auth()->check() ? auth()->user()->name : 'N/A') }}</p>
                </div>
            </div>

            <hr>

            <h5>Detail Pembayaran:</h5>
            <div class="row mb-3">
                <div class="col-md-4">
                    <p><strong>Metode Pembayaran:</strong>
                        @switch($reservasi->payment_method)
                            @case('qris')<span class="badge bg-primary">QRIS</span>@break
                            @case('tunai')<span class="badge bg-warning">Tunai</span>@break
                            @default<span class="text-muted">Tidak Diketahui</span>@break
                        @endswitch
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Status Pembayaran:</strong>
                        @if($reservasi->status === 'dibatalkan')
                            <span class="badge bg-danger">Dibatalkan</span>
                        @elseif(in_array($reservasi->status, ['paid', 'selesai']))
                            <span class="badge bg-success">LUNAS</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Waktu Pembayaran:</strong> {{ $reservasi->waktu_selesai ? \Carbon\Carbon::parse($reservasi->waktu_selesai)->translatedFormat('l, d M Y H:i') : 'N/A' }}</p>
                </div>
            </div>

            <hr>

            <h5>Detail Item Pesanan:</h5>
            <table class="table table-bordered mt-2 items-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Menu</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderSummary['items'] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['nama_menu'] }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-end">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mt-3">
                <div class="col-md-8"></div>
                <div class="col-md-4 text-end grand-total-value">
                    <strong>Total Keseluruhan: Rp {{ number_format($orderSummary['total_keseluruhan'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            @php $from = $from ?? 'reservasi'; @endphp
            @if($from === 'reservasi')
                @if(!in_array($reservasi->status, ['paid', 'selesai']))
                    <a href="{{ route('pelayan.reservasi.bayarSisa', $reservasi->id) }}" class="btn btn-warning me-2">
                        <i class="bi bi-cash-coin"></i> Bayar Sisa
                    </a>
                @endif
                <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">Kembali ke Daftar Reservasi</a>
            @else
                <a href="{{ route('pelayan.dinein') }}" class="btn btn-secondary">Kembali ke Daftar Dine-in</a>
            @endif
        </div>
    </div>
</div>
@endsection