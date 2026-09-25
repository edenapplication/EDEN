<?php

namespace App\Exports;

use App\Models\LotAffectation;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LotsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Enumerable
    {
        $query = LotAffectation::with(['grandSite', 'site', 'tf', 'bloc', 'affectation.client']);

        if (!empty($this->filters['grand_site_id'])) {
            $query->where('grand_site_id', $this->filters['grand_site_id']);
        }
        if (!empty($this->filters['bloc_id'])) {
            $query->where('bloc_id', $this->filters['bloc_id']);
        }
        if (isset($this->filters['disponible']) && $this->filters['disponible'] !== '') {
            $query->where('disponible', $this->filters['disponible'] === '1');
        }

        return $query->orderBy('grand_site_id')
                     ->orderBy('bloc_id')
                     ->orderBy('numero')
                     ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Grand Site',
            'Site',
            'TF',
            'Bloc',
            'Numéro',
            'Superficie (m²)',
            'Statut',
            'Client affecté',
            'Date affectation',
        ];
    }

    public function map($lot): array
    {
        return [
            $lot->id,
            $lot->grandSite?->nom ?? '-',
            $lot->site?->name ?? '-',
            $lot->tf?->title ?? '-',
            $lot->bloc?->code ?? '-',
            $lot->numero,
            $lot->superficie ? number_format($lot->superficie, 0, '.', '') : '',
            $lot->disponible ? 'Disponible' : 'Affecté',
            $lot->affectation?->client?->name ?? '',
            $lot->affectation?->date_affectation?->format('d/m/Y') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
            ],
        ];
    }
}