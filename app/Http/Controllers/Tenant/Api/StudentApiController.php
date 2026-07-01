<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AttendanceResource;
use App\Http\Resources\Api\ExamResultResource;
use App\Http\Resources\Api\StudentResource;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class StudentApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $students = Student::with(['schoolClass', 'section'])
            ->visibleTo($request->user())
            ->when($request->input('class_id'), fn ($q, $v) => $q->where('class_id', $v))
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('search'), function ($q, $s) {
                $q->where(function ($q2) use ($s) {
                    $q2->where('full_name', 'like', "%{$s}%")
                       ->orWhere('admission_no', 'like', "%{$s}%");
                });
            })
            ->orderBy('full_name')
            ->paginate(50);

        return StudentResource::collection($students);
    }

    public function show(Student $student): StudentResource|JsonResponse
    {
        if (! Student::visibleTo(auth()->user())->whereKey($student->id)->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $student->load(['schoolClass', 'section']);

        return new StudentResource($student);
    }

    public function attendance(Request $request, Student $student): AnonymousResourceCollection|JsonResponse
    {
        if (! auth()->user()->can('attendance.view')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $records = Attendance::where('student_id', $student->id)
            ->when($request->input('from'), fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($request->input('to'), fn ($q, $v) => $q->whereDate('date', '<=', $v))
            ->orderByDesc('date')
            ->paginate(100);

        return AttendanceResource::collection($records);
    }

    public function exams(Student $student): AnonymousResourceCollection|JsonResponse
    {
        if (! auth()->user()->can('exams.view')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $results = ExamResult::with(['exam.term', 'subject'])
            ->where('student_id', $student->id)
            ->whereHas('exam', fn ($q) => $q->where('status', 'published'))
            ->get();

        return ExamResultResource::collection($results);
    }
}
