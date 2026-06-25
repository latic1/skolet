<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreFeeDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.edit');
    }

    public function rules(): array
    {
        $type = $this->input('discount_type');

        return [
            'fee_structure_id' => ['nullable', 'uuid', 'exists:fee_structures,id'],
            'discount_type'    => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value'   => array_filter([
                'required',
                'numeric',
                'min:0.01',
                $type === 'percentage' ? 'max:100' : null,
            ]),
            'reason'           => ['required', 'string', 'max:500'],
            'valid_from'       => ['nullable', 'date'],
            'valid_until'      => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'discount_value.max' => 'Percentage discount cannot exceed 100%.',
        ];
    }
}
