<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'admission_no'     => $this->admission_no,
            'full_name'        => $this->full_name,
            'date_of_birth'    => $this->date_of_birth?->toDateString(),
            'gender'           => $this->gender,
            'class_id'         => $this->class_id,
            'class_name'       => $this->schoolClass?->name,
            'section_id'       => $this->section_id,
            'section_name'     => $this->section?->name,
            'guardian_name'    => $this->guardian_name,
            'guardian_contact' => $this->guardian_contact,
            'guardian_email'   => $this->guardian_email,
            'status'           => $this->status,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
