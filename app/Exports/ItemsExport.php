<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ItemsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnFormatting
{
    protected $branchId;
    protected $typeFilter;

    public function __construct($branchId, $typeFilter = 'all')
    {
        $this->branchId = $branchId;
        $this->typeFilter = $typeFilter;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Item::with('category')->where('branch_id', $this->branchId);

        // Apply type filter
        if ($this->typeFilter === 'purchase') {
            $query->purchaseItems();
        } elseif ($this->typeFilter === 'sales') {
            $query->salesItems();
        } elseif ($this->typeFilter === 'both') {
            $query->both();
        }

        return $query->get();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Items';
    }

    /**
     * @var Item $item
     */
    public function map($item): array
    {
        $type = '';
        if ($item->is_purchase && $item->is_sales) {
            $type = 'Both';
        } elseif ($item->is_purchase) {
            $type = 'Purchase';
        } elseif ($item->is_sales) {
            $type = 'Sales';
        }

        return [
            $item->name,
            $item->category->name ?? '',
            $item->sku ?? '',
            $item->barcode ?? '',
            $type,
            $item->is_purchase ? $item->unit_price : '',
            $item->is_purchase ? $item->unit : '',
            $item->is_purchase ? $item->current_stock : '',
            $item->is_purchase ? $item->min_stock_level : '',
            $item->is_sales ? $item->selling_price : '',
            $item->is_active ? 'Yes' : 'No',
            $item->description ?? ''
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Item',
            'Kategori',
            'SKU',
            'Barcode',
            'Tipe',
            'Harga Beli',
            'Satuan',
            'Stok',
            'Min Stok',
            'Harga Jual',
            'Aktif',
            'Deskripsi'
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            // F = Harga Beli (column 6)
            'F' => NumberFormat::FORMAT_NUMBER_00,
            // I = Stok (column 9)
            'I' => NumberFormat::FORMAT_NUMBER_00,
            // J = Min Stok (column 10)
            'J' => NumberFormat::FORMAT_NUMBER_00,
            // K = Harga Jual (column 11)
            'K' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
