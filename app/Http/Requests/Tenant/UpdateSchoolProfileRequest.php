<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSchoolProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'school_name'       => ['required', 'string', 'max:150'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'address'           => ['nullable', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'email'             => ['nullable', 'email', 'max:150'],
            'website'           => ['nullable', 'url', 'max:255'],
            'logo'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:2048'],
            'admission_pattern' => ['nullable', 'string', 'max:100'],
            'admissions_open'   => ['boolean'],
            'currency_code'     => ['nullable', 'string', 'size:3'],
            'currency_symbol'   => ['nullable', 'string', 'max:5'],
        ];
    }
}
