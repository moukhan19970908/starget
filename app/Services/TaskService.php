<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskCreatedNotification;
use App\Notifications\TaskClosedNotification;

class TaskService
{
    public function create(User $requester, string $type, array $payload = [], ?Application $app = null): Task
    {
        $task = Task::create([
            'created_by'     => $requester->id,
            'assigned_role'  => 'doc_manager',
            'type'           => $type,
            'payload'        => $payload,
            'status'         => 'open',
            'application_id' => $app?->id,
        ]);

        // Notify doc managers
        $docManagers = User::role('doc_manager')->get();
        foreach ($docManagers as $manager) {
            $manager->notify(new TaskCreatedNotification($task));
        }

        return $task;
    }

    public function close(Task $task, User $docManager): void
    {
        $task->update([
            'status'      => 'done',
            'resolved_at' => now(),
        ]);

        // Notify the task creator
        $task->creator?->notify(new TaskClosedNotification($task, $docManager));
    }

    public function getForDocManager(): \Illuminate\Database\Eloquent\Collection
    {
        return Task::with(['creator', 'application'])
            ->where('assigned_role', 'doc_manager')
            ->where('status', 'open')
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Task::with(['creator', 'application'])
            ->where('created_by', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }
}
