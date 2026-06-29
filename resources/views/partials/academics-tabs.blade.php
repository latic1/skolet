@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex items-center gap-1 border-b border-border pb-0 overflow-x-auto">
    @php
        $tabs = [
            ['href' => "$host/settings/academic-year", 'label' => 'Academic Calendar',   'active' => request()->routeIs('tenant.settings.academic-year')],
            ['href' => "$host/settings/classes",       'label' => 'Classes & Sections',  'active' => request()->routeIs('tenant.settings.classes')],
            ['href' => "$host/settings/subjects",      'label' => 'Subjects',            'active' => request()->routeIs('tenant.settings.subjects')],
        ];
    @endphp
    @foreach ($tabs as $tab)
        <a href="{{ $tab['href'] }}"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap
                  {{ $tab['active'] ? 'border-accent text-accent' : 'border-transparent text-text-secondary hover:text-text-primary' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
