<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSensorDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_id'   => 'required|string|exists:devices,device_id',
            'heart_rate'  => 'nullable|integer|min:0|max:250',
            'spo2'        => 'nullable|integer|min:0|max:100',
            'temperature' => 'nullable|numeric|min:20|max:45',
            'status'      => 'nullable|in:normal,warning,critical',
            'prediction'  => 'nullable|string|max:50',
        ];
    }
}
