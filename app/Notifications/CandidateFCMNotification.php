<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CandidateFCMNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $title;
    protected $redirectUrl;
    protected $icon;
    protected $type;
    protected $uniqueId;
     protected $Imagetype;

    public function __construct($message, $title, $redirectUrl = null,$icon , $type ,$uniqueId,$Imagetype)
    {

        $this->message = $message;
        $this->title = $title;
        $this->redirectUrl = $redirectUrl;
         $this->icon = $icon;
        $this->type = $type;
        $this->uniqueId = $uniqueId;
        $this->Imagetype = $Imagetype;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'redirect_url' => $this->redirectUrl,
            'icon'         => $this->icon,
            'type'         => $this->type,
            'uniqe_id'     => $this->uniqueId,
            'Imagetype'    => $this->Imagetype,
        ];
    }

    public function toBroadcast($notifiable)
    {
        $broadcastData = [
            'title' => $this->title,
            'message' => $this->message,
            'created_at' => now()->diffForHumans(),
        ];

        if ($this->redirectUrl) {
            $broadcastData['redirect_url'] = $this->redirectUrl;
        }

        return new BroadcastMessage($broadcastData);
    }
}
