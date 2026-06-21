<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class SaveMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('exams.edit');
    }

    public function rules(): array
    {
        return [
            'exam_id'    => ['required', 'uuid', 'exists:exams,id'],
            'class_id'   => ['required', 'uuid', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'uuid', 'exists:sections,id'],
            'subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'marks'      => ['required', 'array'],
            'marks.*'    => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
