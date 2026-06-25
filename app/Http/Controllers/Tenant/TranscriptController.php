<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Student;
use App\Services\ReportCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class TranscriptController extends Controller
{
    public function __construct(
        private readonly ReportCardService $reportCardService,
    ) {}

    public function download(Student $student): BinaryFileResponse|RedirectResponse
    {
        $user = Auth::user();

        $isAdmin     = $user->can('students.view');
        $isOwn       = $student->user_id && $student->user_id === $user->id;
        $isParent    = !$isAdmin && !$isOwn && $student->parents()->where('user_id', $user->id)->exists();

        abort_unless($isAdmin || $isOwn || $isParent, 403);

        try {
            $path     = $this->reportCardService->generateTranscript($student);
            $filename = Str::slug($student->full_name) . '-transcript.pdf';

            return response()->download($path, $filename, ['Content-Type' => 'application/pdf']);
        } catch (\Throwable $e) {
            \Log::error('[TranscriptController.download] ' . $e->getMessage(), [
                'student_id' => $student->id,
            ]);

            return back()->with('error', 'Could not generate transcript. Please try again.');
        }
    }
}
