<?php

namespace App\Http\Requests;

use App\Models\Keyword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKeywordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Keyword::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['required', 'string', 'max:100', Rule::unique('keywords', 'keyword')],
            'category' => ['required', 'string', 'max:50'],
            'active' => ['boolean'],
        ];
    }
}
