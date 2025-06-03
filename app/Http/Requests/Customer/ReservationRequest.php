<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Pelanggan yang terautentikasi bisa membuat reservasi
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // 'meja_id' => 'required|exists:meja,id', // Tidak lagi required jika sistem mencari meja
            'waktu_kedatangan' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after_or_equal:' . Carbon::now()->addMinutes(15)->format('Y-m-d H:i:s'), // Minimal 15 menit dari sekarang
            ],
            'jumlah_tamu' => 'required|integer|min:1',
            'catatan' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'waktu_kedatangan.after_or_equal' => 'Waktu kedatangan minimal 15 menit dari sekarang.',
        ];
    }
}