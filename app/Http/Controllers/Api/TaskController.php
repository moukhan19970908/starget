<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Application;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private TaskService $service) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('doc_manager')) {
            $tasks = $this->service->getForDocManager();
        } else {
            $tasks = $this->service->getForUser($user);
        }

        if ($request->status) {
            $tasks = $tasks->where('status', $request->status)->values();
        }

        return response()->json([
            'success' => true,
            'data'    => TaskResource::collection($tasks),
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $app = $request->application_id
            ? Application::find($request->application_id)
            : null;

        $task = $this->service->create(
            $request->user(),
            $request->type,
            $request->payload ?? [],
            $app
        );

        return response()->json([
            'success' => true,
            'data'    => new TaskResource($task->load(['creator', 'application'])),
            'message' => 'Задача создана.',
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new TaskResource($task->load(['creator', 'application'])),
        ]);
    }

    public function close(Request $request, Task $task): JsonResponse
    {
        if (!$request->user()->can('tasks.close')) {
            return response()->json(['success' => false, 'message' => 'Недостаточно прав.'], 403);
        }

        $this->service->close($task, $request->user());

        return response()->json([
            'success' => true,
            'data'    => new TaskResource($task->fresh()),
            'message' => 'Задача закрыта.',
        ]);
    }
}
