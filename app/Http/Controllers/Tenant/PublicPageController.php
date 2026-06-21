<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Announcement;
use App\Models\Tenant\SchoolProfile;
use Illuminate\View\View;

final class PublicPageController extends Controller
{
    public function index(): View
    {
        $profile = SchoolProfile::first();
        $announcements = Announcement::where('is_public', true)
            ->latest()
            ->limit(5)
            ->get();

        return view('tenant.public-page', compact('profile', 'announcements'));
    }
}
