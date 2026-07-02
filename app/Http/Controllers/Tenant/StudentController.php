<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Exports\StudentImportTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CreateStudentLoginRequest;
use App\Http\Requests\Tenant\ImportStudentsRequest;
use App\Http\Requests\Tenant\StoreStudentRequest;
use App\Http\Requests\Tenant\UpdateStudentRequest;
use App\Imports\StudentImport;
use App\Jobs\ExportStudentDataJob;
use App\Jobs\SendWebhookPayload;
use App\Models\Tenant\AdmissionApplication;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Actions\SyncTenantStudentCount;
use App\Services\AdmissionNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class StudentController extends Controller
{
    public function __construct(
        private readonly AdmissionNumberService $admissionNumberService,
    ) {}
    public function index(): View
    {
        $user = Auth::user();

        $classes = SchoolClass::with('sections')
            ->when(!$user->can('settings.manage'), fn ($q) => $q->whereIn('id', $user->staffAssignedClassIds()))
            ->orderBy('order')->orderBy('name')->get();
        $anyClassHasSections = $classes->some(fn ($c) => $c->sections->isNotEmpty());

        $query = Student::with(['schoolClass', 'section'])->visibleTo($user)->orderBy('full_name');

        if (request()->filled('class_id')) {
            $query->where('class_id', request('class_id'));
        }

        if (request()->filled('section_id')) {
            $query->where('section_id', request('section_id'));
        }

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(25)->withQueryString();

        return view('tenant.students.index', compact('students', 'classes', 'anyClassHasSections'));
    }

    public function create(Request $request): View
    {
        $classes = SchoolClass::with('sections')->orderBy('order')->orderBy('name')->get();
        $classesJson = $classes->map(fn ($c) => [
            'id'       => $c->id,
            'name'     => $c->name,
            'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
        ])->values()->toArray();

        $application    = null;
        $prefill        = [];

        if ($request->filled('from_application')) {
            $application = AdmissionApplication::find($request->input('from_application'));

            if ($application && $application->isPending()) {
                $prefill = [
                    'full_name'        => $application->applicant_name,
                    'date_of_birth'    => $application->date_of_birth?->format('Y-m-d'),
                    'gender'           => $application->gender,
                    'guardian_name'    => $application->guardian_name,
                    'guardian_contact' => $application->guardian_contact,
                    'guardian_email'   => $application->guardian_email,
                ];
            }
        }

        return view('tenant.students.create', compact('classes', 'classesJson', 'application', 'prefill'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        try {
            $data = collect($request->validated())->except('photo')->toArray();
            $data['status']       = $data['status'] ?? 'active';
            $data['admission_no'] = $this->admissionNumberService->generate();

            $student = Student::create($data);

            if ($request->hasFile('photo')) {
                $student->update(['photo_path' => $this->storeStudentPhoto($request, $student->id)]);
            }

            SyncTenantStudentCount::run();

            SendWebhookPayload::dispatch(tenant('id'), 'student_enrolled', [
                'event'        => 'student_enrolled',
                'tenant'       => tenant('id'),
                'timestamp'    => now()->toIso8601String(),
                'data'         => [
                    'student_id'   => $student->id,
                    'full_name'    => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'class_id'     => $student->class_id,
                ],
            ]);

            // If this student was created from an admission application, mark it accepted
            if ($request->filled('from_application_id')) {
                $application = AdmissionApplication::find($request->input('from_application_id'));
                if ($application && $application->isPending()) {
                    $application->update([
                        'status'      => 'accepted',
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                    ]);
                }
            }

            return redirect(request()->getSchemeAndHttpHost() . '/students/create')
                ->with('success', "Student \"{$student->full_name}\" added — Adm. No. {$student->admission_no}.");
        } catch (\Throwable $e) {
            \Log::error('[students.store] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not add student. Please try again.');
        }
    }

    public function show(Student $student): View
    {
        abort_unless(Student::visibleTo(Auth::user())->whereKey($student->id)->exists(), 403);

        $student->load(['schoolClass', 'section', 'user', 'parents']);

        $disciplinaryRecords = $student->disciplinaryRecords()->with('reportedBy')->get();

        $feeDiscounts = $student->feeDiscounts()
            ->with(['feeStructure', 'approver'])
            ->orderByDesc('created_at')
            ->get();

        // Fee structures applicable to this student (for the discount "Applies To" select)
        $studentFeeStructures = FeeStructure::where(function ($q) use ($student): void {
            $q->where('target_class', 'all')
              ->orWhere('target_class', $student->class_id);
        })->orderBy('fee_item')->get(['id', 'fee_item', 'amount']);

        $hasPublishedResults = ExamResult::where('student_id', $student->id)
            ->whereHas('exam', fn ($q) => $q->where('is_published', true))
            ->exists();

        $user = Auth::user();
        $canDownloadTranscript = $hasPublishedResults && (
            $user->can('students.view') ||
            ($student->user_id && $student->user_id === $user->id) ||
            $student->parents->contains('id', $user->id)
        );

        $photoUrl = $this->photoUrl($student);

        return view('tenant.students.show', compact(
            'student', 'disciplinaryRecords', 'feeDiscounts', 'studentFeeStructures', 'canDownloadTranscript', 'photoUrl'
        ));
    }

    public function edit(Student $student): View
    {
        $student->load(['schoolClass', 'section']);
        $classes = SchoolClass::with('sections')->orderBy('order')->orderBy('name')->get();
        $classesJson = $classes->map(fn ($c) => [
            'id'       => $c->id,
            'name'     => $c->name,
            'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
        ])->values()->toArray();
        $photoUrl = $this->photoUrl($student);

        return view('tenant.students.edit', compact('student', 'classes', 'classesJson', 'photoUrl'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        try {
            $data = collect($request->validated())->except('photo')->toArray();

            if ($request->hasFile('photo')) {
                if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
                    Storage::disk('public')->delete($student->photo_path);
                }
                $data['photo_path'] = $this->storeStudentPhoto($request, $student->id);
            }

            $student->update($data);

            if ($student->wasChanged('status')) {
                SyncTenantStudentCount::run();
            }

            return redirect(request()->getSchemeAndHttpHost() . '/students/' . $student->id)
                ->with('success', 'Student updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('[students.update] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not update student. Please try again.');
        }
    }

    public function photo(Student $student): BinaryFileResponse
    {
        abort_unless(Student::visibleTo(Auth::user())->whereKey($student->id)->exists(), 403);

        if (!$student->photo_path || !Storage::disk('public')->exists($student->photo_path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('public')->path($student->photo_path),
            ['Cache-Control' => 'private, max-age=3600']
        );
    }

    public function destroy(Student $student): RedirectResponse
    {
        try {
            $student->delete();

            SyncTenantStudentCount::run();

            return redirect(request()->getSchemeAndHttpHost() . '/students')
                ->with('success', $student->full_name . ' moved to trash.');
        } catch (\Throwable $e) {
            \Log::error('[students.destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not remove student. Please try again.');
        }
    }

    public function trash(): View
    {
        $students = Student::onlyTrashed()->with('schoolClass')->latest('deleted_at')->get();

        return view('tenant.students.trash', compact('students'));
    }

    public function restore(string $id): RedirectResponse
    {
        try {
            $student = Student::onlyTrashed()->findOrFail($id);
            $student->restore();

            SyncTenantStudentCount::run();

            return redirect(request()->getSchemeAndHttpHost() . '/students')
                ->with('success', $student->full_name . ' restored successfully.');
        } catch (\Throwable $e) {
            \Log::error('[students.restore] ' . $e->getMessage());

            return back()->with('error', 'Could not restore student. Please try again.');
        }
    }

    public function forceDelete(string $id): RedirectResponse
    {
        try {
            $student = Student::onlyTrashed()->findOrFail($id);
            $student->forceDelete();

            return redirect(request()->getSchemeAndHttpHost() . '/students/trash')
                ->with('success', 'Student permanently deleted.');
        } catch (\Throwable $e) {
            \Log::error('[students.forceDelete] ' . $e->getMessage());

            return back()->with('error', 'Could not permanently delete student. Please try again.');
        }
    }

    public function anonymize(Student $student): RedirectResponse
    {
        try {
            if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
                Storage::disk('public')->delete($student->photo_path);
            }

            $student->update([
                'full_name'       => 'Deleted Student',
                'guardian_name'   => 'Removed',
                'guardian_contact' => '000',
                'guardian_email'  => null,
                'photo_path'      => null,
                'address'         => null,
                'medical_notes'   => null,
            ]);

            return redirect(request()->getSchemeAndHttpHost() . '/students/' . $student->id)
                ->with('success', 'Student personal data anonymised. Academic records preserved.');
        } catch (\Throwable $e) {
            \Log::error('[students.anonymize] ' . $e->getMessage());

            return back()->with('error', 'Could not anonymise student. Please try again.');
        }
    }

    public function exportData(Request $request, Student $student): RedirectResponse
    {
        $user = Auth::user();

        ExportStudentDataJob::dispatch(
            studentId: $student->id,
            tenantId: tenant()->getTenantKey(),
            tenantHost: $request->getSchemeAndHttpHost(),
            adminEmail: $user->email,
            adminName: $user->name,
        );

        return back()->with('success', 'Export requested. We will email you at ' . $user->email . ' when it\'s ready.');
    }

    public function import(ImportStudentsRequest $request): RedirectResponse
    {
        try {
            $import = new StudentImport();
            Excel::import($import, $request->file('import_file'));

            if (!empty($import->errors)) {
                return back()->with('student_import_errors', $import->errors);
            }

            SyncTenantStudentCount::run();

            return redirect(request()->getSchemeAndHttpHost() . '/students')
                ->with('success', "{$import->imported} student" . ($import->imported !== 1 ? 's' : '') . ' imported successfully.');
        } catch (\Throwable $e) {
            \Log::error('[students.import] ' . $e->getMessage());

            return back()->with('error', 'Could not process the file. Make sure it matches the template format.');
        }
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new StudentImportTemplate(), 'skolet-students-import-template.xlsx');
    }

    public function createLogin(CreateStudentLoginRequest $request, Student $student): RedirectResponse
    {
        if ($student->user_id) {
            return back()->with('error', 'This student already has a login account.');
        }

        try {
            DB::transaction(function () use ($request, $student): void {
                $data = $request->validated();
                $user = User::create([
                    'name'     => $student->full_name,
                    'email'    => $data['email'],
                    'password' => $data['password'],
                ]);
                $user->assignRole($data['role']);
                $student->update(['user_id' => $user->id]);
            });

            return redirect(request()->getSchemeAndHttpHost() . '/students/' . $student->id)
                ->with('success', 'Login account created for ' . $student->full_name . '.');
        } catch (\Throwable $e) {
            \Log::error('[students.createLogin] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not create login account. Please try again.');
        }
    }

    public function revokeLogin(Student $student): RedirectResponse
    {
        if (!$student->user_id) {
            return back()->with('error', 'This student has no login account to revoke.');
        }

        try {
            DB::transaction(function () use ($student): void {
                $user = $student->user;
                $student->update(['user_id' => null]);
                $user->delete();
            });

            return redirect(request()->getSchemeAndHttpHost() . '/students/' . $student->id)
                ->with('success', 'Login access revoked for ' . $student->full_name . '.');
        } catch (\Throwable $e) {
            \Log::error('[students.revokeLogin] ' . $e->getMessage());

            return back()->with('error', 'Could not revoke login access. Please try again.');
        }
    }

    private function storeStudentPhoto(Request $request, string $studentId): string
    {
        $tenantId = tenant('id');
        $ext      = $request->file('photo')->getClientOriginalExtension();

        return $request->file('photo')->storeAs(
            "student-photos/{$tenantId}/{$studentId}",
            "photo.{$ext}",
            'public'
        );
    }

    private function photoUrl(Student $student): ?string
    {
        return $student->photo_path && Storage::disk('public')->exists($student->photo_path)
            ? request()->getSchemeAndHttpHost() . '/students/' . $student->id . '/photo'
            : null;
    }

}
