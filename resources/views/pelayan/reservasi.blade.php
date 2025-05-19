@extends('pelayan.layout.app')

@section('title', 'Dashboard Pelayan')

@section('content')
<div class="container mt-4">
    <h2>Reservation</h2>

    <form method="GET" action="{{ route('pelayan.reservasi') }}" class="d-flex mb-3 gap-2">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name">

        <select name="filter" class="form-select" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="week" {{ request('filter') == 'week' ? 'selected' : '' }}>This Week</option>
            <option value="month" {{ request('filter') == 'month' ? 'selected' : '' }}>This Month</option>
            <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>This Year</option>
        </select>

        <button type="submit" class="btn btn-primary">Search</button>

        @if(request('search') || request('filter'))
            <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>

    {{-- Tombol Konfirmasi QR di pojok kanan atas --}}
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('pelayan.scanqr') }}" class="btn btn-success">Konfirmasi</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Name</th>
                <th>Time & Date</th>
                <th>Status Pembayaran</th>
                <th>Status Kehadiran</th>
                <th>Status Makanan</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reservasi as $item)
                <tr>
                    <td>{{ $item->kode_reservasi }}</td>
                    <td>{{ $item->pengguna->nama ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->waktu_kedatangan)->format('H:i d M, Y') }}</td>
                    <td>{{ $item->pengguna->metode_pembayaran ?? 'Cash' }}</td>
                    <td>
                        @php
                            $statusHadir = $item->status === 'selesai' ? 'Hadir' : 'Tidak Hadir';
                            $warnaHadir = $item->status === 'selesai' ? 'success' : 'danger';
                        @endphp
                        <span class="text-{{ $warnaHadir }}">{{ $statusHadir }}</span>
                    </td>
                    <td>
                        @php
                            $statusMakanan = $item->orders->pluck('status_makanan')->unique()->filter()->implode(', ');
                        @endphp
                        <span class="badge bg-secondary">{{ $statusMakanan ?: 'Belum Ada' }}</span>
                    </td>
                    <td>
                        <a href="{{ route('pelayan.reservasi.detail', $item->id) }}" class="btn btn-primary btn-sm">Details</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada reservasi ditemukan.</td>
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
