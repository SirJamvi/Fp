@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $title }}</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(count($pesanan) > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Meja</th>
                                <th>Status</th>
                                <th>Waktu Pesan</th>
                                <th>Detail</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanan as $index => $p)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $p->meja->nomor }}</td>
                                    <td>
                                        <span class="badge @if($p->status == 'pending') bg-warning
                                                          @elseif($p->status == 'diproses') bg-info
                                                          @elseif($p->status == 'selesai') bg-success
                                                          @else bg-danger @endif">
                                            {{ ucfirst($p->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $p->id }}">
                                            Lihat Menu
                                        </button>
                                        
                                        <!-- Modal Detail Pesanan -->
                                        <div class="modal fade" id="detailModal{{ $p->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $p->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detailModalLabel{{ $p->id }}">Detail Pesanan Meja {{ $p->meja->nomor }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Waktu Pesan:</strong> {{ $p->created_at->format('d/m/Y H:i') }}</p>
                                                        <p><strong>Status:</strong> {{ ucfirst($p->status) }}</p>
                                                        
                                                        @if($p->catatan)
                                                            <p><strong>Catatan:</strong> {{ $p->catatan }}</p>
                                                        @endif
                                                        
                                                        <h6 class="mt-3">Daftar Menu:</h6>
                                                        <ul class="list-group">
                                                            @foreach($p->menu as $menu)
                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                    {{ $menu->nama }}
                                                                    <span class="badge bg-primary rounded-pill">{{ $menu->pivot->jumlah }}x</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <form action="{{ route('koki.update-status-pesanan', $p->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            
                                            @if($p->status == 'pending')
                                                <input type="hidden" name="status" value="diproses">
                                                <button type="submit" class="btn btn-sm btn-info">Proses</button>
                                            @elseif($p->status == 'diproses')
                                                <input type="hidden" name="status" value="selesai">
                                                <button type="submit" class="btn btn-sm btn-success">Selesai</button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Tidak ada pesanan yang perlu diproses saat ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection