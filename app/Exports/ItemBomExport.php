<?php

namespace App\Exports;

use App\Models\ItemRecipe;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ItemBomExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison, WithColumnFormatting
{
    protected $branchId;

    public function __construct($branchId)
    {
        $this->branchId = $branchId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ItemRecipe::with(['parentItem', 'ingredient'])
            ->whereHas('parentItem', function($query) {
                $query->where('branch_id', $this->branchId);
            })
            ->get();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'BOM (Resep)';
    }

    /**
     * @var ItemRecipe $recipe
     */
    public function map($recipe): array
    {
        return [
            $recipe->parentItem->name ?? '',
            $recipe->parentItem->sku ?? '',
            $recipe->ingredient->name ?? '',
            $recipe->ingredient->sku ?? '',
            $recipe->quantity_required,
            $recipe->ingredient->unit ?? '',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Produk',
            'SKU Produk',
            'Nama Bahan',
            'SKU Bahan',
            'Jumlah',
            'Satuan'
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            // E = Jumlah (column 5)
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
