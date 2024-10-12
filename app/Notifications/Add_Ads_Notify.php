<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Add_Ads_Notify extends Notification
{
    use Queueable;


    public $ads;

    public function __construct($ads)
    {
        $this->ads = $ads;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Ads',
            'title_Ads' => $this->ads->title,
            'body' => $this->ads->description,
        ];
    }
}
