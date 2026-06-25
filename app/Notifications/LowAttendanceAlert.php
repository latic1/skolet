<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class LowAttendanceAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Student $student,
        public readonly float $percentPresent,
        public readonly string $termName,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $schoolName = config('app.name');
        $className  = $this->student->schoolClass?->name ?? 'N/A';

        return (new MailMessage())
            ->subject("Low Attendance Alert — {$this->student->full_name}")
            ->greeting("Dear {$this->student->guardian_name},")
            ->line("We are writing to inform you that **{$this->student->full_name}** ({$className}) has an attendance rate of **{$this->percentPresent}%** for **{$this->termName}**.")
            ->line('Regular attendance is important for academic success. Please ensure your child attends school regularly.')
            ->line('If there are any circumstances affecting attendance, please contact the school office as soon as possible.')
            ->line('Thank you for your support.')
            ->salutation("— {$schoolName}");
    }
}
