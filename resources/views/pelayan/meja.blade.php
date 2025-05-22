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
            <button class="btn btn-sm btn-outline-secondary">🔽 Filter</button>
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
                        <th>Keterangan Meja</th>
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
                                <form action="{{ route('pelayan.meja.toggle', $item->id) }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    <label class="{{ $item->status === 'nonaktif' ? 'switch switch-nonaktif' : 'switch switch-ready' }} me-2">
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
                                        <form action="{{ route('pelayan.meja.setTersedia', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="badge bg-warning text-dark border-0" style="cursor: pointer;">
                                                Sedang digunakan (klik untuk kosongkan)
                                            </button>
                                        </form>
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

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    .switch-ready input:checked + .slider {
        background-color: #28a745;
    }

    .switch-nonaktif .slider {
        background-color: #dc3545;
    }

    .switch-ready input:checked + .slider:before {
        transform: translateX(26px);
    }

    .switch-nonaktif input:checked + .slider:before {
        transform: translateX(26px);
    }
</style>
@endsection
