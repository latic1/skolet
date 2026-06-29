@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex items-center gap-1 border-b border-border pb-0 overflow-x-auto">
    @php
        $tabs = [
            ['href' => "$host/settings/profile",      'label' => 'School Profile',       'active' => request()->routeIs('tenant.settings.profile')],
            ['href' => "$host/settings/roles",        'label' => 'Roles & Permissions',  'active' => request()->routeIs('tenant.settings.roles')],
            ['href' => "$host/settings/notifications",'label' => 'Notifications',        'active' => request()->routeIs('tenant.settings.notifications')],
            ['href' => "$host/settings/domain",       'label' => 'Custom Domain',        'active' => request()->routeIs('tenant.settings.domain')],
            ['href' => "$host/settings/billing",      'label' => 'Billing',              'active' => request()->routeIs('tenant.settings.billing')],
            ['href' => "$host/settings/webhooks",     'label' => 'Webhooks',             'active' => request()->routeIs('tenant.settings.webhooks') || request()->routeIs('tenant.settings.webhooks.deliveries')],
            ['href' => "$host/settings/audit-log",    'label' => 'Audit Log',            'active' => request()->routeIs('tenant.settings.audit-log')],
            ['href' => "$host/settings/privacy",      'label' => 'Data & Privacy',       'active' => request()->routeIs('tenant.settings.privacy')],
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
