<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AttendanceResource;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class AttendanceApiController extends Controller
{
    /**
     * Mark attendance for multiple students on a given date.
     * Designed for biometric gate integration.
     *
     * Expected payload:
     * {
     *   "date": "2026-06-26",
     *   "records": [
     *     { "student_id": "uuid", "status": "present|absent|late" },
     *     ...
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        // Read-only tokens cannot perform write operations
        if (! $request->user()->tokenCan('write')) {
            return response()->json(['message' => 'This token has read-only scope.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'date'               => ['required', 'date', 'before_or_equal:today'],
            'records'            => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'uuid', 'exists:students,id'],
            'records.*.status'   => ['required', 'in:present,absent,late'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data      = $validator->validated();
        $date      = $data['date'];
        $markedBy  = auth()->id();
        $saved     = 0;
        $errors    = [];

        foreach ($data['records'] as $record) {
            try {
                Attendance::updateOrCreate(
                    ['student_id' => $record['student_id'], 'date' => $date],
                    ['status' => $record['status'], 'marked_by' => $markedBy]
                );
                $saved++;
            } catch (\Throwable $e) {
                $errors[] = ['student_id' => $record['student_id'], 'error' => 'Failed to save.'];
            }
        }

        return response()->json([
            'message' => "Attendance recorded for {$saved} student(s).",
            'saved'   => $saved,
            'errors'  => $errors,
        ], 201);
    }
}
