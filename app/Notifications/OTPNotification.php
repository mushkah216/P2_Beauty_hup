<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OTPNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $otp,
        public string $subject = 'Beauty Hub — رمز التحقق',
        public string $headline = 'رمز التحقق الخاص بك هو:',
        public string $description = 'استخدم الرمز التالي لإكمال عملية التحقق.'
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('مرحباً ' . ($notifiable->full_name ?? ''))
            ->line($this->headline)
            ->line($this->description)
            ->line('رمز التحقق: ' . $this->otp)
            ->line('هذا الرمز صالح لمدة 10 دقائق.')
            ->line('إذا لم تطلب هذا الرمز، يمكنك تجاهل هذه الرسالة.');
    }
}