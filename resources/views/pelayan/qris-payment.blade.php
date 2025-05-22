@extends('pelayan.layout.app')

@section('title', 'Pembayaran QRIS')

@section('content')
<div class="container mt-4">
    <h2>Pembayaran QRIS</h2>
    
    <div class="card mt-3">
        <div class="card-body">
            <h5>Kode Reservasi: <strong>{{ $reservasi->kode_reservasi }}</strong></h5>
            <p>Jumlah Dibayar: <strong>Rp {{ number_format($jumlah_dibayar, 0, ',', '.') }}</strong></p>
            <p>Status: <span class="badge bg-warning">Menunggu Pembayaran</span></p>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <button id="pay-button" class="btn btn-primary">Lanjutkan Pembayaran</button>
        <div id="qris-container" class="mt-3" style="display: none;">
            <!-- QRIS akan muncul di sini -->
        </div>
    </div>
</div>

<!-- Midtrans Snap JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    document.getElementById('pay-button').addEventListener('click', function() {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) {
                window.location.href = "{{ route('pelayan.reservasi') }}?payment=success";
            },
            onPending: function(result) {
                // Tampilkan QRIS
                if (result.qris_image_url) {
                    document.getElementById('qris-container').innerHTML = 
                        '<img src="'+result.qris_image_url+'" alt="QRIS Code" class="img-fluid">' +
                        '<p class="mt-2">Scan QRIS ini untuk melakukan pembayaran</p>';
                    document.getElementById('qris-container').style.display = 'block';
                    document.getElementById('pay-button').style.display = 'none';
                }
            },
            onError: function(result) {
                window.location.href = "{{ route('pelayan.reservasi') }}?payment=failed";
            }
        });
    });
</script>
@endsection