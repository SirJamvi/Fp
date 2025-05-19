@extends('pelayan.layout.app')

@section('title', 'Daftar Reservasi')

@section('content')
<div class="container mt-4">
    <h2>Daftar Reservasi & Pesanan Dine-in</h2>

    <form method="GET" action="{{ route('pelayan.reservasi') }}" class="d-flex mb-3 gap-2">
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

        <button type="submit" class="btn btn-primary">Cari/Filter</button>

        @if(request('search') || request('filter'))
            <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">Reset Filter</a>
        @endif
    </form>

    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('pelayan.scanqr') }}" class="btn btn-success">Konfirmasi</a>
    </div>

    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Kode Order/Reservasi</th>
                <th>Sumber</th>
                <th>Nama Pelanggan</th>
                <th>Meja</th>
                <th>Jumlah Tamu</th>
                <th>Waktu Kedatangan/Pesan</th>
                <th>Status Reservasi</th>
                <th>Status Kehadiran</th>
                <th>Status Pembayaran</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reservasi as $item)
                <tr>
                    <td>{{ $item->kode_reservasi }}</td>
                    <td>
                        @php
                            $statusHadir = $item->status === 'selesai' ? 'Hadir' : 'Tidak Hadir';
                            $warnaHadir = $item->status === 'selesai' ? 'success' : 'danger';
                        @endphp
                        <span class="text-{{ $warnaHadir }}">{{ $statusHadir }}</span>
                        @if($item->source === 'dine_in')
                            <span class="badge bg-info"><i class="bi bi-shop me-1"></i> Dine-in</span>
                        @elseif($item->source === 'online')
                            <span class="badge bg-success"><i class="bi bi-globe me-1"></i> Online</span>
                        @else
                            <span class="badge bg-secondary">Unknown</span>
                        @endif
                    </td>
                    <td>{{ $item->nama_pelanggan ?? ($item->pengguna->name ?? '-') }}</td>
                    <td>{{ $item->meja->nomor_meja ?? '-' }} ({{ $item->meja->area ?? '-' }})</td>
                    <td>{{ $item->jumlah_tamu ?? '-' }}</td>
                    <td>
                        @if($item->waktu_kedatangan)
                            {{ \Carbon\Carbon::parse($item->waktu_kedatangan)->translatedFormat('d M Y H:i') }}
                        @else
                            {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y H:i') }}
                        @endif
                    </td>
                    <td>
                        @php
                            $statusClass = match($item->status) {
                                'dipesan', 'confirmed' => 'primary',
                                'pending_arrival' => 'warning',
                                'active_order' => 'info',
                                'paid', 'selesai' => 'success',
                                'dibatalkan' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    <td>
                        @php
                            $kehadiranClass = match($item->kehadiran_status) {
                                'hadir' => 'success',
                                'tidak_hadir' => 'danger',
                                'belum_dikonfirmasi' => 'warning',
                                default => 'secondary',
                            };
                            $kehadiranText = ucfirst($item->kehadiran_status ?? 'N/A');
                        @endphp
                        <span class="badge bg-{{ $kehadiranClass }}">{{ $kehadiranText }}</span>
                    </td>
                    <td>
                        @if($item->status === 'paid')
                            <span class="badge bg-success">Lunas ({{ ucfirst($item->payment_method ?? '-') }})</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('pelayan.order.summary', $item->id) }}" class="btn btn-info btn-sm me-1" title="Lihat Ringkasan">
                            <i class="bi bi-receipt"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted">Tidak ada data reservasi atau pesanan dine-in.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $reservasi->withQueryString()->links() }}
    </div>
</div>
@endsection
