<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class CreateStudentLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('students.edit');
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:student,parent'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already in use by another account.',
        ];
    }
}
