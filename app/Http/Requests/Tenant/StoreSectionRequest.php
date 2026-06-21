<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        $schoolClass = $this->route('schoolClass');
        $classId     = $schoolClass instanceof SchoolClass ? $schoolClass->id : (string) $schoolClass;

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('sections', 'name')->where('class_id', $classId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => "This class already has a section named ':input'.",
        ];
    }
}
