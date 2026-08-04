<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('report'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scan_result_id' => ['nullable', 'exists:scan_results,id'],
            'report_date' => ['required', 'date'],
            'analyst' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'final', 'submitted'])],
        ];
    }
}
