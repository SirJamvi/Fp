<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddItemsRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Retained from OVERALL: Anyone can make this request
    }

    public function rules()
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:1000',
        ];
    }
    // No custom attributes() or messages() methods here, as they were in 'main'
}