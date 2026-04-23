<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationStop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    private function generateNumber(): string
    {
        $year = Carbon::now()->year;
        $count = Application::whereYear('created_at', $year)->count() + 1;
        return sprintf('REQ-%d-%04d-KZ', $year, $count);
    }

    public function create(array $data, User $author): Application
    {
        return DB::transaction(function () use ($data, $author) {
            $stops = $data['stops'] ?? [];
            unset($data['stops']);

            $application = Application::create(array_merge($data, [
                'number'    => $this->generateNumber(),
                'author_id' => $author->id,
                'status'    => 'open',
            ]));

            $this->createStops($application, $stops);

            activity_log($author, 'application_created', $application, "Создана заявка {$application->number}");

            return $application;
        });
    }

    public function saveDraft(array $data, User $author): Application
    {
        return DB::transaction(function () use ($data, $author) {
            $stops = $data['stops'] ?? [];
            unset($data['stops']);

            $application = Application::create(array_merge($data, [
                'number'    => $this->generateNumber(),
                'author_id' => $author->id,
                'status'    => 'draft',
            ]));

            $this->createStops($application, $stops);

            return $application;
        });
    }

    public function update(Application $application, array $data): Application
    {
        $application->update($data);
        return $application->fresh();
    }

    public function changeStatus(Application $app, string $status, array $extra = []): void
    {
        $data = ['status' => $status];

        if (in_array($status, ['client_refusal', 'our_refusal', 'mutual_refusal'])) {
            if (empty($extra['refusal_reason_id'])) {
                throw new \InvalidArgumentException('Причина отказа обязательна.');
            }
            $data['refusal_reason_id'] = $extra['refusal_reason_id'];
            $data['refusal_comment']   = $extra['refusal_comment'] ?? null;
        }

        if ($status === 'in_transit') {
            if (empty($extra['planned_transportations_count'])) {
                throw new \InvalidArgumentException('Укажите количество перевозок.');
            }
            $data['planned_transportations_count'] = $extra['planned_transportations_count'];
        }

        $app->update($data);
    }

    public function canAddTransportation(Application $app): bool
    {
        if ($app->planned_transportations_count === 0) {
            return false;
        }
        $created = $app->transportations()->count();
        return $created < $app->planned_transportations_count;
    }

    private function createStops(Application $application, array $stops): void
    {
        foreach ($stops as $i => $stop) {
            ApplicationStop::create(array_merge($stop, [
                'application_id' => $application->id,
                'sort_order'     => $stop['sort_order'] ?? $i + 1,
            ]));
        }
    }
}
