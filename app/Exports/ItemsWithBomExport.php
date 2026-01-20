<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ItemsWithBomExport implements WithMultipleSheets
{
    protected $branchId;
    protected $typeFilter;

    public function __construct($branchId, $typeFilter = 'all')
    {
        $this->branchId = $branchId;
        $this->typeFilter = $typeFilter;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new ItemsExport($this->branchId, $this->typeFilter),
            new ItemBomExport($this->branchId),
        ];
    }
}
