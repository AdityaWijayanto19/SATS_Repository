<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => 'required|string|max:100|unique:devices,device_id|regex:/^[a-zA-Z0-9_-]+$/',
            'name' => 'nullable|string|max:255',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'device_id.required' => 'Device ID wajib diisi',
            'device_id.unique' => 'Device ID sudah terdaftar',
            'device_id.regex' => 'Device ID hanya boleh mengandung huruf, angka, underscore, dan dash',
            'name.max' => 'Nama perangkat maksimal 255 karakter',
            'rate_limit_per_minute.min' => 'Rate limit minimal 1',
            'rate_limit_per_minute.max' => 'Rate limit maksimal 1000',
        ];
    }
}
