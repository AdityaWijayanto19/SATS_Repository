<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDeviceConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wifi_name' => 'required|string|max:255',
            'wifi_password' => 'required|string|max:255',
            'api_key' => 'required|string',
        ];
    }
}
