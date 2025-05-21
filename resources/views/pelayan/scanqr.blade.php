@extends('pelayan.layout.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="container mt-4">
    <h2 class="text-center mb-4">Scan QR Code Kehadiran</h2>

    <div id="reader" style="width: 100%; max-width: 500px;" class="mx-auto my-4 border rounded"></div>

    <div id="scan-feedback" class="text-center d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memproses...</span>
        </div>
        <p class="mt-2">Memproses kode reservasi, harap tunggu...</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-center mt-3">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger text-center mt-3">{{ session('error') }}</div>
    @endif

    <div class="text-center mt-3">
        <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">← Kembali ke Reservasi</a>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let hasScanned = false; // Untuk mencegah pemrosesan ganda
    const feedback = document.getElementById('scan-feedback');

    function onScanSuccess(decodedText, decodedResult) {
        if (hasScanned) return; // Cegah multiple scan

        hasScanned = true;
        feedback.classList.remove('d-none');

        // Bersihkan reader untuk hentikan kamera
        html5QrCode.stop().then(() => {
            // Redirect ke backend proses
            if (decodedText.includes('http://') || decodedText.includes('https://')) {
                window.location.href = decodedText;
            } else {
                window.location.href = `/pelayan/scanqr/proses/${decodedText}`;
            }
        }).catch(err => {
            alert('Gagal menghentikan kamera: ' + err);
        });
    }

    function onScanFailure(error) {
        // Tidak perlu tampilkan error untuk setiap frame gagal scan
    }

    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: 250 };

    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            const cameraId = devices[0].id;
            html5QrCode.start(cameraId, config, onScanSuccess, onScanFailure);
        } else {
            alert("Tidak ada kamera ditemukan.");
        }
    }).catch(err => {
        alert("Kamera tidak bisa diakses: " + err);
    });
</script>
@endsection
