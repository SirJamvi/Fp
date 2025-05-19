<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends Controller
{
    public function buktiPembayaran($kodeReservasi)
    {
        $reservasi = Reservasi::with(['pengguna', 'meja'])
            ->where('kode_reservasi', $kodeReservasi)
            ->firstOrFail();

        $qrCode = QrCode::size(250)->generate($reservasi->kode_reservasi);

        return view('user.bukti-pembayaran', [
            'reservasi' => $reservasi,
            'qrCode' => $qrCode,
        ]);
    }
}
