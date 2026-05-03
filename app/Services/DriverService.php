<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverDocument;
use App\Models\Owner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DriverService
{
    public function create(array $data): Driver
    {
        return DB::transaction(function () use ($data) {
            $isOwner = $data['is_owner'] ?? false;
            $documents = $data['documents'] ?? [];
            $files = $data['files'] ?? [];

            unset($data['documents'], $data['files']);

            $data['is_owner'] = (bool) $isOwner;
            $driver = Driver::create($data);

            // Create owner record if driver is also owner
            if ($isOwner) {
                Owner::create([
                    'type'      => $data['type'],
                    'full_name' => $data['full_name'],
                    'phone'     => $data['phone'] ?? null,
                    'iin'       => $data['iin'] ?? null,
                ]);
            }

            // Create documents
            foreach ($documents as $i => $docData) {
                $filePath = null;
                if (isset($files[$i]) && $files[$i] instanceof UploadedFile) {
                    $filePath = $files[$i]->store('driver_docs', 'public');
                }
                $driver->documents()->create(array_merge($docData, ['file_path' => $filePath]));
            }

            return $driver;
        });
    }

    public function addDocument(Driver $driver, array $data, ?UploadedFile $file = null): DriverDocument
    {
        $filePath = null;
        if ($file) {
            $filePath = $file->store('driver_docs', 'public');
        }

        return $driver->documents()->create(array_merge($data, ['file_path' => $filePath]));
    }

    public function search(?string $iin = null, ?string $phone = null): \Illuminate\Database\Eloquent\Collection
    {
        return Driver::with('documents.documentType')
            ->when($iin, fn($q) => $q->where('iin', 'like', "%{$iin}%"))
            ->when($phone, fn($q) => $q->orWhere('phone', 'like', "%{$phone}%"))
            ->limit(20)
            ->get();
    }
}
