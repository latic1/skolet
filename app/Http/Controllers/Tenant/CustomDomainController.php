<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCustomDomainRequest;
use App\Models\Central\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class CustomDomainController extends Controller
{
    public function index(): View
    {
        $domains = Domain::where('tenant_id', tenant('id'))
            ->orderBy('created_at')
            ->get();

        return view('tenant.settings.domain', [
            'domains'       => $domains,
            'primaryDomain' => $domains->first(),
            'customDomains' => $domains->slice(1)->values(),
            'cnameTarget'   => $this->baseHost(),
        ]);
    }

    public function store(StoreCustomDomainRequest $request): RedirectResponse
    {
        $domain = strtolower(trim($request->validated()['domain']));

        // Block registering a skolet subdomain as a custom domain
        if (str_ends_with($domain, '.' . $this->baseHost())) {
            return back()
                ->withErrors(['domain' => 'You cannot add a ' . $this->baseHost() . ' subdomain here.'])
                ->withInput();
        }

        if (Domain::where('domain', $domain)->exists()) {
            return back()
                ->withErrors(['domain' => 'This domain is already registered on Skolet.'])
                ->withInput();
        }

        try {
            tenant()->domains()->create(['domain' => $domain]);
        } catch (\Throwable $e) {
            Log::error('[CustomDomainController::store] ' . $e->getMessage(), [
                'tenant_id' => tenant('id'),
                'domain'    => $domain,
            ]);

            return back()
                ->with('error', 'Could not add domain. Please try again.')
                ->withInput();
        }

        return back()->with('success', 'Custom domain added. Configure the CNAME record as shown below, then click "Verify DNS".');
    }

    public function verify(int $domainId): RedirectResponse
    {
        $domain = Domain::where('id', $domainId)
            ->where('tenant_id', tenant('id'))
            ->firstOrFail();

        if ($domain->isPrimary()) {
            return back()->with('error', 'The primary subdomain does not require verification.');
        }

        if ($domain->isVerified()) {
            return back()->with('success', 'This domain is already verified.');
        }

        $records = @dns_get_record($domain->domain, DNS_CNAME);
        $baseHost = $this->baseHost();
        $verified = false;

        if (is_array($records)) {
            foreach ($records as $record) {
                $target = rtrim($record['target'] ?? '', '.');
                if ($target === $baseHost || str_ends_with($target, '.' . $baseHost)) {
                    $verified = true;
                    break;
                }
            }
        }

        if ($verified) {
            try {
                $domain->update(['verified_at' => now()]);
            } catch (\Throwable $e) {
                Log::error('[CustomDomainController::verify] ' . $e->getMessage(), [
                    'domain_id' => $domainId,
                ]);

                return back()->with('error', 'Verification confirmed but could not save. Please try again.');
            }

            return back()->with('success', 'Domain verified! SSL will be issued automatically on the first request.');
        }

        return back()->with('error', 'CNAME record not found yet. DNS changes can take up to 48 hours to propagate — please try again later.');
    }

    public function destroy(int $domainId): RedirectResponse
    {
        $domain = Domain::where('id', $domainId)
            ->where('tenant_id', tenant('id'))
            ->firstOrFail();

        if ($domain->isPrimary()) {
            return back()->with('error', 'The primary subdomain cannot be removed.');
        }

        try {
            $domain->delete();
        } catch (\Throwable $e) {
            Log::error('[CustomDomainController::destroy] ' . $e->getMessage(), [
                'domain_id' => $domainId,
            ]);

            return back()->with('error', 'Could not remove domain. Please try again.');
        }

        return back()->with('success', 'Custom domain removed.');
    }

    private function baseHost(): string
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'skolet.com';

        return (string) preg_replace('/^www\./i', '', $appHost);
    }
}
