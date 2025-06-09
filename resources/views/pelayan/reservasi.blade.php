@extends('pelayan.layout.app')

@section('title', 'Daftar Reservasi')

@section('content')
<div class="container mt-4">
    <h2>Daftar Reservasi</h2>

    <form method="GET" action="{{ route('pelayan.reservasi') }}" class="d-flex mb-3 gap-2">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari (Nama/Kode/Meja)">
        <select name="filter" class="form-select" onchange="this.form.submit()">
            <!-- opsi filter -->
        </select>
        <button type="submit" class="btn btn-primary">Cari/Filter</button>
        @if(request('search') || request('filter'))
            <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">Reset Filter</a>
        @endif
    </form>

    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('pelayan.scanqr') }}" class="btn btn-success">Scan QR</a>
    </div>

    @if(request('payment') === 'success')
    <div class="alert alert-success">Pembayaran berhasil dan sudah terkonfirmasi.</div>
    @elseif(request('payment') === 'failed')
        <div class="alert alert-danger">Pembayaran gagal.</div>
    @endif


    <table class="table table-bordered table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Kode Order/Reservasi</th>
                <th>Nama Pelanggan</th>
                <th>Meja</th>
                <th>Jumlah Tamu</th>
                <th>Waktu Kedatangan/Pesan</th>
                <th>Status Reservasi</th>
                <th>Status Kehadiran</th>
                <th>Status Pembayaran</th>
                <th>Detail Pembayaran</th>
                <th>Detail Menu</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reservasi as $item)
                <tr>
                    <td>{{ $item->kode_reservasi }}</td>
                    <td>{{ $item->nama_pelanggan ?? $item->pengguna?->name ?? '-' }}</td>
                    <td>
                        @php
                            $mejaList = $item->meja ?? collect();
                            if ($mejaList->isEmpty() && $item->combined_tables) {
                                $decoded = json_decode($item->combined_tables, true) ?: [];
                                $mejaList = \App\Models\Meja::whereIn('id', $decoded)->get();
                            }
                        @endphp
                        @if($mejaList->isNotEmpty())
                            @foreach($mejaList as $mejaObj)
                                <span>{{ $mejaObj->nomor_meja }} ({{ $mejaObj->area }})</span>@if(!$loop->last)<br>@endif
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $item->jumlah_tamu ?? '-' }}</td>
                     <td>
                        {{
                            ($item->waktu_kedatangan
                                ? \Carbon\Carbon::parse($item->waktu_kedatangan)
                                : $item->created_at
                            )
                            ->timezone('Asia/Jakarta')
                            ->translatedFormat('l, d M Y H:i')
                        }}
                    </td>
                    <td>
                        <span class="badge bg-{{ match($item->status) {
                            'dipesan', 'confirmed' => 'primary',
                            'pending_arrival' => 'warning',
                            'active_order' => 'info',
                            'paid', 'selesai' => 'success',
                            'dibatalkan' => 'danger',
                            default => 'secondary',
                        } }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    <td>
                        @php
                            $kehadiranStatus = $item->kehadiran_status ?? 'N/A';
                            $kehadiranClass = match($kehadiranStatus) {
                                'hadir' => 'success',
                                'tidak_hadir' => 'danger',
                                'belum_dikonfirmasi' => 'warning',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $kehadiranClass }}">{{ ucfirst($kehadiranStatus) }}</span>
                    </td>
                    <td>
                        @if($item->status === 'paid')
                            <span class="badge bg-success">Lunas ({{ ucfirst($item->payment_method ?? '-') }})</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('pelayan.order.summary', ['reservasi_id' => $item->id, 'from' => 'reservasi']) }}" class="btn btn-info btn-sm" title="Order Summary">
                            <i class="bi bi-receipt"></i>
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('pelayan.reservasi.detail', ['id' => $item->id, 'from' => 'reservasi']) }}" class="btn btn-primary btn-sm" title="Detail Menu">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                    <td class="text-center">
                        <form action="{{ route('pelayan.reservasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">Tidak ada reservasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $reservasi->links() }}
    </div>
</div>
@endsection