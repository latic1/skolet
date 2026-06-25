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
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Services\AdmissionNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        $classes = SchoolClass::with('sections')->orderBy('order')->orderBy('name')->get();
        $anyClassHasSections = $classes->some(fn ($c) => $c->sections->isNotEmpty());

        $query = Student::with(['schoolClass', 'section'])->orderBy('full_name');

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

    public function create(): View
    {
        $classes = SchoolClass::with('sections')->orderBy('order')->orderBy('name')->get();
        $classesJson = $classes->map(fn ($c) => [
            'id'       => $c->id,
            'name'     => $c->name,
            'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
        ])->values()->toArray();

        return view('tenant.students.create', compact('classes', 'classesJson'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['admission_no'] = $this->admissionNumberService->generate();

            $student = Student::create($data);

            return redirect(request()->getSchemeAndHttpHost() . '/students/create')
                ->with('success', "Student \"{$student->full_name}\" added — Adm. No. {$student->admission_no}.");
        } catch (\Throwable $e) {
            \Log::error('[students.store] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not add student. Please try again.');
        }
    }

    public function show(Student $student): View
    {
        $student->load(['schoolClass', 'section', 'user', 'parents']);

        $disciplinaryRecords = $student->disciplinaryRecords()->with('reportedBy')->get();

        return view('tenant.students.show', compact('student', 'disciplinaryRecords'));
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

        return view('tenant.students.edit', compact('student', 'classes', 'classesJson'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        try {
            $student->update($request->validated());

            return redirect(request()->getSchemeAndHttpHost() . '/students/' . $student->id)
                ->with('success', 'Student updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('[students.update] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not update student. Please try again.');
        }
    }

    public function destroy(Student $student): RedirectResponse
    {
        try {
            $student->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/students')
                ->with('success', 'Student removed successfully.');
        } catch (\Throwable $e) {
            \Log::error('[students.destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not remove student. Please try again.');
        }
    }

    public function import(ImportStudentsRequest $request): RedirectResponse
    {
        try {
            $import = new StudentImport();
            Excel::import($import, $request->file('import_file'));

            if (!empty($import->errors)) {
                return back()->with('student_import_errors', $import->errors);
            }

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

}
