<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSchoolRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_name' => ['required', 'string', 'max:255'],
            'subdomain'   => ['required', 'string', 'max:63', 'regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$|^[a-z0-9]$/'],
            'admin_name'  => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'subdomain.regex' => 'The subdomain may only contain lowercase letters, numbers, and hyphens, and cannot start or end with a hyphen.',
        ];
    }
}
