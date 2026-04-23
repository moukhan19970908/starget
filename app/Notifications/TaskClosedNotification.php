<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskClosedNotification extends Notification
{
    use Queueable;

    public function __construct(private Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'task_closed',
            'task_id'   => $this->task->id,
            'task_type' => $this->task->type,
            'closed_at' => $this->task->resolved_at?->toISOString(),
            'message'   => "Задача #{$this->task->id} ({$this->task->type}) закрыта.",
        ];
    }
}
