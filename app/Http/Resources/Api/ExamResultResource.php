<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'exam_id'      => $this->exam_id,
            'exam_name'    => $this->exam?->name,
            'term'         => $this->exam?->term?->name,
            'subject_id'   => $this->subject_id,
            'subject_name' => $this->subject?->name,
            'marks'        => $this->marks,
            'grade'        => $this->grade,
            'remarks'      => $this->remarks,
        ];
    }
}
