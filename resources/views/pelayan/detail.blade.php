@extends('pelayan.layout.app')

@section('title', $title)

@section('content')
<div class="container mt-4">
    <h4><i class="bi bi-info-circle-fill text-primary"></i> {{ $title }}</h4>

    {{-- Detail Pesanan --}}
    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Detail Pesanan:</label>
        <div class="col-sm-10">
            <textarea class="form-control" rows="4" readonly>
@foreach ($orders as $order)
{{ $order->menu->name }} ({{ $order->quantity }}x),
@endforeach
            </textarea>
        </div>
    </div>

      {{-- Informasi Meja --}}
  <div class="mb-3 row">
    <label class="col-sm-2 col-form-label">Informasi Meja:</label>
    <div class="col-sm-10">
      @php $from = $from ?? 'reservasi'; @endphp
      <textarea class="form-control" rows="3" readonly>
@if($from === 'reservasi')
  {{-- Online reservation --}}
  @php $mejaReservasi = $reservasi->mejaReservasi ?? collect(); @endphp

  @if($mejaReservasi->isNotEmpty())
    @foreach($mejaReservasi as $mr)
      {{ $mr->meja->nomor_meja }} ({{ $mr->meja->area }})@if(!$loop->last), @endif
    @endforeach
  @else
    N/A
  @endif

@elseif($from === 'dinein')
  {{-- Dine-in --}}
  @if($reservasi->mejaUtama)
    {{ $reservasi->mejaUtama->nomor_meja }} ({{ $reservasi->mejaUtama->area }})
  @else
    N/A
  @endif

@endif
      </textarea>
    </div>
  </div>


    {{-- Konfirmasi Kehadiran --}}
    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Konfirmasi Kehadiran:</label>
        <div class="col-sm-10">
            <select class="form-select" disabled>
                <option {{ $reservasi->kehadiran_status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                <option {{ $reservasi->kehadiran_status === 'belum_dikonfirmasi' ? 'selected' : '' }}>Belum Dikonfirmasi</option>
                <option {{ $reservasi->kehadiran_status === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
            </select>
        </div>
    </div>

    {{-- Total Harga --}}
    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Total Harga:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" 
                   value="Rp. {{ number_format($totalHarga, 0, ',', '.') }}" readonly>
        </div>
    </div>

    {{-- Tombol Kembali --}}
    <div class="d-flex gap-2">
        @php
            $backRoute = request('from') === 'dinein'
                ? route('pelayan.dinein')
                : route('pelayan.reservasi');
        @endphp
        <a href="{{ $backRoute }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
