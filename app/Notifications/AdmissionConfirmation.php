<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\AdmissionApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AdmissionConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly AdmissionApplication $application,
        public readonly string $schoolName,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Admission Application Received — {$this->application->applicant_name}")
            ->greeting("Dear {$this->application->guardian_name},")
            ->line("Thank you for submitting an admission application for **{$this->application->applicant_name}** to **{$this->schoolName}**.")
            ->line("**Class applied for:** {$this->application->class_applying_for}")
            ->line('Your application has been received and is currently under review. You will be notified once a decision has been made.')
            ->line('If you have any questions, please contact the school office directly.')
            ->salutation("— {$this->schoolName}");
    }
}
