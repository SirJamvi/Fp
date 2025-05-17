@extends('pelayan.layout.app')

@section('title', 'Daftar Reservasi') {{-- Changed title --}}

@section('content')
<div class="container mt-4">
    <h2>Daftar Reservasi & Pesanan Dine-in</h2> {{-- Changed title here too --}}

    <form method="GET" action="{{ route('pelayan.reservasi') }}" class="d-flex mb-3 gap-2">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari (Nama/Kode/Meja)"> {{-- Updated placeholder --}}

        <select name="filter" class="form-select" onchange="this.form.submit()">
            <option value="">Semua Status & Waktu</option> {{-- Updated option text --}}
            {{-- Filter Waktu --}}
            <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Hari Ini</option> {{-- Added today filter --}}
            <option value="upcoming" {{ request('filter') == 'upcoming' ? 'selected' : '' }}>Akan Datang</option> {{-- Added upcoming filter --}}
            <option value="past_week" {{ request('filter') == 'past_week' ? 'selected' : '' }}>Seminggu Terakhir</option> {{-- Added past week filter --}}
            {{-- Filter Status Reservasi --}}
            <option value="active" {{ request('filter') == 'active' ? 'selected' : '' }}>Pesanan Aktif</option> {{-- Filter for active orders/reservations --}}
            <option value="paid" {{ request('filter') == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option> {{-- Filter for paid --}}
            <option value="selesai" {{ request('filter') == 'selesai' ? 'selected' : '' }}>Selesai (Manual)</option> {{-- Filter for manually completed --}}
             <option value="dibatalkan" {{ request('filter') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option> {{-- Filter for cancelled --}}
             {{-- Anda bisa tambahkan filter untuk source jika perlu --}}
             {{-- <option value="dine_in" {{ request('filter') == 'dine_in' ? 'selected' : '' }}>Dine-in Only</option> --}}
             {{-- <option value="online" {{ request('filter') == 'online' ? 'selected' : '' }}>Online Only</option> --}}
        </select>

        <button type="submit" class="btn btn-primary">Cari/Filter</button> {{-- Updated button text --}}

        @if(request('search') || request('filter'))
            <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">Reset Filter</a> {{-- Updated button text --}}
        @endif
    </form>

    <table class="table table-bordered table-striped table-hover"> {{-- Added some bootstrap table classes --}}
        <thead>
            <tr>
                <th>Kode Order/Reservasi</th>
                <th>Sumber</th> {{-- Kolom untuk sumber --}}
                <th>Nama Pelanggan</th>
                <th>Meja</th> {{-- Added Meja column --}}
                <th>Jumlah Tamu</th> {{-- Added Jumlah Tamu column --}}
                <th>Waktu Kedatangan/Pesan</th> {{-- Changed column header --}}
                <th>Status Reservasi</th> {{-- Changed column header --}}
                <th>Status Kehadiran</th> {{-- <--- Kolom Status Kehadiran --}}
                <th>Status Pembayaran</th>
                {{-- <th>Status Makanan</th> --}} {{-- Removed Status Makanan from main list for simplicity, can be in detail --}}
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reservasi as $item) {{-- Changed variable name to $item for clarity --}}
                <tr>
                    <td>{{ $item->kode_reservasi }}</td>
                    <td>
                        {{-- Tampilkan badge berdasarkan sumber --}}
                        @if($item->source === 'dine_in')
                            <span class="badge bg-info"><i class="bi bi-shop me-1"></i> Dine-in</span>
                        @elseif($item->source === 'online')
                            <span class="badge bg-success"><i class="bi bi-globe me-1"></i> Online</span>
                        @else
                            <span class="badge bg-secondary">Unknown</span>
                        @endif
                    </td> {{-- Tampilkan sumber di sini --}}
                    {{-- Tampilkan nama pelanggan dari nama_pelanggan atau relasi pengguna --}}
                    <td>{{ $item->nama_pelanggan ?? ($item->pengguna->name ?? '-') }}</td>
                    {{-- Tampilkan nomor dan area meja --}}
                    <td>{{ $item->meja->nomor_meja ?? '-' }} ({{ $item->meja->area ?? '-' }})</td>
                    {{-- Tampilkan jumlah tamu --}}
                    <td>{{ $item->jumlah_tamu ?? '-' }}</td>
                    {{-- Tampilkan waktu kedatangan atau waktu dibuat (untuk dine-in) --}}
                    <td>
                        @if($item->waktu_kedatangan)
                             {{ \Carbon\Carbon::parse($item->waktu_kedatangan)->translatedFormat('d M Y H:i') }}
                        @else
                             {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y H:i') }} {{-- Use created_at if waktu_kedatangan is null --}}
                        @endif
                    </td>
                    {{-- Tampilkan status reservasi --}}
                    <td>
                        @php
                            $statusClass = 'secondary';
                            switch($item->status) {
                                case 'dipesan': $statusClass = 'primary'; break;
                                case 'confirmed': $statusClass = 'primary'; break; // Assuming 'confirmed' is a status
                                case 'pending_arrival': $statusClass = 'warning'; break;
                                case 'active_order': $statusClass = 'info'; break;
                                case 'paid': $statusClass = 'success'; break; // Status paid
                                case 'selesai': $statusClass = 'success'; break;
                                case 'dibatalkan': $statusClass = 'danger'; break;
                                default: $statusClass = 'secondary'; break; // Default if status is unexpected
                            }
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    {{-- Tampilkan status kehadiran dari kolom baru --}}
                    <td>
                         @php
                             $kehadiranClass = 'secondary';
                             $kehadiranText = ucfirst($item->kehadiran_status ?? 'N/A'); // Default to N/A if null
                             switch($item->kehadiran_status) {
                                 case 'hadir': $kehadiranClass = 'success'; break;
                                 case 'tidak_hadir': $kehadiranClass = 'danger'; break;
                                 case 'belum_dikonfirmasi': $kehadiranClass = 'warning'; break;
                                 default: $kehadiranClass = 'secondary'; break; // Default if status is unexpected
                             }
                         @endphp
                         <span class="badge bg-{{ $kehadiranClass }}">{{ $kehadiranText }}</span>
                    </td> {{-- <--- Tampilkan status kehadiran di sini --}}
                    {{-- Tampilkan status pembayaran --}}
                    <td>
                         @if($item->status === 'paid')
                             <span class="badge bg-success">Lunas ({{ ucfirst($item->payment_method ?? '-') }})</span>
                         @else
                             <span class="badge bg-warning text-dark">Belum Lunas</span>
                         @endif
                    </td>
                    {{-- <td> --}}
                         {{-- @php
                             // Logic untuk status makanan per reservasi (bisa kompleks)
                             // Misalnya, cek status semua order item terkait
                             $allOrdersServed = $item->orders->isNotEmpty() && $item->orders->every('status', 'served');
                             $someOrdersPending = $item->orders->isNotEmpty() && $item->orders->contains('status', 'pending');
                             $statusMakananBadgeClass = 'secondary';
                             $statusMakananText = 'Belum Ada';

                             if ($allOrdersServed) {
                                 $statusMakananBadgeClass = 'success';
                                 $statusMakananText = 'Selesai';
                             } elseif ($someOrdersPending) {
                                 $statusMakananBadgeClass = 'warning';
                                 $statusMakananText = 'Pending';
                             } elseif ($item->orders->isNotEmpty()) {
                                  $statusMakananBadgeClass = 'info';
                                  $statusMakananText = 'Diproses'; // Or other status
                             }
                         @endphp
                         <span class="badge bg-{{ $statusMakananBadgeClass }}">{{ $statusMakananText }}</span> --}}
                    {{-- </td> --}}
                    <td>
                        {{-- Link ke halaman detail --}}
                        <a href="{{ route('pelayan.order.summary', $item->id) }}" class="btn btn-info btn-sm me-1" title="Lihat Ringkasan"><i class="bi bi-receipt"></i></a> {{-- Link ke summary --}}
                         {{-- Anda bisa tambahkan tombol aksi lain di sini, misal Edit/Cancel jika status memungkinkan --}}
                         {{-- <a href="{{ route('pelayan.reservasi.edit', $item->id) }}" class="btn btn-warning btn-sm me-1" title="Edit Reservasi"><i class="bi bi-pencil"></i></a> --}}
                         {{-- <form action="{{ route('pelayan.reservasi.cancel', $item->id) }}" method="POST" class="d-inline">
                             @csrf
                             @method('PUT')
                             <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin membatalkan reservasi ini?')" title="Batalkan Reservasi"><i class="bi bi-x-circle"></i></button>
                         </form> --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Tidak ada data reservasi atau pesanan dine-in.</td> {{-- Adjusted colspan --}}
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {{ $reservasi->withQueryString()->links() }}
    </div>
</div>
@endsection
