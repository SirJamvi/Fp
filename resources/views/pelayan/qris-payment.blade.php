@extends('pelayan.layout.app')

@section('title', 'Pembayaran 50% Reservasi')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#107672] to-[#0d625f] py-8 px-4">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white">Pembayaran Sisa Reservasi</h1>
            <p class="text-white opacity-90 mt-2">Selesaikan pembayaran untuk reservasi Anda</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-[#107672] py-4 px-6">
                <h2 class="text-xl font-semibold text-white">Detail Reservasi</h2>
            </div>
            
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between border-b pb-3">
                        <span class="text-gray-600 font-medium">Kode Reservasi:</span>
                        <span class="text-gray-900 font-semibold">{{ $reservasi->kode_reservasi }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-3">
                        <span class="text-gray-600 font-medium">Jumlah Dibayar:</span>
                        <span class="text-gray-900 font-semibold">Rp {{ number_format($jumlah_dibayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">Status:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                            Belum Lunas
                        </span>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <button id="pay-button" class="py-3 px-6 rounded-xl bg-gradient-to-r from-[#107672] to-[#0d625f] hover:from-[#0d625f] hover:to-[#0a4e4b] text-white font-semibold shadow-lg transform hover:-translate-y-1 transition duration-300 ease-in-out flex items-center justify-center mx-auto">
                        <span>Lanjutkan Pembayaran</span>
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="qris-container" class="mt-6 bg-white rounded-2xl shadow-xl p-6 hidden">
            <h3 class="text-lg font-semibold text-center text-gray-800 mb-4">Scan QRIS</h3>
            <div class="flex justify-center">
                <div class="bg-gray-100 p-4 rounded-lg">
                    <!-- QR Image will be inserted here -->
                </div>
            </div>
            <p class="text-center text-gray-600 mt-4">Gunakan aplikasi e-wallet atau mobile banking untuk melakukan pembayaran</p>
        </div>

        <div class="mt-6 text-center">
            <p class="text-white opacity-80 text-sm">© {{ date('Y') }} Restoran Mewah. All rights reserved.</p>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    document.getElementById('pay-button').addEventListener('click', function() {
        const button = document.getElementById('pay-button');
        button.innerHTML = '<span>Memproses...</span>';
        button.disabled = true;
        
        snap.pay('{{ $snap_token }}', {
            onSuccess: function(result) {
                fetch("{{ route('pelayan.reservasi.settle', $reservasi->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({}) 
                })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        window.location.href = "{{ route('pelayan.reservasi') }}?payment=success";
                    } else {
                        alert('Gagal menyimpan status pembayaran.');
                        button.innerHTML = '<span>Lanjutkan Pembayaran</span><svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>';
                        button.disabled = false;
                    }
                })
                .catch(() => {
                    alert('Terjadi kesalahan saat update status.');
                    button.innerHTML = '<span>Lanjutkan Pembayaran</span><svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>';
                    button.disabled = false;
                });
            },
            onPending: function(result) {
                if (result.qris_image_url) {
                    document.getElementById('qris-container').innerHTML = `
                        <h3 class="text-lg font-semibold text-center text-gray-800 mb-4">Scan QRIS</h3>
                        <div class="flex justify-center">
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <img src="${result.qris_image_url}" class="w-48 h-48" alt="Scan QRIS">
                            </div>
                        </div>
                        <p class="text-center text-gray-600 mt-4">Gunakan aplikasi e-wallet atau mobile banking untuk melakukan pembayaran</p>
                    `;
                    document.getElementById('qris-container').classList.remove('hidden');
                    button.style.display = 'none';
                }
            },
            onError: function(err) {
                console.error(err);
                button.innerHTML = '<span>Lanjutkan Pembayaran</span><svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>';
                button.disabled = false;
                window.location.href = "{{ route('pelayan.reservasi') }}?payment=failed";
            }
        });
    });
</script>
@endsection