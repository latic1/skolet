<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class FeeOverdueReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Student $student,
        public readonly FeeStructure $feeStructure,
        public readonly float $outstanding,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $schoolName = config('app.name');
        $amount     = number_format($this->outstanding, 2);
        $feeName    = $this->feeStructure->fee_item;
        $dueDate    = $this->feeStructure->due_date?->format('d M Y') ?? 'N/A';

        return (new MailMessage())
            ->subject("Fee Overdue Reminder — {$this->student->full_name}")
            ->greeting("Dear {$this->student->guardian_name},")
            ->line("This is a reminder that the following fee for **{$this->student->full_name}** is overdue:")
            ->line("**{$feeName}** · Outstanding: **{$amount}** · Due: {$dueDate}")
            ->line('Please visit the school office to settle this balance at your earliest convenience.')
            ->line('If you have already made a payment, please disregard this notice.')
            ->salutation("— {$schoolName}");
    }
}
