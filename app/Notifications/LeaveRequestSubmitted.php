<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class LeaveRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly LeaveRequest $leaveRequest,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $staffName = $this->leaveRequest->staff->full_name;
        $type      = $this->leaveRequest->leave_type_label;
        $dates     = $this->leaveRequest->start_date->format('d M Y')
            . ' – '
            . $this->leaveRequest->end_date->format('d M Y');

        return (new MailMessage())
            ->subject("Leave Request — {$staffName}")
            ->greeting('Dear Admin,')
            ->line("{$staffName} has submitted a {$type} for {$dates}.")
            ->line('Please review and approve or reject the request in the Leave Management section.')
            ->salutation('— ' . config('app.name'));
    }
}
