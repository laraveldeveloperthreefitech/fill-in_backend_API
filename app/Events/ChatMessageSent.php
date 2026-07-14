<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $chat;

    public function __construct(Chat $chat)
    {
         $this->chat = $chat;
    }

    public function broadcastOn()
    {
        if ($this->chat->sendBy === 'candidate') {
            return new PrivateChannel('chat.recruiter.' . $this->chat->recruiter_id);
        } else {
            return new PrivateChannel('chat.candidate.' . $this->chat->canditidate_id);
        }
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

	public function broadcastWith()
    {
      
        return [
            'id' => $this->chat->id,
            'message' => $this->chat->message,
            'sender_id' => $this->chat->sender_id ?? $this->chat->send_by,
            'created_at' => $this->chat->created_at->format('h:i A'),
        ];
    }
}
