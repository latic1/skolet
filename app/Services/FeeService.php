<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

final class FeeService
{
    /**
     * Load all fee structures applicable to a student's class and compute
     * the payment status for each item based on existing payment records.
     *
     * @return array<int, array{fee_structure: FeeStructure, paid_amount: float, outstanding: float, status: string, payments: \Illuminate\Support\Collection}>
     */
    public function getStudentFeeItems(
        Student $student,
        ?string $academicYearId = null,
        ?string $term = null
    ): array {
        $query = FeeStructure::with(['schoolClass', 'academicYear'])
            ->where('class_id', $student->class_id);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($term) {
            $query->where('term', $term);
        }

        $feeStructures = $query->orderBy('term')->orderBy('fee_item')->get();

        if ($feeStructures->isEmpty()) {
            return [];
        }

        $payments = FeePayment::where('student_id', $student->id)
            ->whereIn('fee_structure_id', $feeStructures->pluck('id'))
            ->get()
            ->groupBy('fee_structure_id');

        $items = [];
        foreach ($feeStructures as $fs) {
            $fsPayments  = $payments->get($fs->id, collect());
            $paidAmount  = (float) $fsPayments->sum('amount');
            $outstanding = max(0.0, (float) $fs->amount - $paidAmount);

            $items[] = [
                'fee_structure' => $fs,
                'paid_amount'   => $paidAmount,
                'outstanding'   => $outstanding,
                'status'        => $this->computeStatus((float) $fs->amount, $paidAmount, $fs->due_date),
                'payments'      => $fsPayments,
            ];
        }

        return $items;
    }

    public function computeStatus(float $totalAmount, float $paidAmount, ?Carbon $dueDate): string
    {
        if ($paidAmount >= $totalAmount) {
            return 'paid';
        }

        $isOverdue = $dueDate !== null && $dueDate->isPast();

        if ($paidAmount > 0) {
            return $isOverdue ? 'overdue' : 'partial';
        }

        return $isOverdue ? 'overdue' : 'unpaid';
    }

    /** @return array{success: bool, data: mixed, error: ?string} */
    public function recordCashPayment(
        Student $student,
        FeeStructure $feeStructure,
        float $amount
    ): array {
        try {
            $payment = FeePayment::create([
                'student_id'       => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'amount'           => $amount,
                'status'           => 'paid',
                'payment_method'   => 'cash',
                'paystack_ref'     => null,
                'recorded_by'      => Auth::id(),
                'paid_at'          => now(),
            ]);

            return ['success' => true, 'data' => $payment, 'error' => null];
        } catch (\Throwable $e) {
            \Log::error('[FeeService::recordCashPayment] ' . $e->getMessage());

            return ['success' => false, 'data' => null, 'error' => 'Could not record payment. Please try again.'];
        }
    }
}
