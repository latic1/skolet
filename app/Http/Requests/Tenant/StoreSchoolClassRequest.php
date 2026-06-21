<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('school_classes', 'name'),
            ],
            'order' => [
                'nullable', 'integer', 'min:0',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $clash = SchoolClass::where('order', (int) $value)->first();
                    if ($clash) {
                        $fail("Order {$value} is already used by '{$clash->name}' — choose a different order.");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => "A class named ':input' already exists.",
        ];
    }
}
