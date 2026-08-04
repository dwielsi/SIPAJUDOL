<?php

namespace App\Http\Requests;

use App\Models\ScanResult;
use Illuminate\Foundation\Http\FormRequest;

class StoreScanResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ScanResult::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'website_id' => ['required', 'exists:websites,id'],
        ];
    }
}
