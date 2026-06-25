<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('expenses.edit');
    }

    public function rules(): array
    {
        return [
            'category_id'  => ['required', 'uuid', 'exists:expense_categories,id'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'date'         => ['required', 'date', 'before_or_equal:today'],
            'description'  => ['required', 'string', 'max:255'],
            'receipt'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
