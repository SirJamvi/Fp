<?php

namespace App\Helpers;

use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Config as LaravelConfig;

class MidtransHelper
{
    /**
     * Konfigurasi Midtrans (panggil sebelum getSnapToken).
     */
    public static function configure()
    {
        // Ambil key dan mode dari config/services.php atau .env
        Config::$serverKey    = LaravelConfig::get('services.midtrans.server_key');
        Config::$isProduction = LaravelConfig::get('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Dapatkan Snap Token dari Midtrans.
     * @param array $params Payload transaksi
     * @return string snap_token
     * @throws \Exception jika gagal generate
     */
    public static function getSnapToken(array $params)
    {
        return Snap::getSnapToken($params);
    }
}
