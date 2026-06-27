<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FeeStatusResource;
use App\Models\Tenant\Student;
use App\Services\FeeStatusService;
use Illuminate\Http\JsonResponse;

final class FeeApiController extends Controller
{
    public function __construct(private readonly FeeStatusService $feeStatusService) {}

    public function show(Student $student): JsonResponse
    {
        try {
            $items = $this->feeStatusService->getStudentFeeItems($student);

            $resources = array_map(fn ($item) => (new FeeStatusResource($item))->resolve(), $items);

            return response()->json([
                'data'       => $resources,
                'student_id' => $student->id,
                'full_name'  => $student->full_name,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to load fee data.'], 500);
        }
    }
}
