<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Konfigurasi SMTP tidak lagi diatur lewat halaman ini — sistem
     * memakai konfigurasi mail bawaan (.env).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'instansi_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'head_name' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
        ];
    }
}
