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
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    document.getElementById('pay-button').addEventListener('click', function() {
        // Panggil Snap UI
        snap.pay('{{ $snap_token }}', {
            onSuccess: function(result) {
                // 1) AJAX untuk settle payment
                fetch("{{ route('pelayan.reservasi.settle', $reservasi->id) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'   // ← tambahkan ini
                },
                body: JSON.stringify({}) 
                })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        // 2) Redirect setelah konfirmasi backend
                        window.location.href = "{{ route('pelayan.reservasi') }}?payment=success";
                    } else {
                        alert('Gagal menyimpan status pembayaran.');
                    }
                })
                .catch(() => {
                    alert('Terjadi kesalahan saat update status.');
                });
            },
            onPending: function(result) {
                // Tampilkan QRIS code
                if (result.qris_image_url) {
                    document.getElementById('qris-container').innerHTML =
                        '<img src="'+result.qris_image_url+'" class="img-fluid" alt="Scan QRIS">'+
                        '<p class="mt-2">Scan QRIS ini untuk menyelesaikan pembayaran</p>';
                    document.getElementById('qris-container').style.display = 'block';
                    document.getElementById('pay-button').style.display = 'none';
                }
            },
            onError: function(err) {
                console.error(err);
                window.location.href = "{{ route('pelayan.reservasi') }}?payment=failed";
            }
        });
    });
</script>
@endsection
