<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        $subject   = $this->route('subject');
        $currentId = $subject instanceof Subject ? $subject->id : (string) $subject;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects', 'name')->ignore($currentId)],
            'code' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => "A subject named ':input' already exists.",
        ];
    }
}
