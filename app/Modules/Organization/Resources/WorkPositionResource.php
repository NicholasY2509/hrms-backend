<?php

namespace App\Modules\Organization\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkPositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
            'prefix' => $this->prefix,
            'uang_makan' => $this->uang_makan,
            'potongan_uang_makan' => $this->potongan_uang_makan,
            'uang_transport' => $this->uang_transport,
            'potongan_uang_transport' => $this->potongan_uang_transport,
            'tunjangan_jabatan' => $this->tunjangan_jabatan,
            'tunjangan_kerajinan' => $this->tunjangan_kerajinan,
            'description' => $this->description,
            'pengalaman' => $this->pengalaman,
            'lokasi' => $this->lokasi,
            'employees_count' => $this->whenCounted('employees'),
            'criteria' => JsonResource::collection($this->whenLoaded('criteria')),
            'approvals' => JsonResource::collection($this->whenLoaded('approvals')),
        ];
    }
}
