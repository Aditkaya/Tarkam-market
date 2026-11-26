<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset kata sandi Tarkam Market.ID')
            ->greeting('Halo!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.')
            ->action('Buat kata sandi baru', $this->resetUrl($notifiable))
            ->line('Tautan ini berlaku selama '.config('auth.passwords.users.expire').' menit dan hanya dapat digunakan sekali.')
            ->line('Jika Anda tidak meminta reset kata sandi, abaikan email ini.')
            ->salutation('Tim Tarkam Market.ID');
    }
}
