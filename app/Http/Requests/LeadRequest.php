<?php

namespace App\Http\Requests;

class LeadRequest extends PublicFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:190'],
            'mobile' => ['required', 'string', 'regex:'.self::MOBILE_REGEX],
            'email' => ['nullable', 'email', 'max:190'],
            'company' => ['nullable', 'string', 'max:190'],
            'request_type' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:3000'],
            'page' => ['nullable', 'string', 'max:190'],
            'referrer' => ['nullable', 'string', 'max:500'],
        ];
    }
}
