<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Public form — tidak perlu auth.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'category' => ['required', 'in:kendala_perangkat,kendala_aplikasi,request_akun,lainnya'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'urgency' => ['required', 'in:rendah,sedang,darurat'],
            'detail' => ['required', 'string', 'max:1000'],
        ];

        // Conditional: device_id wajib untuk kendala_perangkat
        if ($this->input('category') === 'kendala_perangkat') {
            $rules['device_id'] = ['required', 'string', 'max:50'];
        } else {
            $rules['device_id'] = ['nullable', 'string', 'max:50'];
        }

        // Conditional: role_requested & institution wajib untuk request_akun
        if ($this->input('category') === 'request_akun') {
            $rules['role_requested'] = ['required', 'in:nakes,dokter'];
            $rules['institution'] = ['required', 'string', 'max:255'];
        } else {
            $rules['role_requested'] = ['nullable', 'in:nakes,dokter'];
            $rules['institution'] = ['nullable', 'string', 'max:255'];
        }

        // Conditional: attachment opsional untuk kendala_perangkat & kendala_aplikasi
        if (in_array($this->input('category'), ['kendala_perangkat', 'kendala_aplikasi'])) {
            $rules['attachment'] = ['nullable', 'file', 'max:2048', 'mimes:jpg,jpeg,png'];
        }

        return $rules;
    }

    /**
     * Custom error messages (Indonesian).
     */
    public function messages(): array
    {
        return [
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'urgency.required' => 'Urgensi wajib dipilih.',
            'detail.required' => 'Detail kendala wajib diisi.',
            'detail.max' => 'Detail kendala maksimal 1000 karakter.',
            'device_id.required' => 'ID Perangkat wajib diisi untuk kendala perangkat.',
            'role_requested.required' => 'Role wajib dipilih untuk request akun.',
            'institution.required' => 'Instansi wajib diisi untuk request akun.',
            'attachment.max' => 'Ukuran file maksimal 2MB.',
            'attachment.mimes' => 'Format file harus JPG, JPEG, atau PNG.',
        ];
    }
}
