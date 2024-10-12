<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Confirm_HelpR_Notify extends Notification
{
    use Queueable;
    public $helpR;

    public function __construct($helpR)
    {
        $this->helpR = $helpR;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Notify for your',
            'body' => "Your Help Request has been successfully accepted : {$this->helpR->description}.",
        ];
    }
}
