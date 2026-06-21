<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('announcements.create');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title'     => ['required', 'string', 'max:150'],
            'body'      => ['required', 'string', 'max:5000'],
            'is_public' => ['boolean'],
        ];
    }
}
