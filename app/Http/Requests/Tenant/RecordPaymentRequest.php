<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.create');
    }

    public function rules(): array
    {
        return [
            'student_id'       => ['required', 'uuid', 'exists:students,id'],
            'fee_structure_id' => ['required', 'uuid', 'exists:fee_structures,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
