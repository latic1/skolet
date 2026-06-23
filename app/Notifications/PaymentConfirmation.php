<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant\FeePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PaymentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly FeePayment $payment,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $schoolName  = config('app.name');
        $student     = $this->payment->student;
        $amount      = number_format((float) $this->payment->amount, 2);
        $feeItem     = $this->payment->feeStructure?->fee_item ?? 'Fee';
        $method      = ucfirst($this->payment->payment_method ?? 'cash');
        $paidAt      = $this->payment->paid_at?->format('d M Y, g:i A') ?? now()->format('d M Y, g:i A');

        return (new MailMessage())
            ->subject("Payment Confirmation — {$student?->full_name}")
            ->greeting("Dear {$student?->guardian_name},")
            ->line("We have received a payment for **{$student?->full_name}**. Details below:")
            ->line("**Fee:** {$feeItem}")
            ->line("**Amount Paid:** {$amount}")
            ->line("**Payment Method:** {$method}")
            ->line("**Date:** {$paidAt}")
            ->line('Thank you for your prompt payment.')
            ->salutation("— {$schoolName}");
    }
}
