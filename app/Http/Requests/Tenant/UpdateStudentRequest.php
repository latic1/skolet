<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('students.edit');
    }

    public function rules(): array
    {
        return [
            'full_name'         => ['required', 'string', 'max:255'],
            'class_id'          => ['required', 'uuid', 'exists:school_classes,id'],
            'section_id'        => ['nullable', 'uuid', 'exists:sections,id'],
            'date_of_birth'     => ['nullable', 'date'],
            'gender'            => ['nullable', 'in:male,female,other'],
            'guardian_name'     => ['nullable', 'string', 'max:255'],
            'guardian_contact'  => ['nullable', 'string', 'max:50'],
            'guardian_email'    => ['nullable', 'email', 'max:255'],
            'address'           => ['nullable', 'string', 'max:500'],
            'status'            => ['required', 'in:active,inactive,graduated'],
        ];
    }
}
