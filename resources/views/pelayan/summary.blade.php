@extends('pelayan.layout.app')

@section('title', $title ?? 'Ringkasan Pesanan')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f0f2f5; }
    .summary-container {
        max-width: 750px;
        margin: 2rem auto;
        background-color: #fff;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    .summary-header h2 {
        color: #343a40;
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1.5rem; margin-bottom: 1.5rem; }
    .info-grid p { margin-bottom: 0.25rem; font-size: 0.95rem; }
    .info-grid strong { color: #495057; min-width: 120px; display: inline-block;}
    .items-table th { background-color: #f8f9fa; }
    .item-notes { font-size: 0.85em; color: #6c757d; padding-left: 1.5rem !important; }
    .grand-total-value { font-size: 1.75rem; color: #198754; }
</style>
@endpush


@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4><i class="bi bi-journals me-2"></i>Ringkasan Pesanan #{{ $reservasi->kode_reservasi }}</h4>
        </div>
        <div class="card-body">
            {{-- Informasi Umum --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>ID Order:</strong> {{ $reservasi->kode_reservasi }}</p>
                    <p><strong>Pelanggan:</strong> {{ $reservasi->nama_pelanggan ?? 'N/A' }}</p>
                    <p><strong>Waktu Pesan:</strong> {{ \Carbon\Carbon::parse($reservasi->waktu_kedatangan ?? $reservasi->created_at)->translatedFormat('l, d M Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>No. Meja:</strong> 
                        @if($reservasi->meja)
                            {{ $reservasi->meja->nomor_meja }} ({{ $reservasi->meja->area }})
                        @else
                            N/A
                        @endif
                    </p>
                    <p><strong>Pelayan:</strong> {{ $reservasi->staff?->nama ?? 'N/A' }}</p>
                </div>
            </div>

            <hr>

            {{-- Detail Pembayaran --}}
            <h5>Detail Pembayaran:</h5>
            <div class="row mb-3">
                <div class="col-md-4">
                    <p><strong>Metode Pembayaran:</strong>
                        @switch($reservasi->payment_method)
                            @case('qris')
                                <span class="badge bg-primary">QRIS</span>
                                @break

                            @case('tunai')
                                <span class="badge bg-warning">Tunai</span>
                                @break
                        @endswitch
                    </p>
                </div>

                <div class="col-md-4">
                    <p><strong>Status Pembayaran:</strong>
                        @if($reservasi->status === 'dibatalkan')
                            <span class="badge bg-danger">Dibatalkan</span>
                        @elseif($reservasi->payment_status === 'paid' || $reservasi->status === 'selesai')
                            <span class="badge bg-success">LUNAS</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </p>
                </div>

                <div class="col-md-4">
                    <p><strong>Waktu Pembayaran:</strong>
                        {{-- Anda bisa menyimpan timestamp pembayaran di kolom terpisah, misal 'waktu_selesai' atau 'paid_at' --}}
                        {{ 
                            $reservasi->waktu_selesai 
                                ? \Carbon\Carbon::parse($reservasi->waktu_selesai)->translatedFormat('l, d M Y H:i') 
                                : 'N/A' 
                        }}
                    </p>
                </div>
            </div>

            <hr>

            {{-- Detail Item Pesanan --}}
            <h5>Detail Item Pesanan:</h5>
            <table class="table table-bordered mt-2">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Menu</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-end" style="width: 20%;">Harga Satuan</th>
                        <th class="text-end" style="width: 20%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservasi->orders as $index => $order)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $order->menu->name }}</td>
                            <td class="text-center">{{ $order->quantity }}</td>
                            <td class="text-end">Rp {{ number_format($order->price_at_order, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Total --}}
            <div class="row mt-3">
                <div class="col-md-8"></div>
                <div class="col-md-4 text-end">
                    <h5><strong>Total Keseluruhan:</strong> Rp {{ number_format($reservasi->total_bill, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('pelayan.dinein') }}" class="btn btn-secondary">Kembali ke Daftar Dine-in</a>
        </div>
    </div>
</div>
@endsection