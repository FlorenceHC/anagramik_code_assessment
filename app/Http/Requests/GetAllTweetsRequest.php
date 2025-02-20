<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAllTweetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:1', 'max:255'],
            'page'     => ['sometimes', 'integer', 'min:1', 'nullable'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100', 'nullable'],
        ];
    }
}
