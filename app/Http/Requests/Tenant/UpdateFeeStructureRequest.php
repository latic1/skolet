<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateFeeStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.edit');
    }

    public function rules(): array
    {
        return [
            'billing_cycle'    => ['required', 'string', 'in:term,annual'],
            'fee_item'         => ['required', 'string', 'max:100'],
            'amount'           => ['required', 'numeric', 'min:0'],
            'target_class'     => ['required', 'string', 'max:100'],
            'term_id'          => ['nullable', 'uuid', 'exists:terms,id'],
            'academic_year_id' => ['nullable', 'uuid', 'exists:academic_years,id'],
            'is_mandatory'     => ['boolean'],
            'due_date'         => ['nullable', 'date'],
        ];
    }
}
