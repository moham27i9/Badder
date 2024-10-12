<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangeRole_Notify extends Notification
{
    use Queueable;

    public $newRole;
    public $oldRole;

    public function __construct($newRole, $oldRole)
    {
        $this->newRole = $newRole;
        $this->oldRole = $oldRole;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Notify for your',
            'body' => "You have been transferred from {$this->oldRole} to {$this->newRole}.",
        ];
    }
}
