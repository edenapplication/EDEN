<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LotsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['EDEN Yaoundé', 'NTOUESSONG 5', 'TF-2024-001', 'A', '01;02;03;04;05', '500'],
            ['EDEN Yaoundé', 'NTOUESSONG 5', 'TF-2024-001', 'B', '01;02;03', '600'],
            ['EDEN Bafoussam', '', '', 'C', '15A;15B;16', '400'],
        ];
    }

    public function headings(): array
    {
        return [
            'grand_site',
            'site',
            'tf',
            'bloc',
            'numeros',
            'superficie',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => '1D4ED8'],
                ],
            ],
        ];
    }
}