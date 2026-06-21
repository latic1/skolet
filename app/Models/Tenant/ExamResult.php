<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ExamResult extends Model
{
    use HasUuids;

    protected $fillable = [
        'exam_id',
        'student_id',
        'subject_id',
        'marks',
        'grade',
        'remarks',
    ];

    protected $casts = [
        'marks' => 'float',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public static function computeGrade(float $marks): string
    {
        return match (true) {
            $marks >= 70 => 'A',
            $marks >= 60 => 'B',
            $marks >= 50 => 'C',
            $marks >= 40 => 'D',
            default      => 'F',
        };
    }
}
