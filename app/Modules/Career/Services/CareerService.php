<?php

namespace App\Modules\Career\Services;

use App\Modules\Career\Models\Career;
use App\Modules\Career\Repositories\CareerRepository;
use Illuminate\Support\Facades\DB;

class CareerService
{
    public function __construct(
        protected CareerRepository $repository
    ) {}

    public function createCareer(array $data): Career
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function updateCareer(Career $career, array $data): Career
    {
        return DB::transaction(function () use ($career, $data) {
            $this->repository->update($career, $data);
            return $career->refresh();
        });
    }

    public function deleteCareer(Career $career): bool
    {
        return DB::transaction(function () use ($career) {
            // Depending on the Approvable trait, deleting it might also delete approval requests
            // but for now, we just delete the model.
            return $this->repository->delete($career);
        });
    }
}
