<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('staff.create');
    }

    public function rules(): array
    {
        return [
            'full_name'   => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'role_title'  => ['nullable', 'string', 'max:100'],
            'status'      => ['required', 'in:active,inactive'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'system_role' => ['required', 'string', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'          => 'That email address is already in use by another account.',
            'system_role.exists'    => 'The selected role does not exist.',
        ];
    }
}
