<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

final class StudentImport implements ToCollection, WithHeadingRow
{
    public array $errors   = [];
    public int   $imported = 0;

    public function collection(Collection $rows): void
    {
        // Preload all classes with sections for efficient row-level lookup
        $classMap = SchoolClass::with('sections')
            ->get()
            ->keyBy(fn (SchoolClass $c) => strtolower(trim($c->name)));

        // Track duplicates within this batch (full_name + class_name)
        $batchKeys   = [];
        $rowsToInsert = [];

        // ── Pass 1: validate every row, collect ALL errors ────────────────────
        foreach ($rows as $index => $row) {
            $rowNum    = $index + 2; // +2: header row + 0-based index
            $rowErrors = [];

            $fullName    = trim((string) ($row['full_name']    ?? ''));
            $className   = trim((string) ($row['class_name']   ?? ''));
            $guardianName    = trim((string) ($row['guardian_name']    ?? ''));
            $guardianContact = trim((string) ($row['guardian_contact'] ?? ''));

            if ($fullName === '') {
                $rowErrors[] = 'Full Name is required.';
            }
            if ($className === '') {
                $rowErrors[] = 'Class Name is required.';
            }
            if ($guardianName === '') {
                $rowErrors[] = 'Guardian Name is required.';
            }
            if ($guardianContact === '') {
                $rowErrors[] = 'Guardian Contact is required.';
            }

            // Class existence
            $class     = null;
            $sectionId = null;
            if ($className !== '') {
                $class = $classMap->get(strtolower($className));
                if ($class === null) {
                    $rowErrors[] = "Class \"{$className}\" does not exist in the system.";
                }
            }

            // Section existence (only checked if class resolved)
            $sectionName = trim((string) ($row['section_name'] ?? ''));
            if ($class !== null && $sectionName !== '') {
                $section = $class->sections->first(
                    fn ($s) => strtolower(trim($s->name)) === strtolower($sectionName)
                );
                if ($section === null) {
                    $rowErrors[] = "Section \"{$sectionName}\" does not exist in class \"{$className}\".";
                } else {
                    $sectionId = $section->id;
                }
            }

            // Gender validation
            $gender = strtolower(trim((string) ($row['gender'] ?? '')));
            if ($gender !== '' && !in_array($gender, ['male', 'female'], true)) {
                $rowErrors[] = 'Gender must be "Male" or "Female" (or leave blank).';
            }

            // Duplicate within this import batch
            if ($fullName !== '' && $className !== '') {
                $dedupKey = strtolower("{$fullName}|{$className}");
                if (isset($batchKeys[$dedupKey])) {
                    $rowErrors[] = "Duplicate: \"{$fullName}\" in class \"{$className}\" appears more than once in this file.";
                } else {
                    $batchKeys[$dedupKey] = true;
                }
            }

            if (!empty($rowErrors)) {
                foreach ($rowErrors as $e) {
                    $this->errors[] = "Row {$rowNum}: {$e}";
                }
                continue;
            }

            // Date of birth — handle Carbon (Excel date cell) and raw strings
            $dob = $row['date_of_birth'] ?? null;
            if ($dob instanceof Carbon) {
                $dob = $dob->format('Y-m-d');
            } elseif (!empty($dob)) {
                $dob = trim((string) $dob) ?: null;
            } else {
                $dob = null;
            }

            $rowsToInsert[] = [
                'class_id'         => $class->id,
                'section_id'       => $sectionId,
                'full_name'        => $fullName,
                'date_of_birth'    => $dob,
                'gender'           => $gender ?: null,
                'guardian_name'    => $guardianName,
                'guardian_contact' => $guardianContact,
                'guardian_email'   => trim((string) ($row['guardian_email']  ?? '')) ?: null,
                'medical_notes'    => trim((string) ($row['medical_notes']   ?? '')) ?: null,
                'status'           => 'active',
            ];
        }

        // If any row failed validation, abort — nothing is imported
        if (!empty($this->errors)) {
            return;
        }

        // ── Pass 2: import all rows in a single transaction ───────────────────
        DB::transaction(function () use ($rowsToInsert): void {
            $year   = now()->year;
            $prefix = $year . '/';

            // Read the current max sequence once before the loop.
            // Querying inside the loop hits MySQL's REPEATABLE READ snapshot and
            // returns the same value every iteration, producing duplicate numbers.
            $last     = Student::where('admission_no', 'like', $prefix . '%')
                ->orderByDesc('admission_no')
                ->value('admission_no');
            $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

            foreach ($rowsToInsert as $data) {
                $data['admission_no'] = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
                Student::create($data);
                $sequence++;
                $this->imported++;
            }
        });
    }
}
