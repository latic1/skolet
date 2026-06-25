<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class StoreExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('expenses.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:expense_categories,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => "A category named ':input' already exists.",
        ];
    }
}
