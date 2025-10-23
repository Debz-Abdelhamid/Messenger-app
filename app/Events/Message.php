<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Message implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $eventType;

    /**
     * Create a new event instance.
     */
    public function __construct($message,$eventType = 'message')
    {
        $this->message = $message;
        $this->eventType = $eventType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('message.'.$this->message->to_id),
        ];
    }

    public function broadcastWith(): array{

        $data = [
            'event_type' => $this->eventType,  // Include the event type in the data
            'message_id' => $this->message->id,
            'from_id' => auth()->user()->id,
        ];

        if ($this->eventType === 'message') {
            $data['message'] = $this->message->body;
            $data['reciver_id'] = $this->message->to_id;
            $data['attachment'] = json_decode($this->message->attachment);
        }

        return $data;
    }
}
