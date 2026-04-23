<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCreatedNotification extends Notification
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
            'type'        => 'task_created',
            'task_id'     => $this->task->id,
            'task_type'   => $this->task->type,
            'priority'    => $this->task->priority,
            'created_by'  => $this->task->creator?->name,
            'message'     => "Новая задача: {$this->task->type} от {$this->task->creator?->name}",
        ];
    }
}
