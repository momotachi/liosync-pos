<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ItemsWithBomTemplateExport implements WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new ItemsTemplateExport(),
            new BomTemplateExport(),
        ];
    }
}
