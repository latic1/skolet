<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var array $item */
        $item = $this->resource;

        $fs = $item['fee_structure'];

        return [
            'fee_structure_id' => $fs->id,
            'fee_item'         => $fs->fee_item,
            'term'             => $fs->term?->name,
            'billing_cycle'    => $fs->billing_cycle,
            'original_amount'  => $item['original_amount'],
            'effective_amount' => $item['effective_amount'],
            'has_discount'     => $item['has_discount'],
            'paid_amount'      => $item['paid_amount'],
            'outstanding'      => $item['outstanding'],
            'status'           => $item['status'],
            'due_date'         => $fs->due_date?->toDateString(),
        ];
    }
}
