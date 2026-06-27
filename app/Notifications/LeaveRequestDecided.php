<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class LeaveRequestDecided extends Notification implements ShouldQueue
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
        $status    = ucfirst($this->leaveRequest->status);
        $type      = $this->leaveRequest->leave_type_label;
        $staffName = $this->leaveRequest->staff->full_name;
        $dates     = $this->leaveRequest->start_date->format('d M Y')
            . ' – '
            . $this->leaveRequest->end_date->format('d M Y');

        $message = (new MailMessage())
            ->subject("Leave Request {$status} — {$type}")
            ->greeting("Dear {$staffName},")
            ->line("Your {$type} ({$dates}) has been **{$status}**.");

        if ($this->leaveRequest->status === 'rejected' && $this->leaveRequest->rejection_reason) {
            $message->line("Reason: {$this->leaveRequest->rejection_reason}");
        }

        return $message->salutation('— ' . config('app.name'));
    }
}
