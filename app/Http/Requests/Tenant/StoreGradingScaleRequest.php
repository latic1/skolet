<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

final class StoreGradingScaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'bands'              => ['required', 'array', 'min:1'],
            'bands.*.min'        => ['required', 'integer', 'min:0', 'max:100'],
            'bands.*.max'        => ['required', 'integer', 'min:0', 'max:100'],
            'bands.*.grade'      => ['required', 'string', 'max:5'],
            'bands.*.remark'     => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'bands.required'         => 'At least one grade band is required.',
            'bands.*.min.required'   => 'Min score is required for each band.',
            'bands.*.max.required'   => 'Max score is required for each band.',
            'bands.*.grade.required' => 'Grade label is required for each band.',
            'bands.*.remark.required'=> 'Remark is required for each band.',
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $bands = $this->input('bands', []);

            if (empty($bands)) {
                return;
            }

            foreach ($bands as $i => $band) {
                $min = (int) ($band['min'] ?? 0);
                $max = (int) ($band['max'] ?? 0);

                if ($min >= $max) {
                    $v->errors()->add(
                        "bands.{$i}.max",
                        'Max score must be greater than min score.'
                    );
                }
            }

            if ($v->errors()->any()) {
                return;
            }

            // Sort bands by min descending to check for gaps and overlaps
            $sorted = collect($bands)
                ->map(fn ($b) => ['min' => (int) $b['min'], 'max' => (int) $b['max']])
                ->sortByDesc('min')
                ->values();

            // Check overlaps: each band's max must be exactly one less than the previous band's min
            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                $current = $sorted[$i];
                $next    = $sorted[$i + 1];

                if ($current['min'] <= $next['max']) {
                    $v->errors()->add('bands', 'Grade bands must not overlap. Check your min/max values.');
                    return;
                }

                if ($current['min'] - $next['max'] > 1) {
                    $v->errors()->add('bands', 'Grade bands must cover all scores from 0 to 100 with no gaps.');
                    return;
                }
            }

            // Check full 0–100 coverage
            $highest = $sorted->max('max');
            $lowest  = $sorted->min('min');

            if ($highest < 100) {
                $v->errors()->add('bands', 'Grade bands must cover scores up to 100.');
            }

            if ($lowest > 0) {
                $v->errors()->add('bands', 'Grade bands must cover scores down to 0.');
            }
        });
    }
}
