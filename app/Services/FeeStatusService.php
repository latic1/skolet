<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\FeeDiscount;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Models\Tenant\Term;
use App\Notifications\PaymentConfirmation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

final class FeeStatusService
{
    /**
     * Load all fee structures applicable to a student's class and compute
     * the payment status for each item based on existing payment records.
     * Active fee discounts are applied to reduce the effective amount.
     *
     * When $termId is provided: returns per-term fees for that term
     * plus annual fees for that term's academic year.
     *
     * When $termId is null: returns all per-term fees plus annual fees
     * for the current academic year.
     *
     * @return array<int, array{fee_structure: FeeStructure, original_amount: float, effective_amount: float, has_discount: bool, discounts: \Illuminate\Support\Collection, paid_amount: float, outstanding: float, status: string, payments: \Illuminate\Support\Collection}>
     */
    public function getStudentFeeItems(
        Student $student,
        ?string $termId = null
    ): array {
        $academicYearId = null;

        if ($termId) {
            $term           = Term::find($termId);
            $academicYearId = $term?->academic_year_id;
        } else {
            $currentYear    = AcademicYear::where('is_current', true)->first();
            $academicYearId = $currentYear?->id;
        }

        $query = FeeStructure::with(['term.academicYear', 'academicYear', 'bundle'])
            ->where(function ($q) use ($student): void {
                $q->where('target_class', 'all')
                  ->orWhere('target_class', $student->class_id);
            });

        if ($termId) {
            // Include per-term fees for this specific term AND annual fees for its academic year
            $query->where(function ($q) use ($termId, $academicYearId): void {
                $q->where(function ($q2) use ($termId): void {
                    $q2->where('billing_cycle', 'term')->where('term_id', $termId);
                });
                if ($academicYearId) {
                    $q->orWhere(function ($q2) use ($academicYearId): void {
                        $q2->where('billing_cycle', 'annual')
                           ->where('academic_year_id', $academicYearId);
                    });
                }
                // Also include legacy rows with no billing_cycle set (term_id matches)
                $q->orWhere(function ($q2) use ($termId): void {
                    $q2->whereNull('billing_cycle')->where('term_id', $termId);
                });
            });
        } elseif ($academicYearId) {
            // No specific term: include all per-term fees and annual fees for the current year
            $query->where(function ($q) use ($academicYearId): void {
                $q->where('billing_cycle', 'term')
                  ->orWhereNull('billing_cycle')
                  ->orWhere(function ($q2) use ($academicYearId): void {
                      $q2->where('billing_cycle', 'annual')
                         ->where('academic_year_id', $academicYearId);
                  });
            });
        }

        $feeStructures = $query->orderBy('fee_item')->get();

        if ($feeStructures->isEmpty()) {
            return [];
        }

        $payments = FeePayment::where('student_id', $student->id)
            ->whereIn('fee_structure_id', $feeStructures->pluck('id'))
            ->get()
            ->groupBy('fee_structure_id');

        // Load all active discounts for this student in one query
        $today = today()->toDateString();
        $allDiscounts = FeeDiscount::where('student_id', $student->id)
            ->where(function ($q) use ($today): void {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today): void {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
            })
            ->get();

        // Blanket discounts (apply to all fee items)
        $blanketDiscounts = $allDiscounts->whereNull('fee_structure_id');

        $items = [];
        foreach ($feeStructures as $fs) {
            $fsPayments = $payments->get($fs->id, collect());
            $paidAmount = (float) $fsPayments->sum('amount');

            // Specific discounts for this fee structure + blanket discounts
            $specificDiscounts = $allDiscounts->where('fee_structure_id', $fs->id);
            $applicableDiscounts = $blanketDiscounts->merge($specificDiscounts)->values();

            // Apply all applicable discounts (each reduces from the original amount)
            $originalAmount = (float) $fs->amount;
            $reduction = 0.0;
            foreach ($applicableDiscounts as $discount) {
                if ($discount->discount_type === 'percentage') {
                    $reduction += $originalAmount * ((float) $discount->discount_value / 100);
                } else {
                    $reduction += (float) $discount->discount_value;
                }
            }
            $effectiveAmount = max(0.0, $originalAmount - $reduction);
            $outstanding     = max(0.0, $effectiveAmount - $paidAmount);

            $items[] = [
                'fee_structure'    => $fs,
                'original_amount'  => $originalAmount,
                'effective_amount' => $effectiveAmount,
                'has_discount'     => $applicableDiscounts->isNotEmpty(),
                'discounts'        => $applicableDiscounts,
                'paid_amount'      => $paidAmount,
                'outstanding'      => $outstanding,
                'status'           => $this->computeStatus($effectiveAmount, $paidAmount, $fs->due_date),
                'payments'         => $fsPayments,
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
                'payment_method'   => 'cash',
                'paystack_ref'     => null,
                'recorded_by'      => Auth::id(),
                'paid_at'          => now(),
            ]);

            $profile = SchoolProfile::first();
            if ($profile?->isNotificationEnabled('payment_confirmation') && $student->guardian_email) {
                Notification::route('mail', $student->guardian_email)
                    ->notify(new PaymentConfirmation($payment));
            }

            return ['success' => true, 'data' => $payment, 'error' => null];
        } catch (\Throwable $e) {
            Log::error('[FeeStatusService::recordCashPayment] ' . $e->getMessage());

            return ['success' => false, 'data' => null, 'error' => 'Could not record payment. Please try again.'];
        }
    }
}
