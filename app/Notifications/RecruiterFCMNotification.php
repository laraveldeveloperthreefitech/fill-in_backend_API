<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class RecruiterFCMNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $title;
    protected $redirectUrl;
    protected $icon;
    protected $type;
    protected $uniqueId;
    protected $Imagetype;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        $message,
        $title,
        $redirectUrl = null,
        $icon = '',
        $type = '',
        $uniqueId = null,
        $Imagetype = ''
    ) {
        $this->message = $message;
        $this->title = $title;
        $this->redirectUrl = $redirectUrl;
        $this->icon = $icon;
        $this->type = $type;
        $this->uniqueId = $uniqueId;
        $this->Imagetype = $Imagetype;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [
            'database',
            'broadcast',
        ];
    }

    /**
     * Store notification in database.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title'        => $this->title,
            'message'      => $this->message,
            'redirect_url' => $this->redirectUrl,
            'icon'         => $this->icon,
            'type'         => $this->type,
            'unique_id'    => $this->uniqueId, // Fixed typo
            'Imagetype'    => $this->Imagetype,
        ];
    }

    /**
     * Broadcast notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title'        => $this->title,
            'message'      => $this->message,
            'redirect_url' => $this->redirectUrl,
            'icon'         => $this->icon,
            'type'         => $this->type,
            'unique_id'    => $this->uniqueId,
            'Imagetype'    => $this->Imagetype,
            'created_at'   => now()->diffForHumans(),
        ]);
    }
}