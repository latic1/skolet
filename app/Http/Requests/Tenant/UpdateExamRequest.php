<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Exam;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('exams.edit');
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'term_id'    => ['required', 'uuid', 'exists:terms,id'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'exam_role'  => ['nullable', 'in:none,ca,end_of_term'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($this->input('exam_role') !== Exam::ROLE_END_OF_TERM) {
                return;
            }

            $exists = Exam::where('term_id', $this->input('term_id'))
                ->where('exam_role', Exam::ROLE_END_OF_TERM)
                ->where('id', '!=', $this->route('exam')->id)
                ->exists();

            if ($exists) {
                $v->errors()->add('exam_role', 'This term already has an End of Term Exam. Only one is allowed per term.');
            }
        });
    }
}
