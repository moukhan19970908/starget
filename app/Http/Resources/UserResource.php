<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'surname'    => $this->surname,
            'full_name'  => trim("{$this->name} {$this->surname}"),
            'email'      => $this->email,
            'role'       => $this->role,
            'avatar_path' => $this->avatar_path,
        ];
    }
}
