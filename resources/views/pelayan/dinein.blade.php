@extends('pelayan.layout.app')

@section('title', 'Pesanan Dine-in')

@section('content')
<div class="container mt-4">
    <h2>Manajemen Pesanan Dine-in</h2>

    <form method="GET" action="{{ route('pelayan.dinein') }}" class="d-flex mb-3 gap-2">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari (Nama/Kode/Meja)">

        <select name="filter" class="form-select" onchange="this.form.submit()">
            <option value="">Filter Status Dine_in</option>
            <option value="active_order" {{ request('filter') == 'active_order' ? 'selected' : '' }}>Aktif</option>
            <option value="selesai" {{ request('filter') == 'selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="dibatalkan" {{ request('filter') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
        </select>

        <button type="submit" class="btn btn-primary">Cari</button>
        @if(request('search') || request('filter'))
            <a href="{{ route('pelayan.dinein') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>

    <table class="table table-bordered table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Kode Order/Reservasi</th>
                <th>Nama Pelanggan</th>
                <th>Meja</th>
                <th>Jumlah Tamu</th>
                <th>Waktu Kedatangan/Pesan</th>
                <th>Status Pembayaran</th>
                <th>Detail Pembayaran</th>
                <th>Detail Menu</th>
                <th>Aksi</th>
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
                                <span>{{ $mejaObj->nomor_meja }} ({{ $mejaObj->area }})</span>
                                @if(!$loop->last)
                                    <br>
                                @endif
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
                            ->translatedFormat('l, d M Y H:i')
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
                           class="btn btn-info btn-sm">
                            <i class="bi bi-receipt me-1"></i> Detail Pembayaran
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('pelayan.reservasi.detail', ['id' => $item->id, 'from' => 'dinein']) }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-card-checklist me-1"></i> Detail Menu
                        </a>
                    </td>
                    <td class="text-center">
                        <form action="{{ route('pelayan.reservasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Tidak ada pesanan dine-in.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $dineInReservations->withQueryString()->links() }}
    </div>
</div>
@endsection