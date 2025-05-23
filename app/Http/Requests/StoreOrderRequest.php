<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreOrderRequest extends FormRequest
{
    /**
     * Authorize the request: hanya boleh jika user sudah terautentikasi.
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Validation rules untuk menyimpan order baru.
     */
    public function rules()
    {
        return [
            'meja_id'          => 'required|exists:meja,id',
            'nama_pelanggan'   => 'nullable|string|max:255',
            'jumlah_tamu'      => 'required|integer|min:1',
            'items'            => 'required|array|min:1',
            'items.*.menu_id'  => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes'    => 'nullable|string|max:1000',
        ];
    }

    /**
     * Nama‐nama atribut (untuk pesan error).
     */
    public function attributes()
    {
        return [
            'meja_id'          => 'Meja',
            'nama_pelanggan'   => 'Nama Pelanggan',
            'jumlah_tamu'      => 'Jumlah Tamu',
            'items'            => 'Daftar Menu',
            'items.*.menu_id'  => 'Menu',
            'items.*.quantity' => 'Kuantitas',
            'items.*.notes'    => 'Catatan',
        ];
    }

    /**
     * Pesan custom untuk masing‐masing rule.
     */
    public function messages()
    {
        return [
            'meja_id.required'         => 'Meja harus dipilih.',
            'meja_id.exists'           => 'Meja tidak ditemukan.',
            'jumlah_tamu.min'          => 'Jumlah tamu minimal 1.',
            'items.required'           => 'Minimal satu item harus dipesan.',
            'items.*.menu_id.exists'   => 'Menu tidak ditemukan.',
            'items.*.quantity.min'     => 'Kuantitas minimal 1.',
            'items.*.notes.max'        => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
