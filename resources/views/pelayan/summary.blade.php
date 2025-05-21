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
<div class="container">
    <div class="summary-container">
        <div class="summary-header text-center">
            <h2><i class="bi bi-receipt-cutoff me-2"></i>{{ $title }}</h2>
        </div>

        @if(isset($orderSummary) && $orderSummary)
            <div class="info-grid mb-4">
                <p><strong>ID Order:</strong> <span>#{{ $orderSummary['kode_reservasi'] ?? $orderSummary['reservasi_id'] ?? 'N/A' }}</span></p>
                <p><strong>No. Meja:</strong> <span>{{ $orderSummary['nomor_meja'] ?? 'N/A' }} ({{ $orderSummary['area_meja'] ?? 'N/A' }})</span></p>
                <p><strong>Pelanggan:</strong> <span>{{ $orderSummary['nama_pelanggan'] ?? 'N/A' }}</span></p>
                <p><strong>Pelayan:</strong> <span>{{ $orderSummary['nama_pelayan'] ?? 'N/A' }}</span></p>
                <p><strong>Waktu Pesan:</strong> <span>{{ isset($orderSummary['waktu_pesan']) ? \Carbon\Carbon::parse($orderSummary['waktu_pesan'])->translatedFormat('l, d M Y H:i') : 'N/A' }}</span></p>
            </div>

           @php
                $combinedTables = is_array($orderSummary['combined_tables'] ?? null)
                    ? $orderSummary['combined_tables']
                    : [];
            @endphp



            {{-- Tampilkan jika meja gabungan lebih dari 1 --}}
            @if(is_array($combinedTables) && count($combinedTables) > 1)
            <div class="alert alert-info mb-4">
                <h5><i class="bi bi-table me-2"></i>Meja yang Digabungkan</h5>
                <ul class="mb-0">
                    @foreach($combinedTables as $meja)
                        <li>Meja {{ $meja['nomor_meja'] }} (Kapasitas: {{ $meja['kapasitas'] }})</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(isset($reservasi) && $reservasi->payment_method)
            <div class="payment-details mb-4">
                <h5 class="fw-semibold mb-3">Detail Pembayaran:</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Metode Pembayaran:</strong><br>
                            <span class="badge bg-primary">
                                {{ strtoupper($reservasi->payment_method) }}
                            </span>
                        </p>

                        @if($reservasi->payment_method === 'cash')
                        <p><strong>Jumlah Uang:</strong><br>
                            Rp {{ number_format($reservasi->amount_paid, 0, ',', '.') }}
                        </p>
                        <p><strong>Kembalian:</strong><br>
                            Rp {{ number_format($reservasi->change_given, 0, ',', '.') }}
                        </p>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <p><strong>Status Pembayaran:</strong><br>
                            @if($reservasi->status === 'paid')
                            <span class="badge bg-success">LUNAS</span>
                            @else
                            <span class="badge bg-warning text-dark">PENDING</span>
                            @endif
                        </p>

                        <p><strong>Waktu Pembayaran:</strong><br>
                             {{ isset($reservasi->waktu_selesai) ? \Carbon\Carbon::parse($reservasi->waktu_selesai)->translatedFormat('d M Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <h5 class="mb-3 fw-semibold">Detail Item Pesanan:</h5>
            <div class="table-responsive">
                <table class="table table-hover items-table">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Menu</th>
                            <th scope="col" class="text-center">Qty</th>
                            <th scope="col" class="text-end">Harga Satuan</th>
                            <th scope="col" class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orderSummary['items'] as $index => $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $item['nama_menu'] ?? 'N/A' }}
                                @if(!empty($item['catatan']))
                                    <div class="item-notes"><em><i class="bi bi-card-text me-1"></i> {{ $item['catatan'] }}</em></div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="text-end">Rp {{ number_format($item['harga_satuan'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Tidak ada item dalam pesanan ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold fs-5 border-0 pt-3">Total Keseluruhan:</td>
                            <td class="text-end fw-bold grand-total-value border-0 pt-3">Rp {{ number_format($orderSummary['total_keseluruhan'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-5 text-center">
                <a href="{{ route('pelayan.dashboard') }}" class="btn btn-primary btn-lg me-2">
                    <i class="bi bi-plus-circle-fill me-1"></i> Buat Pesanan Baru
                </a>
                <a href="{{ route('pelayan.reservasi') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-list-ul me-1"></i> Daftar Reservasi
                </a>
            </div>
        @else
            <div class="alert alert-warning text-center">Tidak ada detail pesanan untuk ditampilkan.</div>
            <div class="mt-4 text-center">
                <a href="{{ route('pelayan.dashboard') }}" class="btn btn-primary btn-lg">Buat Pesanan Baru</a>
            </div>
        @endif
    </div>
</div>
@endsection
