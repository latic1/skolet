<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\DisciplinaryRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class DisciplinaryIncidentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly DisciplinaryRecord $record,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $student    = $this->record->student;
        $schoolName = config('app.name');
        $typeLabel  = ucfirst($this->record->incident_type);
        $date       = $this->record->date->format('l, d M Y');
        $className  = $student->schoolClass?->name ?? 'N/A';

        $message = (new MailMessage())
            ->subject("{$typeLabel} Notice — {$student->full_name}")
            ->greeting("Dear {$student->guardian_name},")
            ->line("We wish to inform you of a **{$typeLabel}** issued to **{$student->full_name}** ({$className}) on {$date}.")
            ->line("**Details:** {$this->record->description}");

        if ($this->record->action_taken) {
            $message->line("**Action Taken:** {$this->record->action_taken}");
        }

        return $message
            ->line('If you have any questions, please contact the school office.')
            ->salutation("— {$schoolName}");
    }
}
