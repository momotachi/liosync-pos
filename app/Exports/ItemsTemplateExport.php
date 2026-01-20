<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ItemsTemplateExport implements FromArray, WithHeadings, WithTitle, WithColumnFormatting
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            // Sample 1: Purchase item
            [
                'Tepung Terigu',
                'Bahan Baku',
                '',
                '',
                'Purchase',
                15000,
                'kg',
                100,
                20,
                '',
                'Yes',
                'Tepung terigu untuk roti'
            ],
            // Sample 2: Sales item
            [
                'Roti Bakar Coklat',
                'Produk',
                'RB001',
                '89910001',
                'Sales',
                '',
                '',
                '',
                '',
                '',
                25000,
                'Yes',
                'Roti bakar dengan topping coklat'
            ],
            // Sample 3: Both (Purchase and Sales)
            [
                'Aqua Botol',
                'Minuman',
                'AQUA001',
                '89960001',
                'Both',
                3000,
                'botol',
                50,
                10,
                5000,
                'Yes',
                'Aqua botol 600ml - bisa dibeli dan dijual'
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Items';
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
