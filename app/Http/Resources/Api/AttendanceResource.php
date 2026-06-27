<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'student_id' => $this->student_id,
            'date'       => $this->date?->toDateString(),
            'status'     => $this->status,
            'note'       => $this->note,
            'marked_by'  => $this->marked_by,
        ];
    }
}
