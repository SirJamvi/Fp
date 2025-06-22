<!-- bayar-sisa.blade.php -->
@extends('pelayan.layout.app')

@section('title', 'Bayar Sisa')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-credit-card-fill" style="color: #107672;"></i>
        Bayar Sisa Pembayaran
    </h2>

    {{-- ALERT SECTION --}}
    <div class="mb-4">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill"></i>
                {{ session('info') }}
            </div>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #107672;">
        <div class="card-header text-white" style="background-color: #107672;">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-receipt"></i>
                Detail Pembayaran
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Kode Reservasi:</span>
                        <strong>{{ $reservasi->kode_reservasi }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Sebelum Pajak:</span>
                        <strong>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pajak ({{ $pajakPersen ?? 10 }}%):</span>
                        <strong>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Termasuk Pajak:</span>
                        <strong>Rp {{ number_format($totalSetelahPajak, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Sudah Dibayar:</span>
                        <strong>Rp {{ number_format($totalDibayar, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Sisa Tagihan:</span>
                        <strong class="{{ $sisa <= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($sisa, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($sisa <= 0)
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            Pembayaran sudah lunas. Tidak ada sisa tagihan.
        </div>
        <a href="{{ route('pelayan.reservasi') }}" class="btn btn-outline-teal mt-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Reservasi
        </a>
    @else
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #107672;">
            <div class="card-header text-white" style="background-color: #107672;">
                <h5 class="mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-cash-coin"></i>
                    Form Pembayaran Sisa
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pelayan.reservasi.bayarSisa.post', $reservasi->id) }}" id="payment-form">
                    @csrf
                    <div class="mb-4">
                        <label for="jumlah_dibayar" class="form-label fw-medium d-flex align-items-center gap-2">
                            <i class="bi bi-currency-dollar"></i>
                            Jumlah Dibayar
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: #e0f2f1; color: #107672;">Rp</span>
                            <input 
                                type="number" 
                                name="jumlah_dibayar" 
                                id="jumlah_dibayar" 
                                class="form-control py-2" 
                                min="1" 
                                max="{{ $sisa }}" 
                                step="1" 
                                value="{{ old('jumlah_dibayar', $sisa) }}" 
                                required
                                style="height: 46px;">
                        </div>
                        <div class="form-text d-flex justify-content-between mt-1">
                            <small>Minimal: Rp 1</small>
                            <small>Maksimal: Rp {{ number_format($sisa, 0, ',', '.') }}</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="metode" class="form-label fw-medium d-flex align-items-center gap-2">
                            <i class="bi bi-wallet2"></i>
                            Metode Pembayaran
                        </label>
                        <div class="d-flex gap-3">
                            <div class="form-check flex-grow-1">
                                <input class="form-check-input" type="radio" name="metode" id="tunai" value="tunai" {{ old('metode') == 'tunai' ? 'checked' : '' }}>
                                <label class="form-check-label d-flex align-items-center gap-2 w-100 p-3 border rounded" for="tunai" style="cursor: pointer; border-color: #107672 !important;">
                                    <i class="bi bi-cash-coin fs-4" style="color: #107672;"></i>
                                    <div>
                                        <span class="d-block fw-medium">Tunai</span>
                                        <small class="text-muted">Bayar dengan uang tunai</small>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check flex-grow-1">
                                <input class="form-check-input" type="radio" name="metode" id="qris" value="qris" {{ old('metode') == 'qris' ? 'checked' : '' }}>
                                <label class="form-check-label d-flex align-items-center gap-2 w-100 p-3 border rounded" for="qris" style="cursor: pointer; border-color: #107672 !important;">
                                    <i class="bi bi-qr-code-scan fs-4" style="color: #107672;"></i>
                                    <div>
                                        <span class="d-block fw-medium">QRIS/TRANSFER</span>
                                        <small class="text-muted">Bayar dengan QR Code</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-teal flex-grow-1 py-2 fw-medium d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-check-circle"></i> Bayar Sisa
                        </button>
                        <a href="{{ route('pelayan.reservasi') }}" class="btn btn-outline-teal py-2 d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<style>
    .btn-teal {
        background-color: #107672;
        border-color: #107672;
        color: white;
    }
    .btn-teal:hover {
        background-color: #0d5e5a;
        border-color: #0d5e5a;
        color: white;
    }
    .btn-outline-teal {
        color: #107672;
        border-color: #107672;
    }
    .btn-outline-teal:hover {
        background-color: #107672;
        color: white;
    }
</style>
@endsection