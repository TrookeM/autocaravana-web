<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;
use Carbon\Carbon;

class CampervanIncomeChart extends ChartWidget
{
    protected static ?string $heading = null; 
    protected static ?int $sort = 4; 

    // Escucha el filtro de año
    public ?string $selectedYear;
    protected $listeners = ['yearChanged' => 'updateYear'];

    public function mount(): void
    {
        $this->selectedYear = (string) now()->year;
    }

    public function updateYear(string $year): void
    {
        $this->selectedYear = $year;
        $this->updateChartData();
    }

    public function getHeading(): string // <-- Corregido a public
    {
        $yearLabel = $this->selectedYear === 'all' ? 'Global' : $this->selectedYear;
        return 'Ingresos por Caravana (' . $yearLabel . ')';
    }

    protected function getData(): array
    {
        $query = Booking::whereIn('status', ['confirmed', 'completed'])
                    ->with('campervan'); 

        if ($this->selectedYear !== 'all') {
            $query->whereYear('start_date', $this->selectedYear);
        }
        
        $bookings = $query->get();

        $incomeByCampervan = $bookings->groupBy('campervan.name')
                                      ->map(fn ($group) => $group->sum('total_price'));

        if ($incomeByCampervan->isEmpty()) {
            return [
                'datasets' => [['label' => 'Ingresos', 'data' => []]],
                'labels' => ['Sin datos']
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $incomeByCampervan->values()->all(),
                    'backgroundColor' => [ 
                        'rgba(22, 163, 74, 0.7)', 
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(234, 179, 8, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                    ],
                    'hoverOffset' => 4
                ],
            ],
            'labels' => $incomeByCampervan->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; 
    }
}