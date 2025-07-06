<!-- summary.blade.php -->
@extends('pelayan.layout.app')

@section('title', $title ?? 'Ringkasan Pesanan')

@push('styles')
<style>
    :root {
        --teal: #107672;
        --teal-light: #e0f2f1;
        --teal-dark: #0d5e5a;
    }
    
    .summary-container {
        max-width: 850px;
        margin: 2rem auto;
        background-color: #fff;
        padding: 0;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid #eaeaea;
    }
    
    .summary-header {
        background: var(--teal);
        color: white;
        padding: 1.5rem 2rem;
        border-bottom: 4px solid rgba(255,255,255,0.15);
    }
    
    .summary-header h2 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .info-card {
        background-color: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
        border-left: 4px solid var(--teal);
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.25rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .info-value {
        font-size: 1.05rem;
        font-weight: 500;
        color: #1e293b;
    }
    
    .items-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .items-table th {
        background-color: var(--teal-light);
        padding: 0.85rem 1.25rem;
        font-weight: 500;
        color: var(--teal-dark);
        border-bottom: 2px solid #e2e8f0;
    }
    
    .items-table td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .items-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .items-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .payment-badge {
        padding: 0.35rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        background-color: var(--teal-light);
        color: var(--teal-dark);
    }
    
    .grand-total {
        background-color: var(--teal-light);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-top: 1.5rem;
        border: 1px solid #e0f2fe;
        border-left: 4px solid var(--teal);
    }
    
    .footer-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 1.5rem;
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 500;
    }
    
    .btn-teal {
        background-color: var(--teal);
        border-color: var(--teal);
        color: white;
    }
    .btn-teal:hover {
        background-color: var(--teal-dark);
        border-color: var(--teal-dark);
        color: white;
    }
    .btn-outline-teal {
        color: var(--teal);
        border-color: var(--teal);
    }
    .btn-outline-teal:hover {
        background-color: var(--teal);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="summary-container">
    <div class="summary-header">
        <h2>
            <i class="bi bi-journals"></i>
            Ringkasan Pesanan #{{ $reservasi->kode_reservasi }}
        </h2>
    </div>
    
    <div class="p-4">
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-tag"></i> ID Order
                    </span>
                    <span class="info-value">{{ $reservasi->kode_reservasi }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-person"></i> Pelanggan
                    </span>
                    <span class="info-value">{{ $reservasi->nama_pelanggan ?? 'N/A' }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-calendar-event"></i> Waktu Pesan
                    </span>
                    <span class="info-value">
                        {{ \Carbon\Carbon::parse($reservasi->waktu_kedatangan ?? $reservasi->created_at)
                            ->translatedFormat('l, d M Y H:i') }}
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-table"></i> No. Meja
                    </span>
                    <span class="info-value">
                        @php $from = $from ?? 'reservasi'; @endphp
                        @if($from === 'reservasi')
                            @php $mejaReservasi = $reservasi->mejaReservasi ?? collect(); @endphp

                            @if($mejaReservasi->isNotEmpty())
                                @foreach($mejaReservasi as $mr)
                                    {{ $mr->meja->nomor_meja ?? '-' }} ({{ $mr->meja->area ?? '-' }})@if(!$loop->last), @endif
                                @endforeach
                            @else
                                N/A
                            @endif

                        @elseif($from === 'dinein')
                            {{-- Dine-in: pakai mejaUtama --}}
                            @if($reservasi->mejaUtama)
                                {{ $reservasi->mejaUtama->nomor_meja }} ({{ $reservasi->mejaUtama->area }})
                            @else
                                N/A
                            @endif
                        @else
                            N/A
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Detail Pembayaran --}}
        <h5 class="mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-credit-card" style="color: #107672;"></i>
            Detail Pembayaran
        </h5>
        
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="info-item">
                    <span class="info-label">Metode Pembayaran</span>
                    <span class="info-value">
                        @switch($reservasi->payment_method)
                            @case('qris')<span class="payment-badge">QRIS</span>@break
                            @case('tunai')<span class="payment-badge">Tunai</span>@break
                            @default<span class="text-muted">Tidak Diketahui</span>@break
                        @endswitch
                    </span>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="info-item">
                    <span class="info-label">Status Pembayaran</span>
                    <span class="info-value">
                        @if($reservasi->status === 'dibatalkan')
                            <span class="status-badge bg-danger">Dibatalkan</span>
                        @elseif(in_array($reservasi->status, ['paid','selesai']))
                            <span class="status-badge bg-success">LUNAS</span>
                        @else
                            <span class="status-badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </span>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="info-item">
                    <span class="info-label">Waktu Pembayaran</span>
                    <span class="info-value">
                        {{ $reservasi->waktu_selesai
                            ? \Carbon\Carbon::parse($reservasi->waktu_selesai)
                                ->translatedFormat('l, d M Y H:i')
                            : 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Detail Item --}}
        <h5 class="mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-list-check" style="color: #107672;"></i>
            Detail Item Pesanan
        </h5>
        
        <div class="table-responsive rounded border">
            <table class="table items-table">
                <thead>
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
                        <td>
                            <div class="fw-medium">{{ $item['nama_menu'] }}</div>
                            @if(!empty($item['catatan']))
                            <div class="text-muted small mt-1">
                                <i class="bi bi-pencil"></i> {{ $item['catatan'] }}
                            </div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-end">Rp {{ number_format($item['harga_satuan'],0,',','.') }}</td>
                        <td class="text-end fw-medium">Rp {{ number_format($item['subtotal'],0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grand-total">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fs-5 fw-medium">Total Keseluruhan:</span>
                <span class="fs-3 fw-bold" style="color: #107672;">
                    Rp {{ number_format($orderSummary['total_keseluruhan'],0,',','.') }}
                </span>
            </div>
        </div>
    </div>

   {{-- Footer tombol --}}
<div class="footer-buttons">
    @if($from === 'reservasi')
        {{-- Tampilkan tombol Bayar Sisa hanya untuk status pending_payment --}}
        @if($reservasi->status === 'pending_payment' && $reservasi->payment_status !== 'dibatalkan')
            <a href="{{ route('pelayan.reservasi.bayarSisa', $reservasi->id) }}"
               class="btn btn-teal d-flex align-items-center gap-2 py-2 px-3">
                <i class="bi bi-cash-coin"></i> Bayar Sisa
            </a>
        @endif
        
        <a href="{{ route('pelayan.reservasi') }}" class="btn btn-outline-teal d-flex align-items-center gap-2 py-2 px-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Reservasi
        </a>
    @else
        <a href="{{ route('pelayan.dinein') }}" class="btn btn-outline-teal d-flex align-items-center gap-2 py-2 px-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Dine-in
        </a>
    @endif
</div>
</div>
@endsection