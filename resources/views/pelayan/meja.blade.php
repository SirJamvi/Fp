@extends('pelayan.layout.app')

@section('title', 'Table Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Table</h4>
        <div class="d-flex">
            <form method="GET" action="{{ route('pelayan.meja') }}" class="d-flex align-items-center me-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search...">
                <button class="btn btn-sm btn-outline-secondary ms-2" type="submit">🔍</button>
                @if(request('search'))
                    <a href="{{ route('pelayan.meja') }}" class="btn btn-sm btn-outline-danger ms-2">Reset</a>
                @endif
            </form>

            <button class="btn btn-sm btn-outline-secondary">
                🔽 Filter
            </button>
        </div>
    </div>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-borderless align-middle">
                <thead class="text-muted">
                    <tr>
                        <th><input type="checkbox" /></th>
                        <th>No. Table</th>
                        <th>Area</th>
                        <th>Capacity</th>
                        <th>Status Meja</th>
                        <th>Keterangan</th> <!-- Kolom keterangan baru -->
                    </tr>
                </thead>
                <tbody>
                    @forelse ($meja as $item)
                        <tr class="border-bottom">
                            <td><input type="checkbox" value="{{ $item->id }}"></td>
                            <td>{{ $item->nomor_meja }}</td>
                            <td>{{ $item->area }}</td>
                            <td>{{ $item->kapasitas }} Orang</td>
                            <td>
                                <form action="{{ route('pelayan.meja.toggle', $item->id) }}" method="POST">
                                    @csrf
                                    <label class="switch">
                                        <input type="checkbox" onchange="this.form.submit()" {{ $item->status !== 'nonaktif' ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                @switch($item->status)
                                    @case('tersedia')
                                        <span class="badge bg-success">Siap digunakan</span>
                                        @break
                                    @case('terisi')
                                        <span class="badge bg-warning text-dark">Sedang digunakan</span>
                                        @break
                                    @case('dipesan')
                                        <span class="badge bg-primary">Sudah dipesan</span>
                                        @break
                                    @case('nonaktif')
                                        <span class="badge bg-secondary">Tidak aktif</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">Tidak diketahui</span>
                                @endswitch
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data meja.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection