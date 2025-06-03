<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class PreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Pelanggan yang terautentikasi bisa membuat pra-pemesanan
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // 'meja_id' tidak perlu ditambahkan di sini karena untuk pre-order, meja belum tentu dipilih.
            // Anda akan mengaturnya ke NULL di controller, dan database akan mengizinkan NULL.
            'jumlah_tamu' => 'nullable|integer|min:1', // Opsional, bisa default 1
            'catatan' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:1000',
        ];
    }
}