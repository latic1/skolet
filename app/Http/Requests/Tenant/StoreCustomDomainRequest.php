<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCustomDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)+$/i',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'domain.required' => 'Please enter a domain name.',
            'domain.regex'    => 'Enter a valid domain name (e.g. portal.yourschool.com).',
            'domain.max'      => 'Domain name must not exceed 255 characters.',
        ];
    }
}
