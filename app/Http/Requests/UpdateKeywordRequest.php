<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKeywordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('keyword'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['required', 'string', 'max:100', Rule::unique('keywords', 'keyword')->ignore($this->route('keyword'))],
            'category' => ['required', 'string', 'max:50'],
            'active' => ['boolean'],
        ];
    }
}
