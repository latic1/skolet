<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RecordPaymentRequest;
use App\Http\Requests\Tenant\StoreFeeStructureRequest;
use App\Http\Requests\Tenant\UpdateFeeStructureRequest;
use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Models\Tenant\Term;
use App\Jobs\SendWebhookPayload;
use App\Services\FeeStatusService;
use App\Services\PaystackService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class FeeController extends Controller
{
    public function __construct(
        private readonly FeeStatusService $feeStatusService,
        private readonly PaystackService $paystackService,
    ) {}

    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole('parent')) {
            return redirect($request->getSchemeAndHttpHost() . '/my-children');
        }

        if ($user->can('fees.view')) {
            return $this->adminView($request);
        }

        return $this->studentSelfView();
    }

    // -------------------------------------------------------------------------
    // Fee Structure CRUD (admin only — gated by fees.create / fees.edit / fees.delete at route level)
    // -------------------------------------------------------------------------

    public function store(StoreFeeStructureRequest $request): RedirectResponse
    {
        try {
            $data          = $request->validated();
            $targetClasses = $data['target_classes'];
            unset($data['target_classes']);

            foreach ($targetClasses as $class) {
                FeeStructure::create(array_merge($data, ['target_class' => $class]));
            }

            $count = count($targetClasses);
            $msg   = $count > 1
                ? "Fee item added for {$count} classes."
                : 'Fee item added successfully.';

            return redirect(request()->getSchemeAndHttpHost() . '/fees?tab=structure')
                ->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('[FeeController::store] ' . $e->getMessage());

            return back()->with('error', 'Could not add fee item. Please try again.')->withInput();
        }
    }

    public function update(UpdateFeeStructureRequest $request, FeeStructure $feeStructure): RedirectResponse
    {
        try {
            $feeStructure->update($request->validated());

            return redirect(request()->getSchemeAndHttpHost() . '/fees?tab=structure')
                ->with('success', 'Fee item updated successfully.');
        } catch (\Throwable $e) {
            Log::error('[FeeController::update] ' . $e->getMessage());

            return back()->with('error', 'Could not update fee item. Please try again.')->withInput();
        }
    }

    public function destroy(FeeStructure $feeStructure): RedirectResponse
    {
        try {
            $feeStructure->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/fees?tab=structure')
                ->with('success', 'Fee item deleted.');
        } catch (\Throwable $e) {
            Log::error('[FeeController::destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not delete fee item. Please try again.');
        }
    }

    // -------------------------------------------------------------------------
    // Cash payment recording (accountant/admin — gated by fees.create at route level)
    // -------------------------------------------------------------------------

    public function pay(RecordPaymentRequest $request): RedirectResponse
    {
        $student      = Student::findOrFail($request->validated()['student_id']);
        $feeStructure = FeeStructure::findOrFail($request->validated()['fee_structure_id']);
        $amount       = (float) $request->validated()['amount'];

        $feeItems     = $this->feeStatusService->getStudentFeeItems($student, $feeStructure->term_id);
        $matchingItem = collect($feeItems)->firstWhere('fee_structure.id', $feeStructure->id);
        $outstanding  = $matchingItem ? $matchingItem['outstanding'] : (float) $feeStructure->amount;

        if ($amount > $outstanding && $outstanding > 0) {
            $amount = $outstanding;
        }

        $result = $this->feeStatusService->recordCashPayment($student, $feeStructure, $amount);

        $host      = request()->getSchemeAndHttpHost();
        $returnUrl = $host . '/fees?student_id=' . $student->id . '&term_id=' . $feeStructure->term_id;

        if ($result['success']) {
            SendWebhookPayload::dispatch(tenant('id'), 'payment_received', [
                'event'     => 'payment_received',
                'tenant'    => tenant('id'),
                'timestamp' => now()->toIso8601String(),
                'data'      => [
                    'student_id'     => $student->id,
                    'student_name'   => $student->full_name,
                    'amount'         => $amount,
                    'payment_method' => 'cash',
                    'fee_structure'  => $feeStructure->name ?? null,
                ],
            ]);

            return redirect($returnUrl)->with('success', 'Payment of ' . number_format($amount, 2) . ' recorded for ' . $student->full_name . '.');
        }

        return redirect($returnUrl)->with('error', $result['error']);
    }

    // -------------------------------------------------------------------------
    // Paystack online payment — initiate checkout
    // -------------------------------------------------------------------------

    public function paystackCheckout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id'       => ['required', 'uuid', 'exists:students,id'],
            'fee_structure_id' => ['required', 'uuid', 'exists:fee_structures,id'],
        ]);

        $student      = Student::with('user')->findOrFail($validated['student_id']);
        $feeStructure = FeeStructure::findOrFail($validated['fee_structure_id']);

        $feeItems     = $this->feeStatusService->getStudentFeeItems($student, $feeStructure->term_id);
        $matchingItem = collect($feeItems)->firstWhere('fee_structure.id', $feeStructure->id);
        $outstanding  = $matchingItem ? $matchingItem['outstanding'] : (float) $feeStructure->amount;

        if ($outstanding <= 0) {
            return back()->with('info', 'This fee item is already fully paid.');
        }

        $email = $student->user?->email ?? Auth::user()->email;

        $callbackUrl = request()->getSchemeAndHttpHost() . '/paystack/callback';

        $result = $this->paystackService->initializeTransaction(
            email: $email,
            amount: $outstanding,
            callbackUrl: $callbackUrl,
            metadata: [
                'student_id'       => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'student_name'     => $student->full_name,
                'fee_item'         => $feeStructure->fee_item,
            ]
        );

        if (! $result['success']) {
            return back()->with('error', $result['error'] ?? 'Could not initialize payment. Please try again.');
        }

        return redirect()->away($result['authorization_url']);
    }

    // -------------------------------------------------------------------------
    // Paystack online payment — callback after redirect from Paystack checkout
    // -------------------------------------------------------------------------

    public function paystackCallback(Request $request): RedirectResponse
    {
        $reference = $request->input('reference', $request->input('trxref', ''));
        $host      = request()->getSchemeAndHttpHost();

        if (! $reference) {
            return redirect($host . '/fees')->with('error', 'No payment reference received.');
        }

        if (FeePayment::where('paystack_ref', $reference)->exists()) {
            return redirect($host . '/fees')->with('success', 'Payment confirmed successfully.');
        }

        $verification = $this->paystackService->verifyTransaction($reference);

        if (! $verification['success']) {
            return redirect($host . '/fees')->with('error', $verification['error'] ?? 'Payment could not be verified.');
        }

        $metadata       = $verification['metadata'];
        $studentId      = $metadata['student_id'] ?? null;
        $feeStructureId = $metadata['fee_structure_id'] ?? null;

        if (! $studentId || ! $feeStructureId) {
            return redirect($host . '/fees')->with('error', 'Payment metadata missing — contact support with reference: ' . $reference);
        }

        $student      = Student::find($studentId);
        $feeStructure = FeeStructure::find($feeStructureId);

        if (! $student || ! $feeStructure) {
            return redirect($host . '/fees')->with('error', 'Student or fee item not found for this payment.');
        }

        try {
            FeePayment::create([
                'student_id'       => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'amount'           => $verification['amount'],
                'payment_method'   => 'paystack',
                'paystack_ref'     => $reference,
                'recorded_by'      => null,
                'paid_at'          => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[FeeController::paystackCallback] ' . $e->getMessage());

            return redirect($host . '/fees')->with('error', 'Could not record payment. Contact support with reference: ' . $reference);
        }

        return redirect($host . '/fees')
            ->with('success', 'Payment of ' . number_format($verification['amount'], 2) . ' recorded for ' . $student->full_name . '.');
    }

    // -------------------------------------------------------------------------
    // Term Bill PDF
    // -------------------------------------------------------------------------

    public function printBill(Request $request, Student $student): SymfonyResponse
    {
        abort_unless($request->user()->can('fees.view'), 403);

        $termId      = $request->input('term_id');
        $term        = $termId ? Term::with('academicYear')->find($termId) : null;
        $feeItems    = $this->feeStatusService->getStudentFeeItems($student, $termId);

        // Arrears: outstanding from per-term fees of previous terms in the same academic year
        $arrearsTotal = 0.0;
        if ($term?->academic_year_id) {
            $prevStructures = FeeStructure::where(function ($q) use ($student): void {
                $q->where('target_class', 'all')
                  ->orWhere('target_class', $student->class_id);
            })
            ->where(function ($q): void {
                $q->where('billing_cycle', 'term')->orWhereNull('billing_cycle');
            })
            ->whereHas('term', function ($q) use ($term): void {
                $q->where('academic_year_id', $term->academic_year_id)
                  ->where('id', '!=', $term->id);
            })
            ->get();

            foreach ($prevStructures as $fs) {
                $paid          = (float) FeePayment::where('student_id', $student->id)
                    ->where('fee_structure_id', $fs->id)
                    ->sum('amount');
                $arrearsTotal += max(0.0, (float) $fs->amount - $paid);
            }
        }

        $student->loadMissing(['schoolClass', 'section']);

        $schoolProfile = SchoolProfile::first();
        $logoBase64    = null;
        if ($schoolProfile?->logo_path) {
            try {
                $content = Storage::disk('public')->get($schoolProfile->logo_path);
                if ($content) {
                    $ext        = strtolower(pathinfo($schoolProfile->logo_path, PATHINFO_EXTENSION));
                    $mime       = match ($ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png'         => 'image/png',
                        'gif'         => 'image/gif',
                        'webp'        => 'image/webp',
                        'svg'         => 'image/svg+xml',
                        default       => 'image/png',
                    };
                    $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($content);
                }
            } catch (\Throwable) {
                // Not critical — bill generates without logo
            }
        }

        $data = compact('student', 'term', 'feeItems', 'arrearsTotal', 'schoolProfile', 'logoBase64');

        $pdf         = Pdf::loadView('tenant.fees.term-bill-pdf', $data)->setPaper('a4', 'portrait');
        $safeName    = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->full_name);
        $safeTerm    = $term ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $term->name) : 'all';

        return $pdf->stream("bill-{$safeName}-{$safeTerm}.pdf");
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function adminView(Request $request): View
    {
        $feeStructures = FeeStructure::with(['term.academicYear', 'academicYear'])
            ->orderBy('billing_cycle')
            ->orderBy('fee_item')
            ->get();

        $classes = SchoolClass::orderBy('order')->get();
        $terms   = Term::with('academicYear')->orderByDesc('id')->get();

        $currentYear      = AcademicYear::where('is_current', true)->first();
        $currentYearTerms = $currentYear
            ? Term::where('academic_year_id', $currentYear->id)->orderBy('id')->get()
            : collect();
        $currentTerm = Term::where('is_current', true)->first();

        $searchQuery  = $request->input('search', '');
        $studentId    = $request->input('student_id');
        $filterTermId = $request->input('term_id');

        $searchResults   = collect();
        $selectedStudent = null;
        $feeItems        = [];

        if ($studentId) {
            $selectedStudent = Student::with(['schoolClass', 'section'])->find($studentId);

            if ($selectedStudent) {
                $feeItems = $this->feeStatusService->getStudentFeeItems(
                    $selectedStudent,
                    $filterTermId ?: null
                );
            }
        } elseif ($searchQuery !== '') {
            $searchResults = Student::with('schoolClass')
                ->where(function ($q) use ($searchQuery) {
                    $q->where('full_name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('admission_no', 'like', '%' . $searchQuery . '%');
                })
                ->orderBy('full_name')
                ->limit(20)
                ->get();
        }

        $activeTab = $request->input('tab', 'collection');

        return view('tenant.fees.index', compact(
            'feeStructures', 'classes', 'terms',
            'searchQuery', 'searchResults', 'selectedStudent', 'feeItems',
            'filterTermId', 'activeTab', 'currentYear', 'currentYearTerms', 'currentTerm'
        ));
    }

    private function studentSelfView(): View
    {
        $student = Student::with(['schoolClass', 'section', 'user'])
            ->where('user_id', Auth::id())
            ->first();

        $currentTerm = Term::where('is_current', true)->first();
        $feeItems    = $student
            ? $this->feeStatusService->getStudentFeeItems($student, $currentTerm?->id)
            : [];

        return view('tenant.fees.my-fees', compact('student', 'feeItems', 'currentTerm'));
    }
}
