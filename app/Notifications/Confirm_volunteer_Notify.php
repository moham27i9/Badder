<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Confirm_volunteer_Notify extends Notification
{
    use Queueable;

    public $vol_unt;

    public function __construct($vol_unt)
    {
        $this->vol_unt = $vol_unt;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Notify for your',
            'body' => "Your Request volunteer has been successfully accepted.",
        ];
    }
}
