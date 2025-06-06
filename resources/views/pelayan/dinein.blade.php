@extends('pelayan.layout.app')

@section('title', 'Pesanan Dine-in')

@section('content')
<div class="container mt-4">
    <h2>Manajemen Pesanan Dine-in</h2>

    <form method="GET" action="{{ route('pelayan.dinein') }}" class="d-flex mb-3 gap-2">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari (Nama/Kode/Meja)">

        <select name="filter" class="form-select" onchange="this.form.submit()">
            <option value="">Semua Status & Waktu</option>
            <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="upcoming" {{ request('filter') == 'upcoming' ? 'selected' : '' }}>Akan Datang</option>
            <option value="past_week" {{ request('filter') == 'past_week' ? 'selected' : '' }}>Seminggu Terakhir</option>
            <option value="active" {{ request('filter') == 'active' ? 'selected' : '' }}>Pesanan Aktif</option>
            <option value="paid" {{ request('filter') == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
            <option value="selesai" {{ request('filter') == 'selesai' ? 'selected' : '' }}>Selesai (Manual)</option>
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
                        {{ $item->meja->pluck('nomor_meja')->implode(', ') ?? '-' }}
                        @if($item->meja->isNotEmpty())
                            ({{ $item->meja->first()->area ?? '-' }}) {{-- Asumsi area sama untuk meja gabungan --}}
                        @endif
                    </td>
                    <td>{{ $item->jumlah_tamu ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->waktu_kedatangan ?? $item->created_at)->translatedFormat('d M Y H:i') }}</td>
                    <td>
                        {{-- Jika status “dibatalkan” --}}
                        @if($item->status === 'dibatalkan')
                            <span class="badge bg-danger">Dibatalkan</span>

                        {{-- Jika sudah lunas (paid atau selesai) --}}
                        @elseif($item->status === 'paid' || $item->status === 'selesai' || $item->payment_method === 'paid')
                            <span class="badge bg-success">Lunas</span>

                        {{-- Sisanya (Belum Lunas) --}}
                        @else
                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('pelayan.order.summary', ['reservasi_id' => $item->id, 'from' => 'dinein']) }}"
                           class="btn btn-info btn-sm">
                            <i class="bi bi-receipt"></i>
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('pelayan.reservasi.detail', ['id' => $item->id, 'from' => 'dinein']) }}"
                           class="btn btn-primary btn-sm">
                            Detail
                        </a>
                    </td>
                    <td class="text-center">
                    <form action="{{ route('pelayan.reservasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                </td>

                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Tidak ada pesanan dine-in.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $dineInReservations->withQueryString()->links() }}
    </div>
</div>
@endsection
