<?php

namespace App\Notifications;

use App\Models\Transportation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransportationCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(private Transportation $transportation) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'              => 'transportation_completed',
            'transportation_id' => $this->transportation->id,
            'number'            => $this->transportation->number,
            'application_id'    => $this->transportation->application_id,
            'message'           => "Перевозка {$this->transportation->number} завершена.",
        ];
    }
}
