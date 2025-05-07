@extends('pelayan.layout.app')

@section('title', 'Dashboard Pelayan')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Status Meja</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @for ($i = 1; $i <= 12; $i++)
                            <div class="col-md-3 mb-4">
                                <div class="card {{ in_array($i, [1, 3, 5, 7, 9]) ? 'bg-danger' : (in_array($i, [2, 8]) ? 'bg-warning' : 'bg-success') }} text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-2">Meja {{ sprintf('%02d', $i) }}</h4>
                                        <p class="mb-0">
                                            {{ in_array($i, [1, 3, 5, 7, 9]) ? 'Terisi' : (in_array($i, [2, 8]) ? 'Dipesan' : 'Kosong') }}
                                        </p>
                                        @if(in_array($i, [1, 3, 5, 7, 9]))
                                            <small>Pesanan: P-20250507-{{ sprintf('%03d', $i) }}</small>
                                        @endif
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-center">
                                        <button class="btn btn-sm {{ in_array($i, [1, 3, 5, 7, 9]) ? 'btn-light' : 'btn-dark' }}">
                                            {{ in_array($i, [1, 3, 5, 7, 9]) ? 'Lihat Pesanan' : 'Tambah Pesanan' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection