@extends('pelayan.layout.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="container mt-4">
  <h2 class="text-center mb-4">Scan QR Code Kehadiran</h2>

  <div id="reader" style="width:100%;max-width:500px;" class="mx-auto my-4 border rounded"></div>
  <div id="scan-feedback" class="text-center d-none">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2">Memproses kode reservasi, harap tunggu...</p>
  </div>

  @if(session('success'))
    <div class="alert alert-success text-center">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger text-center">{{ session('error') }}</div>
  @endif

  <div class="text-center mt-3">
    <a href="{{ route('pelayan.reservasi') }}" class="btn btn-secondary">← Kembali</a>
  </div>
</div>
@endsection

@push('scripts')
<!-- Muat library HTML5 QR Code secara global -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    console.log('Html5Qrcode:', typeof Html5Qrcode); 
    if (typeof Html5Qrcode === 'undefined') {
      console.error('Html5Qrcode masih undefined – CDN gagal dimuat');
      return;
    }

    const feedback = document.getElementById('scan-feedback');
    let hasScanned = false;

    // Inisialisasi scanner
    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: 250 };

    Html5Qrcode.getCameras()
      .then(cameras => {
        if (!cameras || cameras.length === 0) {
          alert('Tidak ada kamera terdeteksi');
          return;
        }
        // pilih kamera pertama (biasanya webcam laptop)
        const cameraId = cameras[0].id;
        console.log('Using cameraId:', cameraId);

        html5QrCode.start(
          cameraId,
          config,
          decodedText => {
            console.log('QR terbaca:', decodedText);
            if (hasScanned) return;
            hasScanned = true;
            feedback.classList.remove('d-none');

            html5QrCode.stop().then(() => {
              fetch(`{{ url('customer/verify-attendance') }}/${decodedText}`, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Accept': 'application/json'
                }
              })
              .then(r => r.json())
              .then(json => {
                feedback.classList.add('d-none');
                alert((json.success ? '✅ ' : '❌ ') + json.message);
                if (json.success) window.location.reload();
                else hasScanned = false;
              })
              .catch(err => {
                console.error('Fetch error:', err);
                alert('⚠️ Error koneksi');
                hasScanned = false;
              });
            });
          },
          error => {
            // ignore per-frame scan errors
            console.log('Scan failure:', error);
          }
        );
      })
      .catch(err => {
        console.error('getCameras error:', err);
        alert('Gagal akses kamera: ' + err);
      });
  });
</script>
@endpush
