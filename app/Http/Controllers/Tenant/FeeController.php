<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RecordPaymentRequest;
use App\Http\Requests\Tenant\StoreFeeStructureRequest;
use App\Http\Requests\Tenant\UpdateFeeStructureRequest;
use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\FeeBundle;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Models\Tenant\Term;
use App\Jobs\SendWebhookPayload;
use App\Services\FeeStatusService;
use App\Services\PaystackService;
use App\Services\ReceiptService;
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
        private readonly ReceiptService $receiptService,
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
    // Fee Structure CRUD (standalone items)
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
    // Fee Bundle CRUD
    // -------------------------------------------------------------------------

    public function storeBundle(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('fees.create'), 403);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'target_class'     => ['required', 'string'],
            'billing_cycle'    => ['required', 'string', 'in:term,annual'],
            'term_id'          => ['required_if:billing_cycle,term', 'nullable', 'uuid', 'exists:terms,id'],
            'academic_year_id' => ['nullable', 'uuid', 'exists:academic_years,id'],
            'due_date'         => ['nullable', 'date'],
        ]);

        try {
            FeeBundle::create($data);

            return redirect(request()->getSchemeAndHttpHost() . '/fees?tab=structure')
                ->with('success', 'Fee bundle created.');
        } catch (\Throwable $e) {
            Log::error('[FeeController::storeBundle] ' . $e->getMessage());

            return back()->with('error', 'Could not create bundle. Please try again.')->withInput();
        }
    }

    public function updateBundle(Request $request, FeeBundle $bundle): RedirectResponse
    {
        abort_unless($request->user()->can('fees.edit'), 403);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'target_class'     => ['required', 'string'],
            'billing_cycle'    => ['required', 'string', 'in:term,annual'],
            'term_id'          => ['required_if:billing_cycle,term', 'nullable', 'uuid', 'exists:terms,id'],
            'academic_year_id' => ['nullable', 'uuid', 'exists:academic_years,id'],
            'due_date'         => ['nullable', 'date'],
        ]);

        try {
            $bundle->update($data);

            return redirect(request()->getSchemeAndHttpHost() . '/fees?tab=structure')
                ->with('success', 'Bundle updated.');
        } catch (\Throwable $e) {
            Log::error('[FeeController::updateBundle] ' . $e->getMessage());

            return back()->with('error', 'Could not update bundle. Please try again.')->withInput();
        }
    }

    public function destroyBundle(FeeBundle $bundle): RedirectResponse
    {
        abort_unless(request()->user()->can('fees.delete'), 403);

        try {
            // Detach items from bundle before deleting (nullify fee_bundle_id)
            FeeStructure::where('fee_bundle_id', $bundle->id)
                ->update(['fee_bundle_id' => null]);

            $bundle->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/fees?tab=structure')
                ->with('success', 'Bundle deleted. Its fee items became standalone.');
        } catch (\Throwable $e) {
            Log::error('[FeeController::destroyBundle] ' . $e->getMessage());

            return back()->with('error', 'Could not delete bundle. Please try again.');
        }
    }

    /**
     * Add a fee item to a specific bundle.
     */
    public function storeBundleItem(Request $request, FeeBundle $bundle): RedirectResponse
    {
        abort_unless($request->user()->can('fees.create'), 403);

        $data = $request->validate([
            'fee_item'     => ['required', 'string', 'max:100'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'is_mandatory' => ['boolean'],
            'due_date'     => ['nullable', 'date'],
        ]);

        try {
            FeeStructure::create(array_merge($data, [
                'fee_bundle_id'    => $bundle->id,
                'target_class'     => $bundle->target_class,
                'billing_cycle'    => $bundle->billing_cycle,
                'term_id'          => $bundle->term_id,
                'academic_year_id' => $bundle->academic_year_id,
            ]));

            return redirect(request()->getSchemeAndHttpHost() . '/fees?tab=structure')
                ->with('success', 'Fee item added to bundle.');
        } catch (\Throwable $e) {
            Log::error('[FeeController::storeBundleItem] ' . $e->getMessage());

            return back()->with('error', 'Could not add item to bundle. Please try again.');
        }
    }

    // -------------------------------------------------------------------------
    // Cash payment recording
    // -------------------------------------------------------------------------

    public function pay(RecordPaymentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $student   = Student::findOrFail($validated['student_id']);
        $amount    = (float) $validated['amount'];
        $host      = request()->getSchemeAndHttpHost();

        if (! empty($validated['fee_bundle_id'])) {
            // Bundle payment
            $bundle = FeeBundle::with('items')->findOrFail($validated['fee_bundle_id']);
            $result = $this->receiptService->recordBundlePayment($student, $bundle, $amount, 'cash');

            $returnUrl = $host . '/fees?student_id=' . $student->id . '&tab=collection';
        } else {
            // Standalone payment
            $feeStructure = FeeStructure::findOrFail($validated['fee_structure_id']);

            // Cap at outstanding balance
            $feeItems    = $this->feeStatusService->getStudentFeeItems($student, $feeStructure->term_id);
            $matchingItem = collect($feeItems)->firstWhere('fee_structure.id', $feeStructure->id);
            $outstanding  = $matchingItem ? $matchingItem['outstanding'] : (float) $feeStructure->amount;
            if ($amount > $outstanding && $outstanding > 0) {
                $amount = $outstanding;
            }

            $result    = $this->receiptService->recordStandalonePayment($student, $feeStructure, $amount, 'cash');
            $returnUrl = $host . '/fees?student_id=' . $student->id . '&term_id=' . $feeStructure->term_id . '&tab=collection';
        }

        if ($result['success']) {
            SendWebhookPayload::dispatch(tenant('id'), 'payment_received', [
                'event'     => 'payment_received',
                'tenant'    => tenant('id'),
                'timestamp' => now()->toIso8601String(),
                'data'      => [
                    'student_id'     => $student->id,
                    'student_name'   => $student->full_name,
                    'amount'         => $result['total_allocated'],
                    'receipt_number' => $result['receipt_number'],
                    'payment_method' => 'cash',
                ],
            ]);

            return redirect($returnUrl)->with('success',
                'Payment of ' . number_format($result['total_allocated'], 2) . ' recorded for ' . $student->full_name . '.'
                . ' Receipt: ' . $result['receipt_number']
            );
        }

        return redirect($returnUrl ?? ($host . '/fees?tab=collection'))->with('error', $result['error']);
    }

    // -------------------------------------------------------------------------
    // Paystack online payment
    // -------------------------------------------------------------------------

    public function paystackCheckout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id'       => ['required', 'uuid', 'exists:students,id'],
            'fee_structure_id' => ['required_without:fee_bundle_id', 'nullable', 'uuid', 'exists:fee_structures,id'],
            'fee_bundle_id'    => ['required_without:fee_structure_id', 'nullable', 'uuid', 'exists:fee_bundles,id'],
        ]);

        $student = Student::with('user')->findOrFail($validated['student_id']);
        $email   = $student->user?->email ?? Auth::user()->email;

        if (! empty($validated['fee_bundle_id'])) {
            $bundle   = FeeBundle::with('items')->findOrFail($validated['fee_bundle_id']);
            $feeItems = $this->feeStatusService->getStudentFeeItems($student, $bundle->term_id);

            $outstanding = (float) collect($feeItems)
                ->filter(fn ($i) => $i['fee_structure']->fee_bundle_id === $bundle->id)
                ->sum('outstanding');

            if ($outstanding <= 0) {
                return back()->with('info', 'This bundle is already fully paid.');
            }

            $metadata = [
                'student_id'    => $student->id,
                'fee_bundle_id' => $bundle->id,
                'student_name'  => $student->full_name,
                'bundle_name'   => $bundle->name,
            ];
        } else {
            $feeStructure = FeeStructure::findOrFail($validated['fee_structure_id']);
            $feeItems     = $this->feeStatusService->getStudentFeeItems($student, $feeStructure->term_id);
            $matchingItem = collect($feeItems)->firstWhere('fee_structure.id', $feeStructure->id);
            $outstanding  = $matchingItem ? $matchingItem['outstanding'] : (float) $feeStructure->amount;

            if ($outstanding <= 0) {
                return back()->with('info', 'This fee item is already fully paid.');
            }

            $metadata = [
                'student_id'       => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'student_name'     => $student->full_name,
                'fee_item'         => $feeStructure->fee_item,
            ];
        }

        $callbackUrl = request()->getSchemeAndHttpHost() . '/paystack/callback';
        $result      = $this->paystackService->initializeTransaction(
            email: $email,
            amount: $outstanding,
            callbackUrl: $callbackUrl,
            metadata: $metadata
        );

        if (! $result['success']) {
            return back()->with('error', $result['error'] ?? 'Could not initialize payment. Please try again.');
        }

        return redirect()->away($result['authorization_url']);
    }

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
        $feeBundleId    = $metadata['fee_bundle_id'] ?? null;
        $feeStructureId = $metadata['fee_structure_id'] ?? null;

        if (! $studentId || (! $feeBundleId && ! $feeStructureId)) {
            return redirect($host . '/fees')->with('error', 'Payment metadata missing — contact support with reference: ' . $reference);
        }

        $student = Student::find($studentId);
        if (! $student) {
            return redirect($host . '/fees')->with('error', 'Student not found for this payment.');
        }

        try {
            if ($feeBundleId) {
                $bundle = FeeBundle::with('items')->find($feeBundleId);
                if (! $bundle) {
                    return redirect($host . '/fees')->with('error', 'Fee bundle not found for this payment.');
                }
                $result = $this->receiptService->recordBundlePayment(
                    $student, $bundle, $verification['amount'], 'paystack', $reference
                );
            } else {
                $feeStructure = FeeStructure::find($feeStructureId);
                if (! $feeStructure) {
                    return redirect($host . '/fees')->with('error', 'Fee item not found for this payment.');
                }
                $result = $this->receiptService->recordStandalonePayment(
                    $student, $feeStructure, $verification['amount'], 'paystack', $reference
                );
            }
        } catch (\Throwable $e) {
            Log::error('[FeeController::paystackCallback] ' . $e->getMessage());

            return redirect($host . '/fees')->with('error', 'Could not record payment. Contact support with reference: ' . $reference);
        }

        if (! $result['success']) {
            return redirect($host . '/fees')->with('error', $result['error']);
        }

        return redirect($host . '/fees')
            ->with('success', 'Payment of ' . number_format($result['total_allocated'], 2) . ' recorded for ' . $student->full_name . '.');
    }

    // -------------------------------------------------------------------------
    // Term Bill PDF
    // -------------------------------------------------------------------------

    public function printBill(Request $request, Student $student): SymfonyResponse
    {
        abort_unless($request->user()->can('fees.view'), 403);

        $termId   = $request->input('term_id');
        $term     = $termId ? Term::with('academicYear')->find($termId) : null;
        $feeItems = $this->feeStatusService->getStudentFeeItems($student, $termId);

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

        $latestPayment  = FeePayment::with('recordedBy')
            ->where('student_id', $student->id)
            ->whereNotNull('recorded_by')
            ->latest('paid_at')
            ->first();
        $accountOfficer = $latestPayment?->recordedBy?->name;

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
                // Not critical
            }
        }

        $data     = compact('student', 'term', 'feeItems', 'arrearsTotal', 'schoolProfile', 'logoBase64', 'accountOfficer');
        $pdf      = Pdf::loadView('tenant.fees.term-bill-pdf', $data)->setPaper('a4', 'portrait');
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->full_name);
        $safeTerm = $term ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $term->name) : 'all';

        return $pdf->stream("bill-{$safeName}-{$safeTerm}.pdf");
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function adminView(Request $request): View
    {
        $academicYears   = AcademicYear::with('terms')->orderByDesc('start_date')->get();
        $structureYearId = $request->input('structure_year_id', '');

        $yearFilter = function ($q) use ($structureYearId): void {
            if ($structureYearId) {
                $q->where(function ($inner) use ($structureYearId): void {
                    $inner->where('academic_year_id', $structureYearId)
                        ->orWhereHas('term', function ($q2) use ($structureYearId): void {
                            $q2->where('academic_year_id', $structureYearId);
                        });
                });
            }
        };

        // Standalone fee structures (not in any bundle)
        $feeStructures = FeeStructure::with(['term.academicYear', 'academicYear'])
            ->whereNull('fee_bundle_id')
            ->when($structureYearId, $yearFilter)
            ->orderBy('billing_cycle')
            ->orderBy('fee_item')
            ->get();

        // Fee bundles with their items
        $feeBundles = FeeBundle::with(['items', 'term.academicYear', 'academicYear'])
            ->when($structureYearId, function ($q) use ($structureYearId): void {
                $q->where(function ($inner) use ($structureYearId): void {
                    $inner->where('academic_year_id', $structureYearId)
                        ->orWhereHas('term', function ($q2) use ($structureYearId): void {
                            $q2->where('academic_year_id', $structureYearId);
                        });
                });
            })
            ->orderByDesc('created_at')
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
                ->where(function ($q) use ($searchQuery): void {
                    $q->where('full_name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('admission_no', 'like', '%' . $searchQuery . '%');
                })
                ->orderBy('full_name')
                ->limit(20)
                ->get();
        }

        $activeTab = $request->input('tab', 'collection');

        $recentPayments = FeePayment::with(['student.schoolClass', 'feeStructure'])
            ->orderByDesc('paid_at')
            ->limit(15)
            ->get();

        return view('tenant.fees.index', compact(
            'feeStructures', 'feeBundles', 'classes', 'terms',
            'searchQuery', 'searchResults', 'selectedStudent', 'feeItems',
            'filterTermId', 'activeTab', 'currentYear', 'currentYearTerms', 'currentTerm',
            'academicYears', 'structureYearId', 'recentPayments'
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
