<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Setting;
use App\Models\Transportation;
use App\Models\User;
use App\Notifications\TransportationCompletedNotification;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class TransportationService
{
    private function generateNumber(): string
    {
        $year = Carbon::now()->year;
        $count = Transportation::whereYear('created_at', $year)->count() + 1;
        return sprintf('TRN-%d-%04d', $year, $count);
    }

    public function create(Application $app, array $data, User $logist): Transportation
    {
        if ($app->status !== 'open') {
            throw new \DomainException('Перевозку можно создать только для заявки со статусом "В ПУТИ".');
        }

        if (!app(ApplicationService::class)->canAddTransportation($app)) {
            throw new \DomainException('Достигнуто максимальное количество перевозок для этой заявки.');
        }

        return DB::transaction(function () use ($app, $data, $logist) {
            $transportation = Transportation::create(array_merge($data, [
                'number'             => $this->generateNumber(),
                'application_id'     => $app->id,
                'organization'       => Setting::get('organization_name', ''),
                'client_manager_id'  => $app->author_id,
                'logistic_manager_id' => $logist->id,
                'client_contract_id' => $app->contract_id,
                'status'             => isset($data['status']) ? $data['status'] : 'in_transit',
            ]));

            activity_log($logist, 'transportation_created', $transportation,
                "Создана перевозка {$transportation->number} по заявке {$app->number}");

            return $transportation;
        });
    }

    public function uploadCallPhoto(Transportation $transportation, UploadedFile $photo): Transportation
    {
        $path = $photo->store('call_photos', 'public');
        $transportation->update(['call_photo_path' => $path]);
        return $transportation->fresh();
    }

    public function complete(Transportation $transportation, User $user): Transportation
    {
        if (!$transportation->call_photo_path) {
            throw new \DomainException('Необходимо загрузить фото звонка перед завершением перевозки.');
        }

        return DB::transaction(function () use ($transportation, $user) {
            $transportation->update(['status' => 'completed']);

            // Update vehicle status
            if ($transportation->vehicle) {
                $transportation->vehicle->update(['status' => 'idle']);
            }

            // Update driver status
            if ($transportation->driver) {
                $transportation->driver->update(['status' => 'active']);
            }

            $this->updateApplicationProgress($transportation->application);

            activity_log($user, 'transportation_completed', $transportation,
                "Перевозка {$transportation->number} завершена");

            // Notify client manager
            if ($transportation->clientManager) {
                $transportation->clientManager->notify(
                    new TransportationCompletedNotification($transportation)
                );
            }

            return $transportation->fresh();
        });
    }

    public function updateApplicationProgress(Application $app): void
    {
        $completed = $app->transportations()->where('status', 'completed')->count();
        if ($app->planned_transportations_count > 0 && $completed >= $app->planned_transportations_count) {
            $app->update(['status' => 'closed']);
        }
    }

    public function uploadDocument(Transportation $transportation, UploadedFile $file, string $type): void
    {
        $path = $file->store('transportation_docs', 'public');
        $transportation->documents()->create([
            'type'          => $type,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    public function exportToWord(Transportation $transportation): string
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle("Транспортная накладная #{$transportation->number}", 1);
        $section->addTextBreak();

        $app = $transportation->application;

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);

        $this->addTableRow($table, 'Номер перевозки', $transportation->number);
        $this->addTableRow($table, 'Заявка', $app?->number ?? '');
        $this->addTableRow($table, 'Организация', $transportation->organization ?? '');
        $this->addTableRow($table, 'Маршрут', ($app?->departureCity?->name ?? '') . ' → ' . ($app?->destinationCity?->name ?? ''));
        $this->addTableRow($table, 'Клиент', $app?->client?->name ?? '');
        $this->addTableRow($table, 'Договор', $app?->contract?->number ?? '');
        $this->addTableRow($table, 'Тягач', "{$transportation->vehicle?->tractor_brand} | {$transportation->vehicle?->tractor_plate}");
        $this->addTableRow($table, 'Прицеп', "{$transportation->vehicle?->trailer_brand} | {$transportation->vehicle?->trailer_plate}");
        $this->addTableRow($table, 'Водитель', $transportation->driver?->full_name ?? '');
        $this->addTableRow($table, 'ИИН водителя', $transportation->driver?->iin ?? '');
        $this->addTableRow($table, 'Поставщик', $transportation->supplier?->name ?? '');
        $this->addTableRow($table, 'Ставка поставщика', "{$transportation->supplier_rate} {$transportation->supplier_rate_currency}");
        $this->addTableRow($table, 'Ставка клиента', "{$app?->client_rate} {$app?->clientRateCurrency?->code}");
        $this->addTableRow($table, 'Создано', $transportation->created_at->format('d.m.Y'));

        $filename = "TRN_{$transportation->number}.docx";
        $path = storage_path("app/public/exports/{$filename}");

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        return $path;
    }

    private function addTableRow($table, string $label, string $value): void
    {
        $row = $table->addRow();
        $row->addCell(3000)->addText($label, ['bold' => true]);
        $row->addCell(5000)->addText($value);
    }
}
