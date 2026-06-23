<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AbsenceAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Student $student,
        public readonly string $date,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $schoolName = config('app.name');
        $className  = $student->schoolClass?->name ?? 'N/A';

        return (new MailMessage())
            ->subject("Absence Notice — {$this->student->full_name}")
            ->greeting("Dear {$this->student->guardian_name},")
            ->line("This is to inform you that **{$this->student->full_name}** ({$className}) was marked **absent** on " . \Carbon\Carbon::parse($this->date)->format('l, d M Y') . '.')
            ->line('If this absence was unplanned, please contact the school office.')
            ->line('Thank you for your attention.')
            ->salutation("— {$schoolName}");
    }
}
