<?php

namespace App\Helpers;

use Midtrans\Config;

class MidtransHelper
{
    public static function configure()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }
}
