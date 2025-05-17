@extends('components.layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Stok Bahan</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bahan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stok Tersedia</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Satuan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">Daging Sapi</p>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">5.2</p>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">kg</p>
                                    </td>
                                    <td>
                                        <span class="badge bg-gradient-warning">Hampir Habis</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">Bawang Merah</p>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">3.0</p>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">kg</p>
                                    </td>
                                    <td>
                                        <span class="badge bg-gradient-danger">Kritis</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection