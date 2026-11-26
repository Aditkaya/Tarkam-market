<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangedNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kata sandi Tarkam Market.ID telah diubah')
            ->greeting('Halo!')
            ->line('Kata sandi akun Anda berhasil diubah melalui pemulihan akun.')
            ->line('Sesi login sebelumnya tidak dapat digunakan lagi. Silakan masuk kembali.')
            ->line('Jika bukan Anda yang melakukan perubahan, segera pulihkan akun dan hubungi pengelola Tarkam Market.ID.')
            ->salutation('Tim Tarkam Market.ID');
    }
}
