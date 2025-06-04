<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan user sudah ter‐auth (misalnya lewat middleware Sanctum)
        return true;
    }

    public function rules(): array
    {
        return [
            // Validasi format tanggal & minimal 15 menit dari sekarang (bisa dicek di controller juga)
            'waktu_kedatangan' => [
                'required',
                'date_format:Y-m-d H:i:s',
                // Jika ingin memeriksa minimal 15 menit, sebaiknya di controller untuk fleksibilitas
                // 'after_or_equal:' . Carbon::now()->addMinutes(15)->format('Y-m-d H:i:s'),
            ],

            'jumlah_tamu' => 'required|integer|min:1|max:20',
            'catatan'     => 'nullable|string|max:1000',

            // id_meja sekarang wajib array minimal 1 item, dan tiap item harus valid
            'id_meja'   => 'required|array|min:1',
            'id_meja.*' => 'integer|exists:meja,id',
        ];
    }

    public function messages(): array
    {
        return [
            'waktu_kedatangan.required'      => 'Waktu kedatangan harus diisi.',
            'waktu_kedatangan.date_format'   => 'Format waktu kedatangan tidak valid. Gunakan YYYY-MM-DD HH:mm:ss.',
            // 'waktu_kedatangan.after_or_equal' => 'Waktu kedatangan minimal 15 menit dari sekarang.',

            'jumlah_tamu.required'           => 'Jumlah tamu harus diisi.',
            'jumlah_tamu.integer'            => 'Jumlah tamu harus berupa angka.',
            'jumlah_tamu.min'                => 'Jumlah tamu minimal 1 orang.',
            'jumlah_tamu.max'                => 'Jumlah tamu maksimal 20 orang.',

            'catatan.string'                 => 'Catatan harus berupa teks.',
            'catatan.max'                    => 'Catatan maksimal 1000 karakter.',

            'id_meja.required'               => 'Anda harus memilih minimal satu meja.',
            'id_meja.array'                  => 'Format ID meja tidak valid.',
            'id_meja.min'                    => 'Anda harus memilih minimal satu meja.',
            'id_meja.*.integer'              => 'ID meja harus berupa angka.',
            'id_meja.*.exists'               => 'Salah satu meja yang dipilih tidak ditemukan.',
        ];
    }
}
