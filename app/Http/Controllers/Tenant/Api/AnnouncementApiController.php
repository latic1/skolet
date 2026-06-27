<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AnnouncementApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $announcements = Announcement::with('postedBy')
            ->latest()
            ->paginate(25);

        $items = $announcements->getCollection()->map(fn ($a) => [
            'id'         => $a->id,
            'title'      => $a->title,
            'body'       => $a->body,
            'is_public'  => $a->is_public,
            'posted_by'  => $a->postedBy?->name,
            'created_at' => $a->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $announcements->currentPage(),
                'last_page'    => $announcements->lastPage(),
                'total'        => $announcements->total(),
            ],
        ]);
    }
}
