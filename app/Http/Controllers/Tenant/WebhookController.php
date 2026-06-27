<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Webhook;
use App\Models\Tenant\WebhookDelivery;
use App\Services\WebhookDeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WebhookController extends Controller
{
    private const VALID_EVENTS = [
        'student_enrolled',
        'payment_received',
        'attendance_marked',
        'exam_published',
        'announcement_posted',
    ];

    public function index(): View
    {
        $webhooks = Webhook::withCount('deliveries')
            ->orderByDesc('created_at')
            ->get()
            ->each(function (Webhook $w): void {
                $w->setRelation('latestDeliveryRecord', $w->deliveries()->latest('attempted_at')->first());
            });

        return view('tenant.settings.webhooks', ['webhooks' => $webhooks]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'url'      => ['required', 'url', 'max:500'],
            'secret'   => ['required', 'string', 'min:16', 'max:255'],
            'events'   => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'in:' . implode(',', self::VALID_EVENTS)],
        ]);

        if (! str_starts_with($data['url'], 'https://')) {
            return back()->withInput()->with('error', 'Webhook URL must use HTTPS.');
        }

        Webhook::create([
            'url'    => $data['url'],
            'secret' => $data['secret'],
            'events' => $data['events'],
            'active' => true,
        ]);

        return back()->with('success', 'Webhook endpoint added successfully.');
    }

    public function toggle(Webhook $webhook): RedirectResponse
    {
        $webhook->update(['active' => ! $webhook->active]);
        $state = $webhook->active ? 'enabled' : 'disabled';

        return back()->with('success', "Webhook {$state}.");
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $webhook->delete();

        return back()->with('success', 'Webhook endpoint removed.');
    }

    public function deliveries(Webhook $webhook): View
    {
        $deliveries = $webhook->deliveries()
            ->orderByDesc('attempted_at')
            ->paginate(20);

        return view('tenant.settings.webhook-deliveries', compact('webhook', 'deliveries'));
    }

    public function retry(Request $request, Webhook $webhook, WebhookDelivery $delivery): RedirectResponse
    {
        abort_if($delivery->webhook_id !== $webhook->id, 404);
        abort_if(! $delivery->canRetry(), 422, 'Max retries reached or delivery already succeeded.');

        app(WebhookDeliveryService::class)->retry($delivery);

        return back()->with('success', 'Retry attempt dispatched.');
    }
}
