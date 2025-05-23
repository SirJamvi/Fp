<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddItemsRequest extends FormRequest
{
    /**
     * Authorize the request: hanya boleh jika user sudah terautentikasi.
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Validation rules untuk menambah item ke order yang sudah berjalan.
     */
    public function rules()
    {
        return [
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
            'items'            => 'Daftar Item Tambahan',
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
            'items.required'          => 'Harap tambahkan minimal satu item.',
            'items.*.menu_id.exists'  => 'Menu tidak ditemukan.',
            'items.*.quantity.min'    => 'Kuantitas minimal 1.',
            'items.*.notes.max'       => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
