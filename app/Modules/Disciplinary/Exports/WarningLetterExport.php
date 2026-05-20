<?php

namespace App\Modules\Disciplinary\Exports;

use App\Modules\Disciplinary\Models\WarningLetter;
use App\Modules\Disciplinary\Services\WarningLetterTemplateService;

class WarningLetterExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = WarningLetter::query()->with([
            'employee.position',
            'employee.work_location',
            'warning_letter_type'
        ]);

        if (isset($this->filters['id'])) {
            $query->where('id', $this->filters['id']);
        }

        return $query;
    }

    /**
     * This is a custom method called by the PDF view to get template data.
     * Although the Job passes $data (the collection), we can enhance it here if needed.
     */
    public function getTemplateData(WarningLetter $warningLetter)
    {
        return (new WarningLetterTemplateService())->getTemplateData($warningLetter);
    }
}
