<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSensorDataBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'readings' => 'required|array|min:1|max:1000',
            'readings.*.heart_rate' => 'nullable|integer|min:0|max:250',
            'readings.*.spo2' => 'nullable|integer|min:0|max:100',
            'readings.*.temperature' => 'nullable|numeric|min:0|max:45',
            'readings.*.status' => 'nullable|in:normal,warning,critical,no_finger',
            'readings.*.prediction' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'readings.required' => 'Readings array is required',
            'readings.min' => 'At least 1 reading is required',
            'readings.max' => 'Maximum 1000 readings per batch',
        ];
    }
}
