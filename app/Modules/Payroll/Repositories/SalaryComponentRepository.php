<?php

namespace App\Modules\Payroll\Repositories;

use App\Modules\Payroll\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Collection;

class SalaryComponentRepository
{
    public function all(): Collection
    {
        return SalaryComponent::all();
    }

    public function find(int $id): ?SalaryComponent
    {
        return SalaryComponent::find($id);
    }

    public function create(array $data): SalaryComponent
    {
        return SalaryComponent::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $component = $this->find($id);
        return $component ? $component->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $component = $this->find($id);
        return $component ? $component->delete() : false;
    }
}
