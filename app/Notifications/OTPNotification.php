<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Hash;

class OTPNotification extends Notification
{
    public string $otp;

    public function __construct()
    {
        $this->otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // احفظ الـ OTP على الموديل قبل ما تبعثه
        $notifiable->update([
            'otp_code'       => Hash::make($this->otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return (new MailMessage)
            ->subject('Beauty Hub — كود التحقق')
            ->greeting('مرحباً ' . $notifiable->full_name)
            ->line('كود التحقق الخاص بك هو:')
            ->line('**' . $this->otp . '**')
            ->line('صالح لمدة 10 دقائق.')
            ->line('إذا لم تطلب هذا الكود، تجاهل هذه الرسالة.');
    }
}