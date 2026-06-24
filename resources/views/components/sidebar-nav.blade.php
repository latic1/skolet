@php
    $user = auth()->user();

    /*
     * route() cannot auto-bind the {subdomain} domain parameter, so we build
     * URLs as host + path. Route::has() is still used to gate whether a nav
     * item renders as a live link (route registered) or a disabled span (not yet).
     *
     * Each item:
     *   permission  — null = visible to all authenticated users
     *   route       — named route; Route::has() checks registration
     *   path        — appended to the current request host to build the href
     */
    $navItems = [
        [
            'label'      => 'Dashboard',
            'route'      => 'tenant.dashboard',
            'path'       => '/dashboard',
            'permission' => null,
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        ],
        [
            'label'      => 'Students',
            'route'      => 'tenant.students.index',
            'path'       => '/students',
            'permission' => 'students.view',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        ],
        [
            'label'      => 'Staff',
            'route'      => 'tenant.staff.index',
            'path'       => '/staff',
            'permission' => 'staff.view',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
        ],
        [
            'label'      => 'Classes & Sections',
            'route'      => 'tenant.settings.classes',
            'path'       => '/settings/classes',
            'permission' => 'settings.manage',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        ],
        [
            'label'      => 'Subjects',
            'route'      => 'tenant.settings.subjects',
            'path'       => '/settings/subjects',
            'permission' => 'settings.manage',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>',
        ],
        [
            'label'      => 'Attendance',
            'route'      => 'tenant.attendance.index',
            'path'       => '/attendance',
            'permission' => 'attendance.view',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        ],
        [
            'label'      => 'Timetable',
            'route'      => 'tenant.timetable.index',
            'path'       => '/timetable',
            'permission' => 'timetable.view',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        ],
        [
            'label'      => 'Exams',
            'route'      => 'tenant.exams.index',
            'path'       => '/exams',
            'permission' => 'exams.view',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        ],
        [
            'label'      => 'Fees',
            'route'      => 'tenant.fees.index',
            'path'       => '/fees',
            'permission' => null,
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
        ],
        [
            'label'      => 'My Children',
            'route'      => 'tenant.parents.portal',
            'path'       => '/my-children',
            'permission' => null,
            'role'       => 'parent',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
        ],
        [
            'label'      => 'Announcements',
            'route'      => 'tenant.announcements.index',
            'path'       => '/announcements',
            'permission' => null,
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
        ],
        [
            'label'      => 'Reports',
            'route'      => 'tenant.reports.index',
            'path'       => '/reports',
            'permission' => 'reports.view',
            'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        ],
        [
            'label'        => 'Settings',
            'route'        => 'tenant.settings.academic-year',
            'activeRoute'  => 'tenant.settings.*',
            'exclude'      => ['tenant.settings.classes', 'tenant.settings.subjects'],
            'path'         => '/settings/academic-year',
            'permission'   => 'settings.manage',
            'icon'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        ],
    ];

    $host = request()->getSchemeAndHttpHost();
@endphp

<nav class="flex flex-col gap-0.5">
    @foreach ($navItems as $item)
        @php
            $permOk  = $item['permission'] === null || $user->can($item['permission']);
            $roleOk  = !isset($item['role']) || $user->hasRole($item['role']);
            $visible = $user && $permOk && $roleOk;
        @endphp

        @if ($visible)
            @php
                $activePattern = $item['activeRoute'] ?? ($item['route'] . '*');
                $excluded      = !empty($item['exclude']) && request()->routeIs($item['exclude']);
                $isActive      = !$excluded && (request()->routeIs($item['route']) || request()->routeIs($activePattern));
                $routeExists   = Route::has($item['route']);
            @endphp

            @if ($routeExists)
                <a href="{{ $host . $item['path'] }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                          {{ $isActive
                             ? 'bg-accent-muted text-accent'
                             : 'text-text-dark hover:bg-surface-secondary hover:text-text-primary' }}">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $item['icon'] !!}
                    </svg>
                    {{ $item['label'] }}
                </a>
            @else
                <span class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-text-muted cursor-not-allowed opacity-50">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $item['icon'] !!}
                    </svg>
                    {{ $item['label'] }}
                </span>
            @endif
        @endif
    @endforeach
</nav>
