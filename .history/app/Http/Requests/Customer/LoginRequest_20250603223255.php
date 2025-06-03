<?php
//d

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Siapapun bisa mencoba login
    }

    public function rules(): array
    {
        return [
            // Boleh memasukkan email ATAU nomor_hp
            'email'    => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'Email atau nomor HP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }
}
