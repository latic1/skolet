<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\Exam;
use App\Models\Tenant\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExamResultsPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Exam $exam,
        public readonly Student $student,
        public readonly string $loginUrl,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $schoolName = config('app.name');

        return (new MailMessage())
            ->subject("Results Published — {$this->exam->name}")
            ->greeting("Hi {$this->student->full_name},")
            ->line("The results for **{$this->exam->name}** have been published.")
            ->line('You can now log in to your school portal to view your report card.')
            ->action('View My Results', $this->loginUrl)
            ->salutation("— {$schoolName}");
    }
}
