<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreFeeDiscountRequest;
use App\Models\Tenant\FeeDiscount;
use App\Models\Tenant\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class FeeDiscountController extends Controller
{
    public function store(StoreFeeDiscountRequest $request, Student $student): RedirectResponse
    {
        try {
            $data = $request->validated();

            $student->feeDiscounts()->create([
                'fee_structure_id' => $data['fee_structure_id'] ?? null,
                'discount_type'    => $data['discount_type'],
                'discount_value'   => $data['discount_value'],
                'reason'           => $data['reason'],
                'approved_by'      => Auth::id(),
                'valid_from'       => $data['valid_from'] ?? null,
                'valid_until'      => $data['valid_until'] ?? null,
            ]);

            return redirect(request()->getSchemeAndHttpHost() . '/students/' . $student->id)
                ->with('success', 'Fee discount added successfully.');
        } catch (\Throwable $e) {
            Log::error('[FeeDiscountController::store] ' . $e->getMessage());

            return back()->with('error', 'Could not add discount. Please try again.')->withInput();
        }
    }

    public function destroy(Request $request, Student $student, FeeDiscount $discount): RedirectResponse
    {
        abort_unless($request->user()->can('fees.edit'), 403);

        try {
            $discount->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/students/' . $student->id)
                ->with('success', 'Discount removed.');
        } catch (\Throwable $e) {
            Log::error('[FeeDiscountController::destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not remove discount. Please try again.');
        }
    }
}
