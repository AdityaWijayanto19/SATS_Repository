<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDeviceManajemenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => 'required|string|max:50|unique:devices,device_id',
            'nama' => 'required|string|max:255',
        ];
    }
}
