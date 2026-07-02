<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateGradingWeightsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'ca_weight'   => ['required', 'integer', 'min:0', 'max:100'],
            'exam_weight' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $caWeight   = (int) $this->input('ca_weight');
            $examWeight = (int) $this->input('exam_weight');

            if ($caWeight + $examWeight !== 100) {
                $v->errors()->add('ca_weight', 'CA Weight and Exam Weight must add up to 100%.');
            }
        });
    }
}
