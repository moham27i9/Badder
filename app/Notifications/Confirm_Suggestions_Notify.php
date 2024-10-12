<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Confirm_Suggestions_Notify extends Notification
{
    use Queueable;

    public $suggestions;

    public function __construct($suggestions)
    {
        $this->suggestions = $suggestions;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Notify for your',
            'body' => "Your Suggestions has been successfully accepted : {$this->suggestions->description}.",
        ];
    }



}


