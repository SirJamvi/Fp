@extends('pelayan.layout.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="container mt-4">
    <h2>Scan QR Code</h2>

    <div id="reader" style="width: 100%; max-width: 500px;" class="mx-auto my-4"></div>
    <div id="result" class="alert alert-info text-center d-none"></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="text-center mt-3">
        <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">Kembali ke Reservasi</a>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    const resultElement = document.getElementById('result');

    function onScanSuccess(decodedText, decodedResult) {
        // Cek jika hasil scan sudah mengandung URL
        if (decodedText.includes('http://') || decodedText.includes('https://')) {
            // Jika sudah URL lengkap, langsung redirect
            window.location.href = decodedText;
        } else {
            // Jika hanya kode, buat URL lengkap
            window.location.href = `/pelayan/scanqr/proses/${decodedText}`;
        }
        
        // Optional: Tampilkan alert
        alert("Memproses QR Code: " + decodedText);
    }

    function onScanFailure(error) {
        // Abaikan error
    }

    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: 250 };

    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            const cameraId = devices[0].id;
            html5QrCode.start(cameraId, config, onScanSuccess, onScanFailure);
        }
    }).catch(err => {
        resultElement.innerHTML = "Kamera tidak ditemukan.";
        resultElement.classList.remove('d-none');
        resultElement.classList.add('alert-danger');
    });
</script>
@endsection