<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class CustomDomainController extends Controller
{
    public function index(): View
    {
        return view('tenant.coming-soon', ['section' => 'Custom Domain', 'phase' => '7.22']);
    }
}
