<!-- meja.blade.php -->
@extends('pelayan.layout.app')

@section('title', 'Table Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #107672;">
            <i class="bi bi-table text-white fs-5"></i>
        </div>
        <div>
            <h4 class="mb-0">Kelola Status & Ketersediaan Meja</h4>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #107672;">
        <div class="card-header text-white" style="background-color: #107672;">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-funnel"></i>
                Pencarian
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pelayan.meja') }}" class="d-flex gap-2">
                <div class="flex-grow-1">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control py-2" placeholder="Cari nomor meja atau area...">
                </div>
                <button class="btn btn-teal d-flex align-items-center gap-2 py-2" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('pelayan.meja') }}" class="btn btn-outline-teal d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-left: 4px solid #107672;">
        <div class="card-header text-white" style="background-color: #107672;">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-table"></i>
                Daftar Meja
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #e0f2f1;">
                        <tr>
                            <th class="ps-4"><input type="checkbox" /></th>
                            <th>No. Meja</th>
                            <th>Area</th>
                            <th>Kapasitas</th>
                            <th>Status Meja</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($meja as $item)
                            <tr class="border-bottom">
                                <td class="ps-4"><input type="checkbox" value="{{ $item->id }}"></td>
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
                                    @if (in_array($item->status, ['terisi', 'dipesan']))
                                        <form action="{{ route('pelayan.meja.setTersedia', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="badge bg-warning text-dark border-0 p-2" style="cursor: pointer;">
                                                {{ $item->status === 'dipesan' ? 'Sudah dipesan (klik untuk kosongkan)' : 'Sedang digunakan (klik untuk kosongkan)' }}
                                            </button>
                                        </form>
                                    @elseif ($item->status === 'tersedia')
                                        <span class="badge bg-teal text-white p-2">Siap digunakan</span>
                                    @elseif ($item->status === 'nonaktif')
                                        <span class="badge bg-secondary p-2">Tidak aktif</span>
                                    @else
                                        <span class="badge bg-light text-dark p-2">Tidak diketahui</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-table" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Tidak ada data meja.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
        background-color: #107672;
    }

    .switch-nonaktif .slider {
        background-color: #dc3545;
    }

    .switch-ready input:checked + .slider:before,
    .switch-nonaktif input:checked + .slider:before {
        transform: translateX(26px);
    }

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
    .badge.bg-teal {
        background-color: #107672;
        color: white;
    }
</style>
@endsection