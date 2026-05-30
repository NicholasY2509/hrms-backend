<?php

namespace App\Modules\Leave\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AuditLeaveImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $this->data = $rows->toArray();
    }

    public function headingRow(): int
    {
        return 3;
    }
}
