<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\AdmissionApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AdmissionRejected extends Notification implements ShouldQueue
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
        $message = (new MailMessage())
            ->subject("Admission Application Update — {$this->application->applicant_name}")
            ->greeting("Dear {$this->application->guardian_name},")
            ->line("Thank you for your interest in **{$this->schoolName}**.")
            ->line("After careful consideration, we regret to inform you that the admission application for **{$this->application->applicant_name}** ({$this->application->class_applying_for}) has not been successful at this time.");

        if ($this->application->rejection_reason) {
            $message = $message->line("**Reason:** {$this->application->rejection_reason}");
        }

        return $message
            ->line('We wish your child all the best in their educational journey.')
            ->line('If you believe there has been an error or wish to discuss this further, please contact the school office.')
            ->salutation("— {$this->schoolName}");
    }
}
