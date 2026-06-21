<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class SaveAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.edit');
    }

    public function rules(): array
    {
        return [
            'class_id'   => ['required', 'string'],
            'section_id' => ['nullable', 'string'],
            'date'       => ['required', 'date', 'before_or_equal:today'],
            'statuses'   => ['required', 'array'],
            'statuses.*' => ['nullable', 'in:present,absent,late'],
        ];
    }
}
