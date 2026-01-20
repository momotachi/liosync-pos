<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ItemsWithBomImport implements WithMultipleSheets
{
    protected $branchId;
    protected $companyId;
    protected $itemsImport;
    protected $bomImport;

    public function __construct($branchId, $companyId)
    {
        $this->branchId = $branchId;
        $this->companyId = $companyId;

        // Create ItemsImport first
        $this->itemsImport = new ItemsImport($branchId, $companyId);

        // Create BomImport with reference to item map from ItemsImport
        $this->bomImport = new BomImport($branchId, $this->itemsImport->getItemMapReference());
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            'Items' => $this->itemsImport,
            'BOM (Resep)' => $this->bomImport,
        ];
    }

    public function getImported(): int
    {
        return $this->itemsImport->getImported();
    }

    public function getUpdated(): int
    {
        return $this->itemsImport->getUpdated();
    }

    public function getSkipped(): int
    {
        return $this->itemsImport->getSkipped();
    }

    public function getRecipesImported(): int
    {
        return $this->bomImport->getRecipesImported();
    }

    public function getFailed(): int
    {
        return $this->itemsImport->getFailed() + $this->bomImport->getFailed();
    }

    public function getErrors(): array
    {
        return array_merge(
            $this->itemsImport->getErrors(),
            $this->bomImport->getErrors()
        );
    }
}
