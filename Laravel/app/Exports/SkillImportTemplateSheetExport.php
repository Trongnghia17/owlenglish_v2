<?php

namespace App\Exports;

use App\Services\SkillExcelImportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SkillImportTemplateSheetExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(
        private readonly string $title,
        private readonly array $rows
    ) {
    }

    public function headings(): array
    {
        return SkillExcelImportService::HEADINGS;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return $this->title;
    }
}
