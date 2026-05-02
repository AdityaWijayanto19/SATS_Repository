<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSystemStatusRequest extends FormRequest
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
            'device_id' => 'required|string|exists:devices,device_id',
            'monitoring_status' => 'required|in:active,inactive',
            'battery_level' => 'nullable|integer|min:0|max:100',
            'signal_strength' => 'nullable|integer|min:0|max:100',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'device_id.required' => 'Device ID wajib diisi',
            'device_id.exists' => 'Device tidak ditemukan',
            'monitoring_status.required' => 'Monitoring status wajib diisi',
            'monitoring_status.in' => 'Status harus active atau inactive',
            'battery_level.integer' => 'Battery level harus angka',
            'battery_level.max' => 'Battery level maksimal 100',
        ];
    }
}
