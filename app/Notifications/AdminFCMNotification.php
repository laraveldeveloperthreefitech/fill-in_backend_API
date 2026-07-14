<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AdminFCMNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $title;
    protected $redirectUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $title, $redirectUrl = null)
    {
        $this->message      = $message;
        $this->title        = $title;
        $this->redirectUrl  = $redirectUrl;
    }

    /**
     * Notification delivery channels.
     */
    public function via($notifiable)
    {

        return ['database', 'broadcast'];
    }

    /**
     * Data stored in the database.
     */
    public function toDatabase($notifiable)
    {
       
        return [
            'title'         => $this->title,
            'message'       => $this->message,
            'redirect_url'  => $this->redirectUrl,
        ];
    }

    /**
     * Data broadcast via Echo/Pusher.
     */
    public function toBroadcast($notifiable)
    {
      
    
        // Only include redirect_url if it's not null
        $broadcastData = [
            'title'       => $this->title,
            'message'     => $this->message,
            'created_at'  => now()->diffForHumans(),
        ];
    
        // Add the redirect_url if it's not null
        if ($this->redirectUrl) {
            $broadcastData['redirect_url'] = $this->redirectUrl;
        }
        return new BroadcastMessage($broadcastData);
    }
    
    
}
