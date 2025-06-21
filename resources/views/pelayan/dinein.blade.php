<!-- dine_in.blade.php -->
@extends('pelayan.layout.app')

@section('title', 'Pesanan Dine-in')

@section('content')
<div class="container mt-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #107672;">
            <i class="bi bi-cup-hot text-white fs-5"></i>
        </div>
        <div>
            <h4 class="mb-0">Kelola Semua Pesanan Dine-in</h4>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #107672;">
        <div class="card-header text-white" style="background-color: #107672;">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-funnel"></i>
                Filter & Pencarian
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pelayan.dinein') }}" class="d-flex flex-wrap gap-3">
                <div class="flex-grow-1">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control py-2" placeholder="Cari (Nama/Kode/Meja)">
                </div>
                <div class="flex-grow-1">
                    <select name="filter" class="form-select py-2" onchange="this.form.submit()">
                        <option value="">-- Filter Status Dine_in --</option>
                        <option value="active_order" {{ request('filter') == 'active_order' ? 'selected' : '' }}>Aktif</option>
                        <option value="selesai" {{ request('filter') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ request('filter') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-teal py-2 d-flex align-items-center gap-2">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    @if(request('search') || request('filter'))
                        <a href="{{ route('pelayan.dinein') }}" class="btn btn-outline-teal py-2 d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-left: 4px solid #107672;">
        <div class="card-header text-white" style="background-color: #107672;">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-table"></i>
                Daftar Pesanan Dine-in
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr style="background-color: #e0f2f1;">
                            <th>Kode Order/Reservasi</th>
                            <th>Nama Pelanggan</th>
                            <th>Meja</th>
                            <th>Jumlah Tamu</th>
                            <th>Waktu Kedatangan/Pesan</th>
                            <th>Status Pembayaran</th>
                            <th class="text-center">Detail Pembayaran</th>
                            <th class="text-center">Detail Menu</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dineInReservations as $item)
                            <tr>
                                <td>{{ $item->kode_reservasi ?? '-' }}</td>
                                <td>{{ $item->nama_pelanggan ?? $item->pengguna?->nama ?? '-' }}</td>
                                <td>
                                    @php
                                        // Cek apakah relasi meja tersedia
                                        $mejaList = $item->meja ?? collect();

                                        if ($mejaList->isEmpty() && $item->combined_tables) {
                                            $decoded = @json_decode($item->combined_tables, true);
                                            $tableIds = is_array($decoded) ? $decoded : [];
                                            $mejaList = \App\Models\Meja::whereIn('id', $tableIds)->get();
                                        }
                                    @endphp

                                    @if($mejaList->isNotEmpty())
                                        @foreach($mejaList as $mejaObj)
                                            <span class="badge bg-teal-light text-teal-dark rounded-pill mb-1">
                                                {{ $mejaObj->nomor_meja }} ({{ $mejaObj->area }})
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item->jumlah_tamu ?? '-' }}</td>
                                <td>
                                    {{
                                        (
                                            $item->waktu_kedatangan
                                                ? \Carbon\Carbon::parse($item->waktu_kedatangan)
                                                : $item->created_at
                                        )
                                        ->timezone('Asia/Jakarta')
                                        ->translatedFormat('d M Y H:i')
                                    }}
                                </td>
                                <td>
                                    @if($item->status === 'dibatalkan')
                                        <span class="badge bg-danger">Dibatalkan</span>
                                    @elseif($item->status === 'paid' || $item->status === 'selesai' || $item->payment_method === 'paid')
                                        <span class="badge bg-success">Lunas</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('pelayan.order.summary', ['reservasi_id' => $item->id, 'from' => 'dinein']) }}"
                                       class="btn btn-sm btn-outline-teal d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-receipt"></i> Detail
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('pelayan.reservasi.detail', ['id' => $item->id, 'from' => 'dinein']) }}"
                                       class="btn btn-sm btn-outline-teal d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-card-checklist"></i> Detail
                                    </a>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('pelayan.reservasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center justify-content-center gap-1">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-table" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Tidak ada pesanan dine-in.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $dineInReservations->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
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
    .table-hover tbody tr:hover {
        background-color: rgba(16, 118, 114, 0.05);
    }
    .badge.bg-teal-light {
        background-color: #e0f2f1;
        color: #107672;
    }
</style>
@endsection