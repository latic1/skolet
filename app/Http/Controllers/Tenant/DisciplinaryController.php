<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\DisciplinaryRecord;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Notifications\DisciplinaryIncidentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class DisciplinaryController extends Controller
{
    public function index(Request $request): View
    {
        $query = DisciplinaryRecord::with(['student.schoolClass', 'reportedBy'])->latest('date');

        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->class_id));
        }

        if ($request->filled('incident_type')) {
            $query->where('incident_type', $request->incident_type);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $records  = $query->paginate(25)->withQueryString();
        $classes  = SchoolClass::orderBy('order')->get();
        $students = Student::orderBy('full_name')->get(['id', 'full_name', 'admission_no', 'class_id']);

        return view('tenant.behavior.index', compact('records', 'classes', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'    => ['required', 'uuid', 'exists:students,id'],
            'incident_type' => ['required', 'in:warning,detention,suspension,expulsion,commendation'],
            'description'   => ['required', 'string', 'max:2000'],
            'action_taken'  => ['nullable', 'string', 'max:1000'],
            'date'          => ['required', 'date'],
            'parent_notified' => ['boolean'],
        ]);

        $data['reported_by']      = Auth::id();
        $data['parent_notified']  = $request->boolean('parent_notified');

        $record = DisciplinaryRecord::create($data);

        if ($record->parent_notified) {
            $record->load('student.schoolClass');
            $guardianEmail = $record->student->guardian_email;

            if ($guardianEmail) {
                (new AnonymousNotifiable())
                    ->route('mail', $guardianEmail)
                    ->notify(new DisciplinaryIncidentNotification($record));
            }
        }

        return back()->with('success', 'Incident logged successfully.');
    }

    public function destroy(DisciplinaryRecord $disciplinaryRecord): RedirectResponse
    {
        $disciplinaryRecord->delete();

        return back()->with('success', 'Incident record deleted.');
    }
}
