<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BomTemplateExport implements FromArray, WithHeadings, WithTitle, WithStrictNullComparison, WithColumnFormatting
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            // Sample 1: Roti Bakar Coklat uses Tepung Terigu
            [
                'Roti Bakar Coklat',
                'RB001',
                'Tepung Terigu',
                '',
                0.2,
                'kg'
            ],
            // Sample 2: Roti Bakar Coklat uses Gula
            [
                'Roti Bakar Coklat',
                'RB001',
                'Gula Pasir',
                '',
                0.05,
                'kg'
            ],
            // Sample 3: Jus Jeruk uses Orange
            [
                'Fresh Orange Juice',
                'JU001',
                'Orange Buah',
                '',
                3,
                'buah'
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'BOM (Resep)';
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
