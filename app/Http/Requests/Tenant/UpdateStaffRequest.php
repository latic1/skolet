<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('staff.edit');
    }

    public function rules(): array
    {
        $userId = $this->route('staff')?->user_id;

        return [
            'full_name'            => ['required', 'string', 'max:255'],
            'phone'                => ['nullable', 'string', 'max:30'],
            'role_title'           => ['nullable', 'string', 'max:100'],
            'status'               => ['required', 'in:active,inactive'],
            'email'                => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'system_role'          => ['required', 'string', 'exists:roles,name'],
            'new_password'         => ['nullable', Password::min(8)->mixedCase()->numbers()],
            'new_password_confirmation' => ['nullable', 'required_with:new_password', 'same:new_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'                          => 'That email address is already in use by another account.',
            'system_role.exists'                    => 'The selected role does not exist.',
            'new_password_confirmation.required_with' => 'Please confirm the new password.',
        ];
    }
}
