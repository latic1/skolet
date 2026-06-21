<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class ImportStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('staff.create');
    }

    public function rules(): array
    {
        return [
            'import_file' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'import_file.required' => 'Please choose a file to import.',
            'import_file.mimes'    => 'The file must be an Excel (.xlsx) or CSV (.csv) file.',
            'import_file.max'      => 'The file may not be larger than 5 MB.',
        ];
    }
}
