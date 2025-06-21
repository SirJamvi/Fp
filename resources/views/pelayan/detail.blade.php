<!-- detail.blade.php -->
@extends('pelayan.layout.app')

@section('title', $title)

@section('content')
<div class="container mt-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #107672;">
            <i class="bi bi-info-circle text-white fs-5"></i>
        </div>
        <div>
            <h4 class="mb-0">{{ $title }}</h4>
            <p class="text-muted mb-0">Detail informasi reservasi</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #107672;">
        <div class="card-header text-white" style="background-color: #107672;">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-list-check"></i>
                Detail Pesanan
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background-color: #e0f2f1;">
                            <th>#</th>
                            <th>Menu</th>
                            <th class="text-center">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $index => $order)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-medium">{{ $order->menu->name }}</div>
                                @if($order->notes)
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-pencil"></i> {{ $order->notes }}
                                </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill fs-6 px-3 py-1" style="background-color: #107672;">{{ $order->quantity }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #107672;">
        <div class="card-header text-white" style="background-color: #107672;">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-geo-alt"></i>
                Informasi Meja
            </h5>
        </div>
        <div class="card-body">
            @php $from = $from ?? 'reservasi'; @endphp
            @if($from === 'reservasi')
                {{-- Online reservation --}}
                @php $mejaReservasi = $reservasi->mejaReservasi ?? collect(); @endphp

                @if($mejaReservasi->isNotEmpty())
                <div class="d-flex flex-wrap gap-2">
                    @foreach($mejaReservasi as $mr)
                    <div class="border rounded p-3 text-center" style="min-width: 120px; border-color: #107672 !important;">
                        <div class="fs-1" style="color: #107672;">
                            <i class="bi bi-table"></i>
                        </div>
                        <div class="fw-medium">{{ $mr->meja->nomor_meja }}</div>
                        <div class="text-muted small">{{ $mr->meja->area }}</div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-exclamation-circle fs-1" style="color: #107672;"></i>
                    <p class="text-muted mt-2">Tidak ada informasi meja</p>
                </div>
                @endif
            @elseif($from === 'dinein')
                {{-- Dine-in --}}
                @if($reservasi->mejaUtama)
                <div class="d-flex flex-wrap gap-2">
                    <div class="border rounded p-3 text-center" style="min-width: 120px; border-color: #107672 !important;">
                        <div class="fs-1" style="color: #107672;">
                            <i class="bi bi-table"></i>
                        </div>
                        <div class="fw-medium">{{ $reservasi->mejaUtama->nomor_meja }}</div>
                        <div class="text-muted small">{{ $reservasi->mejaUtama->area }}</div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-exclamation-circle fs-1" style="color: #107672;"></i>
                    <p class="text-muted mt-2">Tidak ada informasi meja</p>
                </div>
                @endif
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #107672;">
                <div class="card-header text-white" style="background-color: #107672;">
                    <h5 class="mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-person-check"></i>
                        Konfirmasi Kehadiran
                    </h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if($reservasi->kehadiran_status === 'hadir')
                        <span class="badge rounded-pill px-3 py-2 fs-5" style="background-color: #107672;">
                            <i class="bi bi-check-circle me-1"></i> Hadir
                        </span>
                    @elseif($reservasi->kehadiran_status === 'belum_dikonfirmasi')
                        <span class="badge rounded-pill px-3 py-2 fs-5" style="background-color: #6c757d;">
                            <i class="bi bi-clock-history me-1"></i> Belum Dikonfirmasi
                        </span>
                    @else
                        <span class="badge rounded-pill px-3 py-2 fs-5" style="background-color: #dc3545;">
                            <i class="bi bi-x-circle me-1"></i> Tidak Hadir
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #107672;">
                <div class="card-header text-white" style="background-color: #107672;">
                    <h5 class="mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-currency-dollar"></i>
                        Total Harga
                    </h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="mb-0" style="color: #107672;">Rp. {{ number_format($totalHarga, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol Kembali --}}
    <div class="d-flex gap-2 mt-4">
        @php
            $backRoute = request('from') === 'dinein'
                ? route('pelayan.dinein')
                : route('pelayan.reservasi');
        @endphp
        <a href="{{ $backRoute }}" class="btn btn-outline-teal py-2 d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<style>
    .btn-outline-teal {
        color: #107672;
        border-color: #107672;
    }
    .btn-outline-teal:hover {
        background-color: #107672;
        color: white;
    }
    .card-header {
        border-radius: 0 !important;
    }
</style>
@endsection